<?php

namespace App\Providers;

use App\Models\Estacionamento;
use App\Models\Settings;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

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
        $estacionamento = Settings::find(1);
        if ($estacionamento) {
            Log::info('Estacionamento encontrado:', ['nome' => $estacionamento['nome_da_empresa']]);

            config(["adminlte.logo" => '<span class="break-word" style="word-wrap: break-word !important;
    white-space: normal !important;">' . $estacionamento['nome_da_empresa'] . '</span>']);
        } else {
            Log::warning('Estacionamento não encontrado');
        }

        config(["adminlte.logo_img" => "/public/img/LogoEstacionamento.png"]);
    }
}
