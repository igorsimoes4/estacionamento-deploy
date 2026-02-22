<?php

namespace App\Services\Parking;

use App\Models\AccountingEntry;
use App\Models\Cars;
use App\Models\MonthlyBillingCycle;
use App\Models\MonthlySubscriber;
use App\Models\ParkingReservation;
use App\Models\PaymentTransaction;
use App\Models\PaymentWebhook;
use Illuminate\Support\Arr;

class PaymentReconciliationService
{
    public function registerWebhook(string $provider, array $payload, ?string $signature = null): array
    {
        $externalId = (string) (Arr::get($payload, 'id') ?? Arr::get($payload, 'payment.id') ?? Arr::get($payload, 'reference') ?? '');
        $eventType = (string) (Arr::get($payload, 'event') ?? Arr::get($payload, 'type') ?? Arr::get($payload, 'status') ?? 'unknown');

        $webhook = PaymentWebhook::query()->create([
            'provider' => strtolower($provider),
            'event_type' => $eventType,
            'external_id' => $externalId !== '' ? $externalId : null,
            'payload' => json_encode($payload, JSON_UNESCAPED_UNICODE),
            'signature' => $signature,
            'status' => 'pending',
        ]);

        $transaction = $this->findTransaction($provider, $payload, $externalId);

        if (!$transaction) {
            $webhook->status = 'ignored';
            $webhook->error_message = 'Transacao nao encontrada para o webhook recebido.';
            $webhook->processed_at = now();
            $webhook->save();

            return ['ok' => true, 'message' => 'Webhook registrado, sem transacao relacionada.', 'webhook_id' => $webhook->id];
        }

        $status = $this->normalizeStatus((string) (Arr::get($payload, 'status') ?? Arr::get($payload, 'payment.status') ?? Arr::get($payload, 'charges.0.status') ?? 'pending'));

        $transaction->status = $status;
        $transaction->external_id = $transaction->external_id ?: ($externalId !== '' ? $externalId : null);
        $transaction->gateway_payload = $payload;
        $transaction->reconciled_at = now();

        if ($status === 'paid') {
            $transaction->paid_at = now();
        }

        $transaction->save();

        $this->syncDomainEntities($transaction, $status);

        $webhook->status = 'processed';
        $webhook->processed_at = now();
        $webhook->save();

        return ['ok' => true, 'message' => 'Webhook processado com sucesso.', 'transaction_id' => $transaction->id];
    }

    private function findTransaction(string $provider, array $payload, string $externalId): ?PaymentTransaction
    {
        $reference = (string) (Arr::get($payload, 'reference')
            ?? Arr::get($payload, 'reference_id')
            ?? Arr::get($payload, 'data.reference')
            ?? Arr::get($payload, 'charges.0.reference_id')
            ?? '');

        $query = PaymentTransaction::query();

        if ($externalId !== '') {
            $query->orWhere('external_id', $externalId);
        }

        if ($reference !== '') {
            $query->orWhere('reference', $reference);
        }

        $providerLower = strtolower($provider);
        if ($providerLower !== '') {
            $query->orWhere(function ($builder) use ($providerLower, $reference) {
                $builder->where('provider', $providerLower);
                if ($reference !== '') {
                    $builder->where('reference', $reference);
                }
            });
        }

        return $query->latest('id')->first();
    }

    private function normalizeStatus(string $gatewayStatus): string
    {
        $status = strtolower(trim($gatewayStatus));

        return match ($status) {
            'paid', 'approved', 'settled', 'succeeded', 'authorized' => 'paid',
            'refunded', 'chargedback', 'chargeback' => 'refunded',
            'cancelled', 'canceled', 'voided' => 'cancelled',
            'failed', 'denied' => 'failed',
            'expired' => 'expired',
            default => 'pending',
        };
    }

    private function syncDomainEntities(PaymentTransaction $transaction, string $status): void
    {
        if ($status !== 'paid') {
            return;
        }

        if ($transaction->car_id) {
            $car = Cars::query()->find($transaction->car_id);
            if ($car) {
                $car->payment_status = 'paid';
                $car->status = 'finalizado';
                $car->saida = $car->saida ?: now();
                $car->paid_at = $car->paid_at ?: now();
                $car->payment_reference = $car->payment_reference ?: $transaction->reference;
                $car->payment_provider = $car->payment_provider ?: $transaction->provider;
                $car->payment_method = $car->payment_method ?: $transaction->method;
                $car->save();

                $this->ensureAccountingEntry($transaction, 'Ticket ' . $car->placa);
            }
        }

        if ($transaction->parking_reservation_id) {
            $reservation = ParkingReservation::query()->find($transaction->parking_reservation_id);
            if ($reservation) {
                $reservation->payment_status = 'paid';
                if ($reservation->status === ParkingReservation::STATUS_PENDING) {
                    $reservation->status = ParkingReservation::STATUS_CONFIRMED;
                }
                $reservation->prepaid_amount_cents = max((int) $reservation->prepaid_amount_cents, (int) $transaction->amount_cents);
                $reservation->save();
            }
        }

        if ($transaction->monthly_billing_cycle_id) {
            $cycle = MonthlyBillingCycle::query()->find($transaction->monthly_billing_cycle_id);
            if ($cycle) {
                $cycle->status = 'paid';
                $cycle->paid_at = now();
                $cycle->save();

                $subscriber = MonthlySubscriber::query()->find($cycle->monthly_subscriber_id);
                if ($subscriber) {
                    $subscriber->delinquent_since = null;
                    $subscriber->blocked_at = null;
                    $subscriber->is_active = true;
                    $subscriber->access_enabled = true;
                    $subscriber->save();
                }

                $this->ensureAccountingEntry($transaction, 'Mensalidade ' . $cycle->reference);
            }
        }
    }

    private function ensureAccountingEntry(PaymentTransaction $transaction, string $description): void
    {
        $alreadyExists = AccountingEntry::query()
            ->where('notes', 'payment_transaction:' . $transaction->id)
            ->exists();

        if ($alreadyExists) {
            return;
        }

        AccountingEntry::query()->create([
            'type' => 'receita',
            'category' => 'pagamento',
            'description' => $description,
            'amount' => ((int) $transaction->amount_cents) / 100,
            'occurred_at' => now()->toDateString(),
            'payment_method' => $transaction->method,
            'notes' => 'payment_transaction:' . $transaction->id,
        ]);
    }
}
