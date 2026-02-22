<?php

namespace App\Console\Commands;

use App\Services\Parking\RecurringBillingService;
use Illuminate\Console\Command;

class RunRecurringBilling extends Command
{
    protected $signature = 'parking:billing-run {--competency=}';

    protected $description = 'Gera ciclos de cobranca recorrente para mensalistas.';

    public function handle(RecurringBillingService $service): int
    {
        $competency = $this->option('competency');
        $cycles = $service->run(is_string($competency) && $competency !== '' ? $competency : null);

        $this->info('Ciclos gerados: ' . $cycles->count());

        return self::SUCCESS;
    }
}
