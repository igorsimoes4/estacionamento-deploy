<?php

use App\Http\Controllers\CarsController;
use App\Http\Controllers\Api\AnprController;
use App\Http\Controllers\Api\GateIntegrationController;
use App\Http\Controllers\Api\HealthController;
use App\Http\Controllers\Api\PaymentWebhookController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('/cars', [CarsController::class, 'index']);
Route::post('/payments/webhooks/{provider}', [PaymentWebhookController::class, 'handle'])
    ->name('api.payments.webhook');

Route::middleware('integration.token')->prefix('v1')->group(function () {
    Route::get('/status', [GateIntegrationController::class, 'status'])->name('api.integration.status');
    Route::post('/gate/entry', [GateIntegrationController::class, 'entry'])->name('api.integration.gate.entry');
    Route::post('/gate/exit', [GateIntegrationController::class, 'exit'])->name('api.integration.gate.exit');
    Route::post('/anpr/ingest', [AnprController::class, 'ingest'])->name('api.integration.anpr.ingest');
    Route::get('/health', [HealthController::class, 'index'])->name('api.integration.health');
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
