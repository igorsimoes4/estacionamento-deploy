<?php

use App\Http\Controllers\CarsController;
use App\Http\Controllers\EstacionamentoController;
use App\Http\Controllers\PembayaranController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

/*
|--------------------------------------------------------------------------
| Rota Principal do Projeto
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return redirect('painel');
});


/*
|--------------------------------------------------------------------------
| Rota Painel Administrativo
|--------------------------------------------------------------------------
*/
Route::prefix('painel')->group(function(){
/*
|--------------------------------------------------------------------------
| Rota Painel Administrativo Dashboard
|--------------------------------------------------------------------------
*/
    Route::get('/', [EstacionamentoController::class, 'index'])->name('home');

/*
|--------------------------------------------------------------------------
| Rota Painel Administrativo Carros ( Cadastro, Edição e Finalizar )
|--------------------------------------------------------------------------
*/
    Route::resource('/cars', CarsController::class);

/*
|--------------------------------------------------------------------------
| Rota Painel Administrativo Visualizar o carro com Modal
|--------------------------------------------------------------------------
*/
    Route::get('/cars/showmodal/{car}', [CarsController::class, 'showModal'])->name('cars.modal');

/*
|--------------------------------------------------------------------------
| Rota Painel Administrativo Impressão comprovante Carro estacionado
|--------------------------------------------------------------------------
*/
    Route::post('/pembayaran/print', [PembayaranController::class, 'print'])->name('pembayaran.print');

/*
|--------------------------------------------------------------------------
| Rota Painel Administrativo Informações Principais do Estacionamento
|-------------------------------------------------------------------------
*/
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings');

    /*
|--------------------------------------------------------------------------
| Rota Painel Administrativo Edição de Informações do Estacionamento
|--------------------------------------------------------------------------
*/
    Route::post('/settings/info', [SettingsController::class, 'editSettings'])->name('editSettings');

/*
|--------------------------------------------------------------------------
| Rota Painel Administrativo Preço para os Carros
|--------------------------------------------------------------------------
*/
    Route::get('/settings/price-car', [SettingsController::class, 'priceCar'])->name('priceCar');

/*
|--------------------------------------------------------------------------
| Rota Painel Administrativo Edição de Preço para Carros
|--------------------------------------------------------------------------
*/
    Route::post('/settings/price-car/edit', [SettingsController::class, 'editPriceCar'])->name('editPriceCar');

/*
|--------------------------------------------------------------------------
| Rota Painel Administrativo Preço para Motos
|--------------------------------------------------------------------------
*/
    Route::get('/settings/price-motorcycle', [SettingsController::class, 'priceMotorcycle'])->name('priceMotorcycle');

/*
|--------------------------------------------------------------------------
| Rota Painel Administrativo Edição de Preço para Motos
|--------------------------------------------------------------------------
*/
    Route::post('/settings/price-motorcycle/edit', [SettingsController::class, 'editPriceMotorcycle'])->name('editPriceMotorcycle');

/*
|--------------------------------------------------------------------------
| Rota Painel Administrativo Preço para Caminhonetes
|--------------------------------------------------------------------------
*/
    Route::get('/settings/price-truck', [SettingsController::class, 'priceTruck'])->name('priceTruck');

/*
|--------------------------------------------------------------------------
| Rota Painel Administrativo Edição de Preço para Caminhonetes
|--------------------------------------------------------------------------
*/
    Route::post('/settings/price-truck/edit', [SettingsController::class, 'editPriceTruck'])->name('editPriceTruck');

/*
|--------------------------------------------------------------------------
| Rota Painel Administrativo Pesquisa
|--------------------------------------------------------------------------
*/

    Route::post('/cars/search', [CarsController::class, 'search'])->name('search');

});
