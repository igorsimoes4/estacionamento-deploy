<?php

namespace App\Console\Commands;

use App\Services\Parking\DelinquencyService;
use Illuminate\Console\Command;

class RunDelinquencyRoutine extends Command
{
    protected $signature = 'parking:delinquency-run';

    protected $description = 'Aplica multas/juros, alertas e bloqueios para mensalidades vencidas.';

    public function handle(DelinquencyService $service): int
    {
        $result = $service->process();

        $this->info('Ciclos atualizados: ' . (int) ($result['cycles_updated'] ?? 0));
        $this->info('Mensalistas bloqueados: ' . (int) ($result['subscribers_blocked'] ?? 0));

        return self::SUCCESS;
    }
}
