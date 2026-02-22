<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cars;
use App\Services\Parking\ParkingSpotAllocatorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AnprController extends Controller
{
    public function ingest(Request $request, ParkingSpotAllocatorService $allocator): JsonResponse
    {
        $payload = $request->validate([
            'plate' => ['required', 'string', 'max:12'],
            'direction' => ['required', 'in:entry,exit'],
            'confidence' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'camera_id' => ['nullable', 'string', 'max:120'],
            'sector_id' => ['nullable', 'integer', 'exists:parking_sectors,id'],
            'vehicle_type' => ['nullable', 'in:carro,moto,caminhonete'],
        ]);

        $plate = $this->normalizePlate($payload['plate']);

        if (!$plate) {
            return response()->json(['ok' => false, 'message' => 'Placa invalida para OCR.'], 422);
        }

        if ($payload['direction'] === 'entry') {
            $existing = Cars::parked()->where('placa', $plate)->latest('id')->first();
            if ($existing) {
                return response()->json(['ok' => true, 'message' => 'Entrada ja registrada.', 'car_id' => $existing->id]);
            }

            $car = Cars::query()->create([
                'modelo' => 'Leitura ANPR',
                'placa' => $plate,
                'entrada' => now()->format('H:i:s'),
                'tipo_car' => $payload['vehicle_type'] ?? 'carro',
                'preco' => 0,
                'entry_source' => 'anpr',
                'anpr_confidence' => isset($payload['confidence']) ? (float) $payload['confidence'] : null,
                'payment_reference' => $payload['camera_id'] ?? null,
            ]);

            $allocator->occupySpotForCar($car, isset($payload['sector_id']) ? (int) $payload['sector_id'] : null);

            return response()->json(['ok' => true, 'car_id' => $car->id, 'plate' => $plate]);
        }

        $car = Cars::parked()->where('placa', $plate)->latest('id')->first();

        if (!$car) {
            return response()->json(['ok' => false, 'message' => 'Nao existe entrada ativa para esta placa.'], 404);
        }

        $minutes = max(1, now()->diffInMinutes($car->created_at));
        $price = round(($minutes / 60) * 5, 2);

        $car->preco = $price;
        $car->status = 'finalizado';
        $car->saida = now();
        $car->payment_method = 'dinheiro';
        $car->payment_provider = 'manual';
        $car->payment_status = 'pending';
        $car->save();

        $allocator->releaseSpotByCar($car);

        return response()->json(['ok' => true, 'car_id' => $car->id, 'amount_due' => $price]);
    }

    private function normalizePlate(string $plate): ?string
    {
        $plate = strtoupper(trim($plate));
        $plate = preg_replace('/\s+/', '', $plate);

        if (preg_match('/^[A-Z]{3}-\d{4}$/', $plate) === 1) {
            return $plate;
        }

        if (preg_match('/^[A-Z]{3}\d[A-Z]\d{2}$/', $plate) === 1) {
            return substr($plate, 0, 3) . '-' . substr($plate, 3);
        }

        return null;
    }
}
