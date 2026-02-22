<?php

namespace App\Services\System;

use App\Models\Cars;
use App\Models\MonthlySubscriber;
use App\Models\PaymentTransaction;
use App\Models\SystemBackup;
use Illuminate\Support\Facades\Storage;

class SystemBackupService
{
    public function createJsonSnapshot(): SystemBackup
    {
        $filename = 'backups/backup-' . now()->format('Ymd-His') . '.json';

        $backup = SystemBackup::query()->create([
            'backup_type' => 'app',
            'storage_disk' => 'local',
            'path' => $filename,
            'status' => 'started',
            'started_at' => now(),
        ]);

        try {
            $payload = [
                'generated_at' => now()->toIso8601String(),
                'cars' => Cars::query()->orderByDesc('id')->limit(1000)->get()->toArray(),
                'monthly_subscribers' => MonthlySubscriber::query()->orderByDesc('id')->limit(1000)->get()->toArray(),
                'payment_transactions' => PaymentTransaction::query()->orderByDesc('id')->limit(1000)->get()->toArray(),
            ];

            Storage::disk('local')->put($filename, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            $size = Storage::disk('local')->size($filename);

            $backup->status = 'completed';
            $backup->size_bytes = $size;
            $backup->finished_at = now();
            $backup->save();

            return $backup;
        } catch (\Throwable $e) {
            $backup->status = 'failed';
            $backup->error_message = $e->getMessage();
            $backup->finished_at = now();
            $backup->save();

            throw $e;
        }
    }
}
