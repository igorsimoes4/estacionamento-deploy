<?php

namespace App\Services\Parking;

use App\Models\NotificationLog;

class NotificationCenterService
{
    public function dispatchPending(int $limit = 100): array
    {
        $sent = 0;
        $failed = 0;

        $notifications = NotificationLog::query()
            ->whereIn('status', ['queued', 'retry'])
            ->where(function ($query) {
                $query->whereNull('scheduled_at')->orWhere('scheduled_at', '<=', now());
            })
            ->orderBy('id')
            ->limit($limit)
            ->get();

        foreach ($notifications as $notification) {
            try {
                // Ponto de extensao para providers reais (SMTP, WhatsApp API, etc.).
                $notification->status = 'sent';
                $notification->sent_at = now();
                $notification->provider_response = 'sent_locally';
                $notification->error_message = null;
                $notification->save();
                $sent++;
            } catch (\Throwable $e) {
                $notification->status = 'retry';
                $notification->error_message = $e->getMessage();
                $notification->save();
                $failed++;
            }
        }

        return [
            'sent' => $sent,
            'failed' => $failed,
        ];
    }
}
