<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthenticateWithCookie
{
    public function handle(Request $request, Closure $next)
    {
        // Extrair o token do cookie
        $token = $request->cookie('auth_token');

        if ($token) {
            // Tentar autenticar o usuário com o token do cookie
            if (Auth::guard('web')->setToken($token)->check()) {
                return $next($request);
            }
        }

        // Se a autenticação falhar, redirecionar para a página de login ou retornar não autorizado
        return redirect()->route('login')->withErrors(['message' => 'Você precisa estar logado para acessar essa página.']);
    }
}

