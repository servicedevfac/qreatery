<?php

namespace App\Actions;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RecordPaymentAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(Restaurant $restaurant, Order $order, User $operator, array $data): Payment
    {
        abort_unless($order->restaurant_id === $restaurant->id, 404);

        return DB::transaction(function () use ($restaurant, $order, $operator, $data): Payment {
            $existing = $restaurant->payments()->where('idempotency_key', $data['idempotency_key'])->first();
            if ($existing instanceof Payment) {
                if ($existing->order_id !== $order->id || $existing->amount_minor !== $data['amount_minor'] || $existing->type !== 'charge') {
                    throw ValidationException::withMessages(['idempotency_key' => 'Cette clé a déjà été utilisée avec des données différentes.']);
                }

                return $existing->load(['operator', 'refunds']);
            }

            $lockedOrder = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
            $paidMinor = $this->netPaidMinor($lockedOrder);
            $remainingMinor = $lockedOrder->total_minor - $paidMinor;

            if ($data['amount_minor'] > $remainingMinor) {
                throw ValidationException::withMessages(['amount_minor' => "Le montant dépasse le solde restant de {$remainingMinor} {$lockedOrder->currency}."]);
            }

            $payment = $restaurant->payments()->create([
                'order_id' => $lockedOrder->id,
                'operator_id' => $operator->id,
                'type' => 'charge',
                'amount_minor' => $data['amount_minor'],
                'currency' => $lockedOrder->currency,
                'method' => $data['method'],
                'external_reference' => $data['external_reference'] ?? null,
                'idempotency_key' => $data['idempotency_key'],
                'status' => 'succeeded',
                'metadata' => $data['metadata'] ?? null,
                'processed_at' => now(),
            ]);

            $newPaidMinor = $paidMinor + $payment->amount_minor;
            $lockedOrder->update([
                'payment_status' => $newPaidMinor >= $lockedOrder->total_minor ? 'paid' : 'partially_paid',
            ]);

            return $payment->load(['operator', 'refunds']);
        });
    }

    private function netPaidMinor(Order $order): int
    {
        $charges = (int) $order->payments()->where('type', 'charge')->where('status', 'succeeded')->sum('amount_minor');
        $refunds = (int) $order->payments()->where('type', 'refund')->where('status', 'succeeded')->sum('amount_minor');

        return $charges - $refunds;
    }
}
