<?php

namespace App\Http\Controllers;

use App\Models\IntegrationEndpoint;
use App\Models\SystemHealthCheck;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class IntegrationController extends Controller
{
    public function index(): View
    {
        $integrations = IntegrationEndpoint::query()->orderBy('name')->get();
        $healthChecks = SystemHealthCheck::query()->latest('checked_at')->limit(30)->get();

        return view('integrations.index', compact('integrations', 'healthChecks'));
    }

    public function store(Request $request): RedirectResponse
    {
        $payload = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'type' => ['required', 'in:anpr,cancela,catraca,fiscal,app,webhook,payment'],
            'base_url' => ['nullable', 'url', 'max:255'],
            'auth_token' => ['nullable', 'string', 'max:255'],
            'auth_secret' => ['nullable', 'string', 'max:255'],
            'settings' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        IntegrationEndpoint::query()->create([
            'name' => $payload['name'],
            'type' => $payload['type'],
            'base_url' => $payload['base_url'] ?? null,
            'auth_token' => $payload['auth_token'] ?? null,
            'auth_secret' => $payload['auth_secret'] ?? null,
            'settings' => $this->decodeSettings($payload['settings'] ?? null),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return back()->with('create', 'Integracao cadastrada com sucesso.');
    }

    public function update(Request $request, IntegrationEndpoint $integration): RedirectResponse
    {
        $payload = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'base_url' => ['nullable', 'url', 'max:255'],
            'auth_token' => ['nullable', 'string', 'max:255'],
            'auth_secret' => ['nullable', 'string', 'max:255'],
            'settings' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $integration->fill([
            'name' => $payload['name'],
            'base_url' => $payload['base_url'] ?? null,
            'auth_token' => $payload['auth_token'] ?? null,
            'auth_secret' => $payload['auth_secret'] ?? null,
            'settings' => $this->decodeSettings($payload['settings'] ?? null),
            'is_active' => $request->boolean('is_active', true),
        ])->save();

        return back()->with('create', 'Integracao atualizada.');
    }

    private function decodeSettings(?string $raw): ?array
    {
        if (!$raw) {
            return null;
        }

        $decoded = json_decode($raw, true);

        return is_array($decoded) ? $decoded : null;
    }
}
