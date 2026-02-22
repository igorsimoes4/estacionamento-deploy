<?php

namespace App\Console\Commands;

use App\Services\Parking\NotificationCenterService;
use Illuminate\Console\Command;

class ProcessPaymentNotifications extends Command
{
    protected $signature = 'parking:notifications-run {--limit=100}';

    protected $description = 'Processa fila de notificacoes (email/whatsapp) do sistema.';

    public function handle(NotificationCenterService $service): int
    {
        $limit = (int) $this->option('limit');
        $result = $service->dispatchPending($limit > 0 ? $limit : 100);

        $this->info('Notificacoes enviadas: ' . (int) ($result['sent'] ?? 0));
        $this->info('Notificacoes com falha: ' . (int) ($result['failed'] ?? 0));

        return self::SUCCESS;
    }
}
