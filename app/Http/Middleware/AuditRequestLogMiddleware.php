<?php

namespace App\Http\Middleware;

use App\Services\Audit\AuditLogger;
use Closure;
use Illuminate\Http\Request;

class AuditRequestLogMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $start = microtime(true);
        $response = $next($request);

        if (!$this->shouldLog($request)) {
            return $response;
        }

        AuditLogger::logRequest($request, $response, (microtime(true) - $start) * 1000);

        return $response;
    }

    private function shouldLog(Request $request): bool
    {
        if (!config('audit.enabled', true) || !config('audit.log_http_requests', true)) {
            return false;
        }

        if (in_array(strtoupper($request->method()), (array) config('audit.skip_methods', []), true)) {
            return false;
        }

        foreach ((array) config('audit.skip_paths', []) as $pattern) {
            if ($request->is($pattern)) {
                return false;
            }
        }

        return true;
    }
}

