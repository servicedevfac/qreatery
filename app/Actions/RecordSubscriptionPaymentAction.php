<?php

namespace App\Actions;

use App\Models\Subscription;
use App\Models\SubscriptionPayment;
use App\Models\User;
use App\SubscriptionPaymentStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RecordSubscriptionPaymentAction
{
    public function __construct(private readonly RecordAuditLogAction $recordAuditLog) {}

    /** @param array<string, mixed> $data */
    public function handle(Subscription $subscription, User $actor, array $data): SubscriptionPayment
    {
        return DB::transaction(function () use ($subscription, $actor, $data): SubscriptionPayment {
            $existing = SubscriptionPayment::query()
                ->where('idempotency_key', $data['idempotency_key'])
                ->first();

            if ($existing instanceof SubscriptionPayment) {
                return $existing->load('recorder');
            }

            if (isset($data['transaction_reference']) && SubscriptionPayment::query()
                ->where('transaction_reference', $data['transaction_reference'])
                ->exists()) {
                throw ValidationException::withMessages([
                    'transaction_reference' => ['Cette référence de transaction est déjà utilisée.'],
                ]);
            }

            $status = SubscriptionPaymentStatus::from($data['status']);

            $payment = $subscription->payments()->create([
                'recorded_by' => $actor->id,
                'amount_minor' => $data['amount_minor'],
                'currency' => strtoupper($data['currency']),
                'method' => $data['method'],
                'transaction_reference' => $data['transaction_reference'] ?? null,
                'idempotency_key' => $data['idempotency_key'],
                'status' => $status,
                'failure_reason' => $data['failure_reason'] ?? null,
                'metadata' => $data['metadata'] ?? null,
                'paid_at' => $status === SubscriptionPaymentStatus::Succeeded
                    ? ($data['paid_at'] ?? now())
                    : null,
            ])->load('recorder');

            $this->recordAuditLog->handle($actor, 'subscription.payment_recorded', $payment, $subscription->restaurant, [
                'subscription_id' => $subscription->id,
                'amount_minor' => $payment->amount_minor,
                'currency' => $payment->currency,
                'status' => $status->value,
                'method' => $payment->method,
            ]);

            return $payment;
        });
    }
}
