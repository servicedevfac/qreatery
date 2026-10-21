<?php

namespace App\Actions;

use App\Events\OrderCreated;
use App\Models\Order;
use App\Models\Product;
use App\Models\QrCode;
use App\Models\Restaurant;
use App\Notifications\NewOrderNotification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CreateOrderAction
{
    public function __construct(private readonly RecordAuditLogAction $recordAuditLog) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(Restaurant $restaurant, array $data): Order
    {
        $this->validateRestaurantConfiguration($restaurant, $data);
        $restaurantTableId = $this->resolveRestaurantTableId($restaurant, $data);
        $products = $this->loadProducts($restaurant, $data['items']);
        $preparedItems = $this->prepareItems($products, $data['items']);
        $subtotalMinor = array_sum(array_column($preparedItems, 'line_total_minor'));
        $taxRate = (float) ($restaurant->settings['tax_rate'] ?? 0);
        $taxMinor = (int) round($subtotalMinor * $taxRate / 100);
        $serviceFeeMinor = (int) ($restaurant->settings['service_fee_minor'] ?? 0);

        $order = DB::transaction(function () use ($restaurant, $data, $restaurantTableId, $preparedItems, $subtotalMinor, $taxMinor, $serviceFeeMinor): Order {
            $order = $restaurant->orders()->create([
                'restaurant_table_id' => $restaurantTableId,
                'number' => $this->uniqueOrderNumber(),
                'tracking_token' => $this->uniqueTrackingToken(),
                'mode' => $data['mode'],
                'customer_name' => $data['customer_name'] ?? null,
                'customer_phone' => $data['customer_phone'] ?? null,
                'notes' => $data['notes'] ?? null,
                'subtotal_minor' => $subtotalMinor,
                'tax_minor' => $taxMinor,
                'service_fee_minor' => $serviceFeeMinor,
                'total_minor' => $subtotalMinor + $taxMinor + $serviceFeeMinor,
                'currency' => $restaurant->currency,
            ]);

            foreach ($preparedItems as $preparedItem) {
                $options = $preparedItem['options'];
                unset($preparedItem['options']);
                $orderItem = $order->items()->create($preparedItem);
                $orderItem->options()->createMany($options);
            }

            $order->statusHistories()->create([
                'from_status' => null,
                'to_status' => 'pending',
            ]);

            return $order->load(['restaurantTable', 'items.options', 'statusHistories']);
        });

        $this->recordAuditLog->handle(null, 'order.created', $order, $restaurant, [
            'mode' => $order->mode,
            'total_minor' => $order->total_minor,
            'currency' => $order->currency,
        ]);
        Notification::send(
            $restaurant->users()->wherePivot('is_active', true)->get(),
            new NewOrderNotification($order),
        );
        OrderCreated::dispatch($order);

        return $order;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function validateRestaurantConfiguration(Restaurant $restaurant, array $data): void
    {
        abort_unless($restaurant->status->value === 'active', 404);

        $allowedModes = $restaurant->settings['order_modes'] ?? ['dine_in', 'takeaway'];
        if (! in_array($data['mode'], $allowedModes, true)) {
            throw ValidationException::withMessages(['mode' => 'Ce mode de commande est désactivé pour ce restaurant.']);
        }

        foreach ($restaurant->settings['required_customer_fields'] ?? [] as $requiredField) {
            $key = 'customer_'.$requiredField;
            if (blank($data[$key] ?? null)) {
                throw ValidationException::withMessages([$key => 'Ce champ client est obligatoire.']);
            }
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function resolveRestaurantTableId(Restaurant $restaurant, array $data): ?int
    {
        $qrCode = null;
        if (! empty($data['qr_token'])) {
            $qrCode = $restaurant->qrCodes()
                ->where('token', $data['qr_token'])
                ->where('status', 'active')
                ->first();

            if (! $qrCode instanceof QrCode) {
                throw ValidationException::withMessages(['qr_token' => 'Ce QR Code est invalide ou révoqué.']);
            }
        }

        if ($data['mode'] === 'dine_in') {
            if (! $qrCode instanceof QrCode || $qrCode->type !== 'table' || $qrCode->restaurant_table_id === null) {
                throw ValidationException::withMessages(['qr_token' => 'Un QR Code de table actif est requis pour une commande sur place.']);
            }

            $isActiveTable = $restaurant->restaurantTables()
                ->whereKey($qrCode->restaurant_table_id)
                ->where('is_active', true)
                ->whereNull('archived_at')
                ->exists();

            if (! $isActiveTable) {
                throw ValidationException::withMessages(['qr_token' => 'La table associée à ce QR Code est indisponible.']);
            }

            return $qrCode->restaurant_table_id;
        }

        if ($qrCode instanceof QrCode && $qrCode->type !== 'takeaway') {
            throw ValidationException::withMessages(['qr_token' => 'Utilisez le QR Code général pour une commande à emporter.']);
        }

        return null;
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     * @return Collection<int, Product>
     */
    private function loadProducts(Restaurant $restaurant, array $items): Collection
    {
        $productIds = collect($items)->pluck('product_id')->unique()->values();
        $products = $restaurant->products()
            ->with(['variants', 'optionGroups.options'])
            ->whereIn('id', $productIds)
            ->get()
            ->keyBy('id');

        if ($products->count() !== $productIds->count()) {
            throw ValidationException::withMessages(['items' => 'Un produit ne fait pas partie de ce restaurant.']);
        }

        return $products;
    }

    /**
     * @param  Collection<int, Product>  $products
     * @param  array<int, array<string, mixed>>  $items
     * @return array<int, array<string, mixed>>
     */
    private function prepareItems(Collection $products, array $items): array
    {
        $preparedItems = [];

        foreach ($items as $index => $item) {
            /** @var Product $product */
            $product = $products->get($item['product_id']);
            if ($product->status !== 'active' || ! $product->is_available) {
                throw ValidationException::withMessages(["items.{$index}.product_id" => 'Ce produit est indisponible.']);
            }

            $variant = null;
            if (! empty($item['variant_id'])) {
                $variant = $product->variants->first(fn ($candidate) => $candidate->id === $item['variant_id'] && $candidate->is_active);
                if ($variant === null) {
                    throw ValidationException::withMessages(["items.{$index}.variant_id" => 'Cette variante est invalide ou indisponible.']);
                }
            }

            $selectedOptionIds = collect($item['option_ids'] ?? []);
            $allowedOptions = $product->optionGroups
                ->where('is_active', true)
                ->flatMap(fn ($group) => $group->options->where('is_active', true))
                ->keyBy('id');

            if ($selectedOptionIds->contains(fn ($id) => ! $allowedOptions->has($id))) {
                throw ValidationException::withMessages(["items.{$index}.option_ids" => 'Une option est invalide pour ce produit.']);
            }

            foreach ($product->optionGroups->where('is_active', true) as $optionGroup) {
                $selectedCount = $selectedOptionIds->intersect($optionGroup->options->pluck('id'))->count();
                if ($selectedCount < $optionGroup->minimum_selections || $selectedCount > $optionGroup->maximum_selections) {
                    throw ValidationException::withMessages(["items.{$index}.option_ids" => "Le groupe {$optionGroup->name} exige entre {$optionGroup->minimum_selections} et {$optionGroup->maximum_selections} choix."]);
                }
            }

            $selectedOptions = $selectedOptionIds->map(fn ($optionId) => $allowedOptions->get($optionId));
            $unitPriceMinor = $product->price_minor
                + ($variant?->price_delta_minor ?? 0)
                + $selectedOptions->sum('price_delta_minor');

            if ($unitPriceMinor < 0) {
                throw ValidationException::withMessages(["items.{$index}" => 'Le prix calculé de cette ligne est invalide.']);
            }

            $preparedItems[] = [
                'product_id' => $product->id,
                'product_variant_id' => $variant?->id,
                'product_name' => $product->name,
                'variant_name' => $variant?->name,
                'unit_price_minor' => $unitPriceMinor,
                'quantity' => $item['quantity'],
                'line_total_minor' => $unitPriceMinor * $item['quantity'],
                'instructions' => $item['instructions'] ?? null,
                'options' => $selectedOptions->map(fn ($option): array => [
                    'option_id' => $option->id,
                    'option_name' => $option->name,
                    'price_delta_minor' => $option->price_delta_minor,
                ])->values()->all(),
            ];
        }

        return $preparedItems;
    }

    private function uniqueOrderNumber(): string
    {
        do {
            $number = 'ORD-'.now()->format('Ymd').'-'.Str::upper(Str::random(6));
        } while (Order::where('number', $number)->exists());

        return $number;
    }

    private function uniqueTrackingToken(): string
    {
        do {
            $token = Str::random(64);
        } while (Order::where('tracking_token', $token)->exists());

        return $token;
    }
}
