<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CarsController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\EstacionamentoController;
use App\Http\Controllers\PDFController;
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
    return redirect(route('login'));
});
///////
Route::get('/login', function () {return view('login');});
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::post('/register', [AuthController::class, 'register'])->name('register');
Route::post('/refresh', [AuthController::class, 'refresh'])->name('refresh');

Route::middleware(['auth.cookie'])->group(function () {
    /*
    |--------------------------------------------------------------------------
    | Rota Painel Administrativo
    |--------------------------------------------------------------------------
    */
    Route::prefix('painel')->group(function () {
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
        Route::post('/cars/create', [CarsController::class, 'store'])->name('cars.store');


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
        Route::post('/pembayaran/printTicket', [PembayaranController::class, 'printTicket'])->name('pembayaran.printTicket');

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

        Route::post('/cars', [CarsController::class, 'search'])->name('search');

         /*
|--------------------------------------------------------------------------
| Rota Painel Administrativo Gerar PDF
|--------------------------------------------------------------------------
*/
        Route::get('/relatorios', [PDFController::class, 'showReports'])->name('reports.index');
        Route::get('/car-mounth', [PDFController::class, 'generatePDFCars'])->name('generatePDFCars');
        Route::get('/motorcycle-mounth', [PDFController::class, 'generatePDFMotorcycle'])->name('generatePDFMotorcycle');
        Route::get('/truck-mounth', [PDFController::class, 'generatePDFTruck'])->name('generatePDFTruck');
    });
});
