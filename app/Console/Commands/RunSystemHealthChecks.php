<?php

namespace App\Console\Commands;

use App\Services\System\SystemHealthService;
use Illuminate\Console\Command;

class RunSystemHealthChecks extends Command
{
    protected $signature = 'system:health-check';

    protected $description = 'Executa verificacoes de saude do sistema e persiste historico.';

    public function handle(SystemHealthService $service): int
    {
        $checks = $service->runAll();

        foreach ($checks as $key => $result) {
            $this->line(strtoupper($key) . ': ' . strtoupper((string) ($result['status'] ?? 'unknown')) . ' - ' . (string) ($result['message'] ?? ''));
        }

        return self::SUCCESS;
    }
}
