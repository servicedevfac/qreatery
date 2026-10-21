<?php

namespace App\Actions;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RefundPaymentAction
{
    public function __construct(private readonly RecordAuditLogAction $recordAuditLog) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(Restaurant $restaurant, Payment $payment, User $operator, array $data): Payment
    {
        abort_unless($payment->restaurant_id === $restaurant->id, 404);

        return DB::transaction(function () use ($restaurant, $payment, $operator, $data): Payment {
            $existing = $restaurant->payments()->where('idempotency_key', $data['idempotency_key'])->first();
            if ($existing instanceof Payment) {
                if ($existing->original_payment_id !== $payment->id || $existing->amount_minor !== $data['amount_minor'] || $existing->type !== 'refund') {
                    throw ValidationException::withMessages(['idempotency_key' => 'Cette clé a déjà été utilisée avec des données différentes.']);
                }

                return $existing->load(['operator', 'originalPayment']);
            }

            $lockedOrder = Order::whereKey($payment->order_id)->lockForUpdate()->firstOrFail();
            $lockedPayment = Payment::whereKey($payment->id)->lockForUpdate()->firstOrFail();
            if ($lockedPayment->type !== 'charge' || $lockedPayment->status !== 'succeeded') {
                throw ValidationException::withMessages(['payment' => 'Seul un encaissement réussi peut être remboursé.']);
            }

            $alreadyRefunded = (int) $lockedPayment->refunds()->where('status', 'succeeded')->sum('amount_minor');
            $refundableMinor = $lockedPayment->amount_minor - $alreadyRefunded;
            if ($data['amount_minor'] > $refundableMinor) {
                throw ValidationException::withMessages(['amount_minor' => "Le montant dépasse le solde remboursable de {$refundableMinor} {$lockedPayment->currency}."]);
            }

            $refund = $restaurant->payments()->create([
                'order_id' => $lockedOrder->id,
                'operator_id' => $operator->id,
                'original_payment_id' => $lockedPayment->id,
                'type' => 'refund',
                'amount_minor' => $data['amount_minor'],
                'currency' => $lockedPayment->currency,
                'method' => $lockedPayment->method,
                'idempotency_key' => $data['idempotency_key'],
                'status' => 'succeeded',
                'metadata' => ['reason' => $data['reason']],
                'processed_at' => now(),
            ]);

            $charges = (int) $lockedOrder->payments()->where('type', 'charge')->where('status', 'succeeded')->sum('amount_minor');
            $refunds = (int) $lockedOrder->payments()->where('type', 'refund')->where('status', 'succeeded')->sum('amount_minor');
            $lockedOrder->update(['payment_status' => $charges - $refunds === 0 ? 'refunded' : 'partially_refunded']);

            $this->recordAuditLog->handle($operator, 'payment.refunded', $refund, $restaurant, [
                'order_id' => $lockedOrder->id,
                'original_payment_id' => $lockedPayment->id,
                'amount_minor' => $refund->amount_minor,
                'currency' => $refund->currency,
                'reason' => $data['reason'],
            ]);

            return $refund->load(['operator', 'originalPayment']);
        });
    }
}
