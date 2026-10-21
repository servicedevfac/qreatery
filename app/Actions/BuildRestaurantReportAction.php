<?php

namespace App\Actions;

use App\Models\OrderItem;
use App\Models\Restaurant;
use Carbon\Carbon;

class BuildRestaurantReportAction
{
    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function handle(Restaurant $restaurant, array $filters): array
    {
        $from = isset($filters['date_from'])
            ? Carbon::parse($filters['date_from'], $restaurant->timezone)->startOfDay()
            : now($restaurant->timezone)->startOfMonth();
        $to = isset($filters['date_to'])
            ? Carbon::parse($filters['date_to'], $restaurant->timezone)->endOfDay()
            : now($restaurant->timezone)->endOfDay();
        $mode = $filters['mode'] ?? null;

        $orders = $restaurant->orders()
            ->whereBetween('created_at', [$from->copy()->utc(), $to->copy()->utc()])
            ->when($mode, fn ($query) => $query->where('mode', $mode));

        $payments = $restaurant->payments()
            ->where('status', 'succeeded')
            ->whereBetween('processed_at', [$from->copy()->utc(), $to->copy()->utc()]);

        $chargesMinor = (int) (clone $payments)->where('type', 'charge')->sum('amount_minor');
        $refundsMinor = (int) (clone $payments)->where('type', 'refund')->sum('amount_minor');
        $orderCount = (clone $orders)->count();
        $averageBasketMinor = $orderCount === 0 ? 0 : (int) round((float) (clone $orders)->avg('total_minor'));
        $preparationDurations = (clone $orders)
            ->whereNotNull('accepted_at')
            ->whereNotNull('ready_at')
            ->get(['accepted_at', 'ready_at'])
            ->map(fn ($order): int => (int) $order->accepted_at->diffInSeconds($order->ready_at));

        $ordersByMode = (clone $orders)
            ->selectRaw('mode, COUNT(*) as aggregate')
            ->groupBy('mode')
            ->pluck('aggregate', 'mode')
            ->map(fn ($count): int => (int) $count);

        $topProducts = OrderItem::query()
            ->whereHas('order', function ($query) use ($restaurant, $from, $to, $mode): void {
                $query->whereBelongsTo($restaurant)
                    ->whereBetween('created_at', [$from->copy()->utc(), $to->copy()->utc()])
                    ->when($mode, fn ($modeQuery) => $modeQuery->where('mode', $mode));
            })
            ->selectRaw('product_name, SUM(quantity) as quantity, SUM(line_total_minor) as revenue_minor')
            ->groupBy('product_name')
            ->orderByDesc('quantity')
            ->limit(10)
            ->get()
            ->map(fn ($item): array => [
                'product_name' => $item->product_name,
                'quantity' => (int) $item->quantity,
                'revenue_minor' => (int) $item->revenue_minor,
            ])->values()->all();

        return [
            'restaurant' => [
                'id' => $restaurant->id,
                'name' => $restaurant->name,
            ],
            'period' => [
                'from' => $from->toIso8601String(),
                'to' => $to->toIso8601String(),
                'timezone' => $restaurant->timezone,
                'mode' => $mode,
            ],
            'orders' => [
                'total' => $orderCount,
                'accepted' => (clone $orders)->whereNotNull('accepted_at')->count(),
                'completed' => (clone $orders)->where('preparation_status', 'completed')->count(),
                'cancelled' => (clone $orders)->where('preparation_status', 'cancelled')->count(),
                'rejected' => (clone $orders)->where('preparation_status', 'rejected')->count(),
                'average_basket_minor' => $averageBasketMinor,
                'by_mode' => $ordersByMode->all(),
                'average_preparation_seconds' => $preparationDurations->isEmpty() ? null : (int) round($preparationDurations->average()),
            ],
            'payments' => [
                'charged_minor' => $chargesMinor,
                'refunded_minor' => $refundsMinor,
                'net_collected_minor' => $chargesMinor - $refundsMinor,
                'currency' => $restaurant->currency,
            ],
            'top_products' => $topProducts,
        ];
    }
}
