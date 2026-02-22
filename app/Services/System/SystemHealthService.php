<?php

namespace App\Services\System;

use App\Models\IntegrationEndpoint;
use App\Models\SystemHealthCheck;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SystemHealthService
{
    public function runAll(): array
    {
        $checks = [
            'database' => $this->checkDatabase(),
            'storage' => $this->checkStorage(),
            'integrations' => $this->checkIntegrations(),
            'queue' => $this->checkQueue(),
        ];

        foreach ($checks as $key => $result) {
            SystemHealthCheck::query()->updateOrCreate(
                ['check_key' => $key],
                [
                    'status' => $result['status'],
                    'message' => $result['message'],
                    'details' => $result['details'],
                    'checked_at' => now(),
                ]
            );
        }

        return $checks;
    }

    private function checkDatabase(): array
    {
        try {
            DB::select('SELECT 1 AS ok');
            return $this->ok('Banco conectado.', ['driver' => DB::getDriverName()]);
        } catch (\Throwable $e) {
            return $this->fail('Falha de conexao com banco.', ['error' => $e->getMessage()]);
        }
    }

    private function checkStorage(): array
    {
        try {
            $disk = Storage::disk('local');
            $path = 'health-check-' . now()->format('YmdHis') . '.txt';
            $disk->put($path, 'ok');
            $disk->delete($path);

            return $this->ok('Storage local com escrita/leitura.', ['disk' => 'local']);
        } catch (\Throwable $e) {
            return $this->fail('Falha no storage.', ['error' => $e->getMessage()]);
        }
    }

    private function checkIntegrations(): array
    {
        $total = IntegrationEndpoint::query()->where('is_active', true)->count();

        return $this->ok('Integracoes monitoradas.', ['active_integrations' => $total]);
    }

    private function checkQueue(): array
    {
        $driver = (string) config('queue.default', 'sync');

        if ($driver === 'sync') {
            return $this->ok('Fila em modo sync.', ['driver' => $driver]);
        }

        return $this->ok('Fila configurada.', ['driver' => $driver]);
    }

    private function ok(string $message, array $details = []): array
    {
        return ['status' => 'ok', 'message' => $message, 'details' => $details];
    }

    private function fail(string $message, array $details = []): array
    {
        return ['status' => 'fail', 'message' => $message, 'details' => $details];
    }
}
