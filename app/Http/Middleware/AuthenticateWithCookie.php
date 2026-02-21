<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;

class AuthenticateWithCookie
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->cookie('auth_token');

        if (!empty($token)) {
            try {
                if (Auth::guard('web')->setToken($token)->check()) {
                    return $next($request);
                }
            } catch (\Throwable $e) {
                return redirect()
                    ->route('login')
                    ->withErrors(['message' => 'Sua sessao expirou. Faca login novamente.'])
                    ->withCookie(Cookie::forget('auth_token'));
            }
        }

        return redirect()
            ->route('login')
            ->withErrors(['message' => 'Voce precisa estar logado para acessar esta pagina.'])
            ->withCookie(Cookie::forget('auth_token'));
    }
}
