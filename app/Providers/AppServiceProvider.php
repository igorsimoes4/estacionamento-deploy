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

            config(["adminlte.logo" => $estacionamento['nome_da_empresa']]);
        } else {
            Log::warning('Estacionamento não encontrado');
        }
        Log::info('Setando Logo:', ['path' => asset("img/LogoEstacionamento.png")]);
        config(["adminlte.preloader.img.path" => asset("img/LogoEstacionamento.png")]);
        config(['adminlte.auth_logo.img' => asset('img/LogoEstacionamento.png')]);
        config(['adminlte.auth_logo.img.path' => asset('img/LogoEstacionamento.png')]);
        config(['adminlte.logo_img' => asset('img/LogoEstacionamento.png')]);
        config(['adminlte.favicon.path' => asset('img/LogoEstacionamento.png')]);
    }
}
