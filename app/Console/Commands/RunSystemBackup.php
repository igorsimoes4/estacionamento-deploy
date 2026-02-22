<?php

namespace App\Console\Commands;

use App\Services\System\SystemBackupService;
use Illuminate\Console\Command;

class RunSystemBackup extends Command
{
    protected $signature = 'system:backup-run';

    protected $description = 'Gera backup JSON da base operacional para restauracao rapida.';

    public function handle(SystemBackupService $service): int
    {
        try {
            $backup = $service->createJsonSnapshot();
            $this->info('Backup concluido: ' . $backup->path . ' (' . (int) ($backup->size_bytes ?? 0) . ' bytes)');
            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Falha ao gerar backup: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}
