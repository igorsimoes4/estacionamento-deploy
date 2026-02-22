<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureIntegrationToken
{
    public function handle(Request $request, Closure $next)
    {
        $token = (string) config('services.integration.api_token', env('INTEGRATION_API_TOKEN', ''));

        if ($token === '') {
            abort(503, 'Token de integracao nao configurado.');
        }

        $sentToken = (string) ($request->bearerToken() ?: $request->header('X-Integration-Token', ''));

        if (!hash_equals($token, $sentToken)) {
            abort(401, 'Token de integracao invalido.');
        }

        return $next($request);
    }
}
