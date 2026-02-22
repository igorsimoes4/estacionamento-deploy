<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cars;
use App\Models\ParkingSpot;
use App\Services\Parking\ParkingSpotAllocatorService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GateIntegrationController extends Controller
{
    public function status(): JsonResponse
    {
        $activeCars = Cars::parked()->count();
        $available = ParkingSpot::query()->where('status', ParkingSpot::STATUS_AVAILABLE)->count();
        $occupied = ParkingSpot::query()->where('status', ParkingSpot::STATUS_OCCUPIED)->count();

        return response()->json([
            'active_cars' => $activeCars,
            'spots' => [
                'available' => $available,
                'occupied' => $occupied,
            ],
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    public function entry(Request $request, ParkingSpotAllocatorService $allocator): JsonResponse
    {
        $payload = $request->validate([
            'plate' => ['required', 'string', 'max:12'],
            'model' => ['nullable', 'string', 'max:120'],
            'vehicle_type' => ['nullable', 'in:carro,moto,caminhonete'],
            'sector_id' => ['nullable', 'integer', 'exists:parking_sectors,id'],
            'entry_at' => ['nullable', 'date'],
            'source' => ['nullable', 'string', 'max:20'],
        ]);

        $plate = $this->normalizePlate($payload['plate']);

        if (!$plate) {
            return response()->json(['ok' => false, 'message' => 'Placa invalida.'], 422);
        }

        $existing = Cars::parked()->where('placa', $plate)->first();

        if ($existing) {
            return response()->json(['ok' => true, 'message' => 'Veiculo ja estava ativo.', 'car_id' => $existing->id]);
        }

        $entryAt = isset($payload['entry_at']) ? Carbon::parse($payload['entry_at']) : now();

        $car = Cars::query()->create([
            'modelo' => $payload['model'] ?? 'Entrada API',
            'placa' => $plate,
            'entrada' => $entryAt->format('H:i:s'),
            'tipo_car' => $payload['vehicle_type'] ?? 'carro',
            'preco' => 0,
            'entry_source' => $payload['source'] ?? 'api',
            'created_at' => $entryAt,
        ]);

        $allocator->occupySpotForCar($car, isset($payload['sector_id']) ? (int) $payload['sector_id'] : null);

        return response()->json(['ok' => true, 'car_id' => $car->id, 'plate' => $plate]);
    }

    public function exit(Request $request, ParkingSpotAllocatorService $allocator): JsonResponse
    {
        $payload = $request->validate([
            'plate' => ['required', 'string', 'max:12'],
            'payment_method' => ['nullable', 'in:dinheiro,pix,boleto,cartao_credito,cartao_debito,transferencia,outro'],
            'payment_reference' => ['nullable', 'string', 'max:120'],
            'exit_at' => ['nullable', 'date'],
        ]);

        $plate = $this->normalizePlate($payload['plate']);

        if (!$plate) {
            return response()->json(['ok' => false, 'message' => 'Placa invalida.'], 422);
        }

        $car = Cars::parked()->where('placa', $plate)->latest('id')->first();

        if (!$car) {
            return response()->json(['ok' => false, 'message' => 'Veiculo nao encontrado.'], 404);
        }

        $exitAt = isset($payload['exit_at']) ? Carbon::parse($payload['exit_at']) : now();

        $minutes = max(1, Carbon::parse($car->created_at)->diffInMinutes($exitAt));
        $price = round(($minutes / 60) * 5, 2);

        $car->preco = $price;
        $car->status = 'finalizado';
        $car->saida = $exitAt;
        $car->payment_method = $payload['payment_method'] ?? 'dinheiro';
        $car->payment_provider = 'manual';
        $car->payment_status = 'paid';
        $car->payment_reference = $payload['payment_reference'] ?? null;
        $car->paid_at = $exitAt;
        $car->save();

        $allocator->releaseSpotByCar($car);

        return response()->json(['ok' => true, 'car_id' => $car->id, 'amount' => $price]);
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
