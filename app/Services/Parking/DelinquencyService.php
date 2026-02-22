<?php

namespace App\Services\Parking;

use App\Models\MonthlyBillingCycle;
use App\Models\MonthlySubscriber;
use App\Models\NotificationLog;
use Carbon\Carbon;

class DelinquencyService
{
    public function process(?Carbon $today = null): array
    {
        $today ??= now()->startOfDay();
        $updated = 0;
        $blocked = 0;

        $cycles = MonthlyBillingCycle::query()
            ->open()
            ->whereDate('due_date', '<', $today)
            ->with('subscriber')
            ->get();

        foreach ($cycles as $cycle) {
            /** @var MonthlySubscriber|null $subscriber */
            $subscriber = $cycle->subscriber;

            if (!$subscriber) {
                continue;
            }

            $daysLate = max(1, Carbon::parse($cycle->due_date)->diffInDays($today));
            $baseAmount = (int) $cycle->amount_cents;

            $fineCents = (int) round($baseAmount * ((float) $subscriber->late_fee_percent / 100));
            $interestCents = (int) round($baseAmount * ((float) $subscriber->daily_interest_percent / 100) * $daysLate);

            $cycle->fine_cents = $fineCents;
            $cycle->interest_cents = $interestCents;
            $cycle->total_amount_cents = $baseAmount + $fineCents + $interestCents;
            $cycle->status = 'overdue';
            $cycle->save();

            if (!$subscriber->delinquent_since) {
                $subscriber->delinquent_since = Carbon::parse($cycle->due_date)->toDateString();
            }

            if ($daysLate >= 15 && !$subscriber->blocked_at) {
                $subscriber->blocked_at = now();
                $subscriber->access_enabled = false;
                $blocked++;
            }

            $subscriber->save();

            $this->enqueueAlerts($subscriber, $cycle, $daysLate);
            $updated++;
        }

        return [
            'cycles_updated' => $updated,
            'subscribers_blocked' => $blocked,
        ];
    }

    private function enqueueAlerts(MonthlySubscriber $subscriber, MonthlyBillingCycle $cycle, int $daysLate): void
    {
        $message = sprintf(
            'Sua mensalidade %s esta vencida ha %d dia(s). Valor atualizado: R$ %s',
            $cycle->reference,
            $daysLate,
            number_format(((int) $cycle->total_amount_cents) / 100, 2, ',', '.')
        );

        if (!empty($subscriber->email)) {
            NotificationLog::query()->create([
                'channel' => 'email',
                'recipient' => $subscriber->email,
                'title' => 'Mensalidade em atraso',
                'message' => $message,
                'status' => 'queued',
                'notifiable_type' => MonthlySubscriber::class,
                'notifiable_id' => $subscriber->id,
                'scheduled_at' => now(),
            ]);
        }

        if (!empty($subscriber->phone)) {
            NotificationLog::query()->create([
                'channel' => 'whatsapp',
                'recipient' => $subscriber->phone,
                'title' => 'Mensalidade em atraso',
                'message' => $message,
                'status' => 'queued',
                'notifiable_type' => MonthlySubscriber::class,
                'notifiable_id' => $subscriber->id,
                'scheduled_at' => now(),
            ]);
        }
    }
}
