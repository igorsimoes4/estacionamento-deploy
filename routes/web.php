<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AccountingController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\CarsController;
use App\Http\Controllers\CashShiftController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\DynamicPricingRuleController;
use App\Http\Controllers\EstacionamentoController;
use App\Http\Controllers\IntegrationController;
use App\Http\Controllers\PDFController;
use App\Http\Controllers\PembayaranController;
use App\Http\Controllers\NotificationCenterController;
use App\Http\Controllers\OperationsFinanceController;
use App\Http\Controllers\ParkingOperationsController;
use App\Http\Controllers\ParkingReservationController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\MonthlySubscriberController;
use App\Http\Controllers\MonthlySubscriberAccessController;
use App\Http\Controllers\UserManagementController;
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
Route::get('/login', function () {
    return view('login');
});
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::post('/refresh', [AuthController::class, 'refresh'])->name('refresh');

Route::get('/mensalista/login', [MonthlySubscriberAccessController::class, 'loginForm'])->name('monthly-access.login');
Route::post('/mensalista/login', [MonthlySubscriberAccessController::class, 'authenticate'])->name('monthly-access.authenticate');
Route::post('/mensalista/logout', [MonthlySubscriberAccessController::class, 'logout'])->name('monthly-access.logout');
Route::middleware(['monthly.auth'])->prefix('mensalista')->group(function () {
    Route::get('/painel', [MonthlySubscriberAccessController::class, 'dashboard'])->name('monthly-access.dashboard');
    Route::get('/boleto/baixar', [MonthlySubscriberAccessController::class, 'downloadBoleto'])->name('monthly-access.boleto.download');
});

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
        Route::resource('/cars', CarsController::class)->middleware('role:admin,operador,financeiro');


        /*
|--------------------------------------------------------------------------
| Rota Painel Administrativo Visualizar o carro com Modal
|--------------------------------------------------------------------------
*/
        Route::get('/cars/showmodal/{car}', [CarsController::class, 'showModal'])->middleware('role:admin,operador,financeiro')->name('cars.modal');

        /*
|--------------------------------------------------------------------------
| Rota Painel Administrativo Impressão comprovante Carro estacionado
|--------------------------------------------------------------------------
*/
        Route::post('/pembayaran/print', [PembayaranController::class, 'print'])->middleware('role:admin,operador')->name('pembayaran.print');
        Route::post('/pembayaran/printTicket', [PembayaranController::class, 'printTicket'])->middleware('role:admin,operador')->name('pembayaran.printTicket');

        /*
|--------------------------------------------------------------------------
| Rota Painel Administrativo Informações Principais do Estacionamento
|-------------------------------------------------------------------------
*/
        Route::get('/settings', [SettingsController::class, 'index'])->middleware('role:admin')->name('settings');

        /*
|--------------------------------------------------------------------------
| Rota Painel Administrativo Edição de Informações do Estacionamento
|--------------------------------------------------------------------------
*/
        Route::post('/settings/info', [SettingsController::class, 'editSettings'])->middleware('role:admin')->name('editSettings');

        Route::get('/settings/payments', [SettingsController::class, 'paymentSettings'])->middleware('role:admin')->name('paymentSettings');
        Route::post('/settings/payments', [SettingsController::class, 'editPaymentSettings'])->middleware('role:admin')->name('editPaymentSettings');

        /*
|--------------------------------------------------------------------------
| Rota Painel Administrativo Preço para os Carros
|--------------------------------------------------------------------------
*/
        Route::get('/settings/price-car', [SettingsController::class, 'priceCar'])->middleware('role:admin')->name('priceCar');

        /*
|--------------------------------------------------------------------------
| Rota Painel Administrativo Edição de Preço para Carros
|--------------------------------------------------------------------------
*/
        Route::post('/settings/price-car/edit', [SettingsController::class, 'editPriceCar'])->middleware('role:admin')->name('editPriceCar');

        /*
|--------------------------------------------------------------------------
| Rota Painel Administrativo Preço para Motos
|--------------------------------------------------------------------------
*/
        Route::get('/settings/price-motorcycle', [SettingsController::class, 'priceMotorcycle'])->middleware('role:admin')->name('priceMotorcycle');

        /*
|--------------------------------------------------------------------------
| Rota Painel Administrativo Edição de Preço para Motos
|--------------------------------------------------------------------------
*/
        Route::post('/settings/price-motorcycle/edit', [SettingsController::class, 'editPriceMotorcycle'])->middleware('role:admin')->name('editPriceMotorcycle');

        /*
|--------------------------------------------------------------------------
| Rota Painel Administrativo Preço para Caminhonetes
|--------------------------------------------------------------------------
*/
        Route::get('/settings/price-truck', [SettingsController::class, 'priceTruck'])->middleware('role:admin')->name('priceTruck');

        /*
|--------------------------------------------------------------------------
| Rota Painel Administrativo Edição de Preço para Caminhonetes
|--------------------------------------------------------------------------
*/
        Route::post('/settings/price-truck/edit', [SettingsController::class, 'editPriceTruck'])->middleware('role:admin')->name('editPriceTruck');
        Route::get('/settings/dynamic-pricing', [DynamicPricingRuleController::class, 'index'])->middleware('role:admin')->name('dynamic-pricing.index');
        Route::post('/settings/dynamic-pricing', [DynamicPricingRuleController::class, 'store'])->middleware('role:admin')->name('dynamic-pricing.store');
        Route::put('/settings/dynamic-pricing/{dynamicPricingRule}', [DynamicPricingRuleController::class, 'update'])->middleware('role:admin')->name('dynamic-pricing.update');
        Route::delete('/settings/dynamic-pricing/{dynamicPricingRule}', [DynamicPricingRuleController::class, 'destroy'])->middleware('role:admin')->name('dynamic-pricing.destroy');

        /*
|--------------------------------------------------------------------------
| Rota Painel Administrativo Pesquisa
|--------------------------------------------------------------------------
*/

        Route::match(['get', 'post'], '/cars/search', [CarsController::class, 'search'])->middleware('role:admin,operador,financeiro')->name('cars.search');

        /*
|--------------------------------------------------------------------------
| Rota Painel Administrativo Gerar PDF
|--------------------------------------------------------------------------
*/
        // Rota para mostrar os relatórios disponíveis
        Route::get('/relatorios', [PDFController::class, 'showReports'])->middleware('role:admin,financeiro')->name('reports.index');

        // Rota para gerar o relatório de veículos (Carros) em PDF
        Route::get('/car-mounth-pdf', [PDFController::class, 'generatePDFCars'])->name('generatePDFCars');

        // Rota para gerar o relatório de veículos (Carros) em Excel
        Route::get('/car-mounth-excel', [PDFController::class, 'generateExcelCars'])->name('generateExcelCars');

        // Rota para gerar o relatório de motocicletas em PDF
        Route::get('/motorcycle-mounth-pdf', [PDFController::class, 'generatePDFMotorcycle'])->name('generatePDFMotorcycle');

        // Rota para gerar o relatório de motocicletas em Excel
        Route::get('/motorcycle-mounth-excel', [PDFController::class, 'generateExcelMotorcycle'])->name('generateExcelMotorcycle');

        // Rota para gerar o relatório de caminhões em PDF
        Route::get('/truck-mounth-pdf', [PDFController::class, 'generatePDFTruck'])->name('generatePDFTruck');

        // Rota para gerar o relatório de caminhões em Excel
        Route::get('/truck-mounth-excel', [PDFController::class, 'generateExcelTruck'])->name('generateExcelTruck');

        // Rota para gerar o relatório de veículos finalizados em PDF
        Route::get('/finished-vehicles-pdf', [PDFController::class, 'generatePDFFinishedVehicles'])->name('generatePDFFinishedVehicles');

        Route::get('/relatorios/carros', [PDFController::class, 'generatePDFCars'])->name('generatePDFCars');
        Route::get('/relatorios/motos', [PDFController::class, 'generatePDFMotorcycle'])->name('generatePDFMotorcycle');
        Route::get('/relatorios/caminhonetes', [PDFController::class, 'generatePDFTruck'])->name('generatePDFTruck');
        Route::get('/relatorios/veiculos-finalizados', [PDFController::class, 'generatePDFFinishedVehicles'])->name('generatePDFFinishedVehicles');
        Route::get('/relatorios/veiculos-cliente/{client_id}', [PDFController::class, 'generatePDFClientVehicles'])->name('generatePDFClientVehicles');
        Route::get('/relatorios/entrada-saida', [PDFController::class, 'generatePDFEntryExit'])->name('generatePDFEntryExit');
        Route::get('/relatorios/financeiro', [PDFController::class, 'generatePDFFinancial'])->name('generatePDFFinancial');
        Route::get('/relatorios/pagamentos-por-metodo', [PDFController::class, 'generatePDFPaymentMethods'])->name('generatePDFPaymentMethods');
        Route::get('/relatorios/pagamentos-por-metodo-excel', [PDFController::class, 'generateExcelPaymentMethods'])->name('generateExcelPaymentMethods');
        Route::get('/relatorios/mensalistas-ativos', [PDFController::class, 'generatePDFActiveSubscribers'])->name('generatePDFActiveSubscribers');
        Route::get('/relatorios/ocupacao', [PDFController::class, 'generatePDFParkingOccupancy'])->name('generatePDFParkingOccupancy');
        Route::get('/relatorios/veiculos-estacionados', [PDFController::class, 'generatePDFCurrentlyParked'])->name('generatePDFCurrentlyParked');
        Route::get('/relatorios/infracoes', [PDFController::class, 'generatePDFViolations'])->name('generatePDFViolations');
        Route::get('/relatorios/finalizados-hoje', [PDFController::class, 'generatePDFFinishedVehiclesToday'])->name('generatePDFFinishedVehiclesToday');
        Route::get('/relatorios/finalizados-hoje-excel', [PDFController::class, 'generateExcelFinishedVehiclesToday'])->name('generateExcelFinishedVehiclesToday');
        Route::get('/relatorios/top-faturamento', [PDFController::class, 'generatePDFTopRevenueVehicles'])->name('generatePDFTopRevenueVehicles');
        Route::get('/relatorios/top-faturamento-excel', [PDFController::class, 'generateExcelTopRevenueVehicles'])->name('generateExcelTopRevenueVehicles');
        Route::get('/relatorios/faturamento-por-tipo', [PDFController::class, 'generatePDFRevenueByType'])->name('generatePDFRevenueByType');
        Route::get('/relatorios/faturamento-por-tipo-excel', [PDFController::class, 'generateExcelRevenueByType'])->name('generateExcelRevenueByType');
        Route::get('/relatorios/movimentacao-diaria', [PDFController::class, 'generatePDFDailyMovement'])->name('generatePDFDailyMovement');
        Route::get('/relatorios/movimentacao-diaria-excel', [PDFController::class, 'generateExcelDailyMovement'])->name('generateExcelDailyMovement');
        Route::get('/relatorios/mensalistas-vencendo', [PDFController::class, 'generatePDFExpiringSubscribers'])->name('generatePDFExpiringSubscribers');
        Route::get('/relatorios/mensalistas-vencendo-excel', [PDFController::class, 'generateExcelExpiringSubscribers'])->name('generateExcelExpiringSubscribers');
        Route::get('/relatorios/mensalistas-inativos', [PDFController::class, 'generatePDFInactiveSubscribers'])->name('generatePDFInactiveSubscribers');
        Route::get('/relatorios/mensalistas-inativos-excel', [PDFController::class, 'generateExcelInactiveSubscribers'])->name('generateExcelInactiveSubscribers');
        
        Route::resource('monthly-subscribers', MonthlySubscriberController::class)->middleware('role:admin,financeiro,operador');
        Route::get('get-vehicle-price/{type}', [MonthlySubscriberController::class, 'getVehiclePrice'])
            ->middleware('role:admin,financeiro,operador')
            ->name('get-vehicle-price');

        Route::resource('users', UserManagementController::class)->except(['show'])->middleware('role:admin');
        Route::resource('accounting', AccountingController::class)->except(['show'])->middleware('role:admin,financeiro');
        Route::get('/auditoria', [ActivityLogController::class, 'index'])->middleware('role:admin,financeiro')->name('audit.index');
        Route::get('/auditoria/exportar/csv', [ActivityLogController::class, 'exportCsv'])->middleware('role:admin,financeiro')->name('audit.export.csv');
        Route::get('/auditoria/exportar/pdf', [ActivityLogController::class, 'exportPdf'])->middleware('role:admin,financeiro')->name('audit.export.pdf');

        Route::get('/operacao/mapa', [ParkingOperationsController::class, 'index'])
            ->middleware('role:admin,operador')
            ->name('operations.map');
        Route::post('/operacao/setores', [ParkingOperationsController::class, 'storeSector'])
            ->middleware('role:admin,operador')
            ->name('operations.sectors.store');
        Route::post('/operacao/vagas', [ParkingOperationsController::class, 'storeSpot'])
            ->middleware('role:admin,operador')
            ->name('operations.spots.store');
        Route::patch('/operacao/vagas/{spot}/status', [ParkingOperationsController::class, 'updateSpotStatus'])
            ->middleware('role:admin,operador')
            ->name('operations.spots.status');

        Route::get('/reservas', [ParkingReservationController::class, 'index'])
            ->middleware('role:admin,operador')
            ->name('reservations.index');
        Route::post('/reservas', [ParkingReservationController::class, 'store'])
            ->middleware('role:admin,operador')
            ->name('reservations.store');
        Route::post('/reservas/{reservation}/check-in', [ParkingReservationController::class, 'checkIn'])
            ->middleware('role:admin,operador')
            ->name('reservations.checkin');
        Route::post('/reservas/{reservation}/cancelar', [ParkingReservationController::class, 'cancel'])
            ->middleware('role:admin,operador')
            ->name('reservations.cancel');

        Route::get('/caixa-turno', [CashShiftController::class, 'index'])
            ->middleware('role:admin,operador')
            ->name('cash-shifts.index');
        Route::post('/caixa-turno/abrir', [CashShiftController::class, 'open'])
            ->middleware('role:admin,operador')
            ->name('cash-shifts.open');
        Route::post('/caixa-turno/{cashShift}/movimento', [CashShiftController::class, 'addMovement'])
            ->middleware('role:admin,operador')
            ->name('cash-shifts.movement');
        Route::post('/caixa-turno/{cashShift}/fechar', [CashShiftController::class, 'close'])
            ->middleware('role:admin,operador')
            ->name('cash-shifts.close');

        Route::get('/operacao/financeiro', [OperationsFinanceController::class, 'index'])
            ->middleware('role:admin,financeiro')
            ->name('operations.finance');
        Route::post('/operacao/financeiro/recorrencia', [OperationsFinanceController::class, 'runRecurringBilling'])
            ->middleware('role:admin,financeiro')
            ->name('operations.finance.recurring');
        Route::post('/operacao/financeiro/inadimplencia', [OperationsFinanceController::class, 'runDelinquency'])
            ->middleware('role:admin,financeiro')
            ->name('operations.finance.delinquency');
        Route::post('/operacao/financeiro/fiscal', [OperationsFinanceController::class, 'issueFiscal'])
            ->middleware('role:admin,financeiro')
            ->name('operations.finance.fiscal');

        Route::get('/integracoes', [IntegrationController::class, 'index'])
            ->middleware('role:admin')
            ->name('integrations.index');
        Route::post('/integracoes', [IntegrationController::class, 'store'])
            ->middleware('role:admin')
            ->name('integrations.store');
        Route::put('/integracoes/{integration}', [IntegrationController::class, 'update'])
            ->middleware('role:admin')
            ->name('integrations.update');

        Route::get('/notificacoes', [NotificationCenterController::class, 'index'])
            ->middleware('role:admin,financeiro')
            ->name('notifications.index');
        Route::post('/notificacoes/dispatch', [NotificationCenterController::class, 'processQueue'])
            ->middleware('role:admin,financeiro')
            ->name('notifications.dispatch');
    });
});


