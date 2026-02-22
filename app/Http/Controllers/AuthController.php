<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\Audit\AuditLogger;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

class AuthController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function __construct()
    {
        $this->middleware('auth:web', ['except' => ['login', 'register']]);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $token = Auth::attempt($credentials);

        if (!$token) {
            AuditLogger::log('auth.login_failed', [
                'level' => 'warning',
                'description' => 'Tentativa de login administrativo falhou.',
                'metadata' => ['email' => $credentials['email'] ?? null],
            ]);

            Session::flash('error', 'Email e/ou senha invalidos.');
            return redirect()->route('login')->withInput($request->only('email'));
        }

        $user = Auth::user();
        if ($user && !$user->is_active) {
            Auth::logout();
            Session::flash('error', 'Usuario inativo. Procure o administrador.');
            return redirect()->route('login')->withInput($request->only('email'));
        }

        AuditLogger::log('auth.login_success', [
            'description' => 'Login administrativo efetuado com sucesso.',
            'metadata' => ['email' => $credentials['email'] ?? null],
        ]);

        $cookie = cookie(
            'auth_token',
            $token,
            60,
            null,
            null,
            $request->isSecure(),
            true,
            false,
            'Lax'
        );

        return redirect()->route('home')->with('status', 'success')->cookie($cookie);
    }

    public function register(Request $request)
    {
        $payload = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:6',
            'role' => 'nullable|in:admin,operador,financeiro',
        ]);

        $user = User::create([
            'name' => $payload['name'],
            'email' => $payload['email'],
            'password' => Hash::make($payload['password']),
            'role' => $payload['role'] ?? User::ROLE_ADMIN,
            'is_active' => true,
        ]);

        AuditLogger::log('auth.user_registered', [
            'description' => 'Novo usuario administrativo cadastrado.',
            'subject_type' => User::class,
            'subject_id' => (string) $user->id,
            'new_values' => ['name' => $user->name, 'email' => $user->email],
        ]);

        $token = Auth::login($user);

        $cookie = cookie('auth_token', $token, 60, null, null, $request->isSecure(), true, false, 'Lax');

        return redirect()->route('home')->with('status', 'success')->cookie($cookie);
    }

    public function logout()
    {
        $userId = Auth::id();

        try {
            Auth::logout();
        } catch (\Throwable $e) {
            // Ignore token errors and continue logout flow.
        }

        AuditLogger::log('auth.logout', [
            'description' => 'Logout administrativo efetuado.',
            'subject_type' => User::class,
            'subject_id' => $userId ? (string) $userId : null,
        ]);

        return redirect()->route('login')->withCookie(Cookie::forget('auth_token'));
    }

    public function refresh()
    {
        return response()->json([
            'status' => 'success',
            'user' => Auth::user(),
            'authorisation' => [
                'token' => Auth::refresh(),
                'type' => 'bearer',
            ],
        ]);
    }
}
