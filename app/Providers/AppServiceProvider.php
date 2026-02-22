<?php

namespace App\Providers;

use App\Models\ActivityLog;
use App\Models\Settings;
use App\Services\Audit\AuditLogger;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Throwable;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        try {
            $estacionamento = Settings::find(1);

            if ($estacionamento) {
                Log::info('Estacionamento encontrado:', ['nome' => $estacionamento['nome_da_empresa']]);
                config(['adminlte.logo' => $estacionamento['nome_da_empresa']]);
            } else {
                Log::warning('Estacionamento nao encontrado');
            }
        } catch (Throwable $e) {
            Log::warning('Falha ao carregar configuracoes de estacionamento no boot.', [
                'error' => $e->getMessage(),
            ]);
        }

        Log::info('Setando Logo:', ['path' => asset('img/LogoEstacionamento.png')]);
        config(['adminlte.preloader.img.path' => asset('img/LogoEstacionamento.png')]);
        config(['adminlte.auth_logo.img' => asset('img/LogoEstacionamento.png')]);
        config(['adminlte.auth_logo.img.path' => asset('img/LogoEstacionamento.png')]);
        config(['adminlte.logo_img' => asset('img/LogoEstacionamento.png')]);
        config(['adminlte.favicon.path' => asset('img/LogoEstacionamento.png')]);

        $this->registerAuditModelEvents();
    }

    private function registerAuditModelEvents(): void
    {
        if (!config('audit.enabled', true) || !config('audit.log_model_events', true)) {
            return;
        }

        Event::listen('eloquent.created: *', function (string $eventName, array $data): void {
            $this->logModelEvent('created', $data);
        });

        Event::listen('eloquent.updated: *', function (string $eventName, array $data): void {
            $this->logModelEvent('updated', $data);
        });

        Event::listen('eloquent.deleted: *', function (string $eventName, array $data): void {
            $this->logModelEvent('deleted', $data);
        });
    }

    private function logModelEvent(string $action, array $data): void
    {
        $model = $data[0] ?? null;

        if (!$model instanceof Model) {
            return;
        }

        if ($model instanceof ActivityLog) {
            return;
        }

        AuditLogger::logModelEvent($action, $model);
    }
}
