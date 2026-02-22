<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Parking\PaymentReconciliationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentWebhookController extends Controller
{
    public function handle(Request $request, string $provider, PaymentReconciliationService $service): JsonResponse
    {
        $payload = $request->all();

        $result = $service->registerWebhook(
            strtolower($provider),
            is_array($payload) ? $payload : ['raw' => $payload],
            (string) $request->header('X-Signature', '')
        );

        return response()->json($result);
    }
}
