<?php

namespace App\Services\Parking;

use App\Models\MonthlyBillingCycle;
use App\Models\MonthlySubscriber;
use App\Models\NotificationLog;
use App\Models\PaymentTransaction;
use App\Services\Payments\MonthlySubscriberBoletoService;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class RecurringBillingService
{
    public function run(?string $competency = null): Collection
    {
        $competency ??= now()->format('Y-m');
        $dueDate = $this->buildDueDate($competency);

        $created = collect();

        MonthlySubscriber::query()
            ->where('is_active', true)
            ->where('auto_renew_enabled', true)
            ->chunkById(100, function ($subscribers) use ($competency, $dueDate, $created) {
                foreach ($subscribers as $subscriber) {
                    $existing = MonthlyBillingCycle::query()
                        ->where('monthly_subscriber_id', $subscriber->id)
                        ->where('competency', $competency)
                        ->first();

                    if ($existing) {
                        continue;
                    }

                    $amountCents = (int) round((float) $subscriber->monthly_fee * 100);
                    $reference = 'MS-' . $subscriber->id . '-' . str_replace('-', '', $competency);

                    $cycle = MonthlyBillingCycle::query()->create([
                        'monthly_subscriber_id' => $subscriber->id,
                        'reference' => $reference,
                        'competency' => $competency,
                        'due_date' => $dueDate->toDateString(),
                        'amount_cents' => $amountCents,
                        'total_amount_cents' => $amountCents,
                        'status' => 'pending',
                    ]);

                    $boleto = app(MonthlySubscriberBoletoService::class)->generateForSubscriber($subscriber, true);

                    $transaction = PaymentTransaction::query()->create([
                        'reference' => $reference,
                        'provider' => (string) ($boleto['provider'] ?? 'manual'),
                        'method' => 'boleto',
                        'status' => strtolower((string) ($boleto['status'] ?? 'pending')),
                        'type' => 'recurring',
                        'amount_cents' => $amountCents,
                        'currency' => 'BRL',
                        'monthly_subscriber_id' => $subscriber->id,
                        'monthly_billing_cycle_id' => $cycle->id,
                        'external_id' => $boleto['reference'] ?? null,
                        'payment_url' => $boleto['url'] ?? null,
                        'barcode' => $boleto['barcode'] ?? null,
                        'digitable_line' => $boleto['digitable_line'] ?? null,
                        'due_date' => $dueDate->toDateString(),
                    ]);

                    $cycle->payment_transaction_id = $transaction->id;
                    $cycle->save();

                    if (!empty($subscriber->email)) {
                        NotificationLog::query()->create([
                            'channel' => 'email',
                            'recipient' => $subscriber->email,
                            'title' => 'Mensalidade gerada',
                            'message' => 'Sua mensalidade de ' . $competency . ' foi gerada. Referencia: ' . $reference,
                            'status' => 'queued',
                            'notifiable_type' => MonthlySubscriber::class,
                            'notifiable_id' => $subscriber->id,
                            'scheduled_at' => now(),
                        ]);
                    }

                    $created->push($cycle);
                }
            });

        return $created;
    }

    private function buildDueDate(string $competency): Carbon
    {
        [$year, $month] = explode('-', $competency);
        return Carbon::createFromDate((int) $year, (int) $month, 10)->startOfDay();
    }
}
