<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('parking:billing-run')->dailyAt('07:00');
        $schedule->command('parking:delinquency-run')->dailyAt('08:00');
        $schedule->command('parking:notifications-run --limit=200')->everyTenMinutes();
        $schedule->command('system:health-check')->hourly();
        $schedule->command('system:backup-run')->dailyAt('02:30');
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }

    protected $commands = [
        Commands\WebSocketServer::class,
    ];
}
