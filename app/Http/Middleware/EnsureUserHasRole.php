<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string ...$roles)
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        if (method_exists($user, 'hasAnyRole') && $user->hasAnyRole($roles)) {
            return $next($request);
        }

        abort(403, 'Perfil sem permissao para acessar este recurso.');
    }
}
