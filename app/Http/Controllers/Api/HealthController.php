<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SystemHealthCheck;
use App\Services\System\SystemHealthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HealthController extends Controller
{
    public function index(Request $request, SystemHealthService $service): JsonResponse
    {
        if ($request->boolean('run')) {
            $service->runAll();
        }

        $checks = SystemHealthCheck::query()->latest('checked_at')->limit(20)->get();

        return response()->json([
            'status' => 'ok',
            'checks' => $checks,
            'timestamp' => now()->toIso8601String(),
        ]);
    }
}
