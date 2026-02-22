<?php

namespace App\Http\Controllers;

use App\Models\Cars;
use App\Models\ParkingSector;
use App\Models\ParkingSpot;
use App\Services\Parking\DynamicPricingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ParkingOperationsController extends Controller
{
    public function index(DynamicPricingService $pricingService): View
    {
        $sectors = ParkingSector::query()
            ->with(['spots' => fn ($query) => $query->orderBy('code')])
            ->orderBy('name')
            ->get();

        $summary = [
            'active_cars' => Cars::parked()->count(),
            'finished_today' => Cars::finished()->whereDate('saida', today())->count(),
            'occupancy_percent' => $pricingService->currentOccupancyPercent(),
            'occupied_spots' => ParkingSpot::query()->where('status', ParkingSpot::STATUS_OCCUPIED)->count(),
            'available_spots' => ParkingSpot::query()->where('status', ParkingSpot::STATUS_AVAILABLE)->count(),
        ];

        return view('operations.map', compact('sectors', 'summary'));
    }

    public function storeSector(Request $request): RedirectResponse
    {
        $payload = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'code' => ['required', 'string', 'max:30', 'unique:parking_sectors,code'],
            'capacity' => ['required', 'integer', 'min:1', 'max:10000'],
            'color' => ['nullable', 'string', 'max:16'],
            'map_rows' => ['nullable', 'integer', 'min:1', 'max:30'],
            'map_columns' => ['nullable', 'integer', 'min:1', 'max:60'],
            'notes' => ['nullable', 'string'],
        ]);

        ParkingSector::query()->create([
            'name' => $payload['name'],
            'code' => strtoupper($payload['code']),
            'capacity' => (int) $payload['capacity'],
            'color' => $payload['color'] ?? '#0f6c74',
            'map_rows' => (int) ($payload['map_rows'] ?? 5),
            'map_columns' => (int) ($payload['map_columns'] ?? 10),
            'notes' => $payload['notes'] ?? null,
        ]);

        return redirect()->route('operations.map')->with('create', 'Setor criado com sucesso.');
    }

    public function storeSpot(Request $request): RedirectResponse
    {
        $payload = $request->validate([
            'parking_sector_id' => ['required', 'integer', 'exists:parking_sectors,id'],
            'code' => ['required', 'string', 'max:20'],
            'vehicle_type' => ['required', 'in:carro,moto,caminhonete,geral'],
        ]);

        $exists = ParkingSpot::query()
            ->where('parking_sector_id', (int) $payload['parking_sector_id'])
            ->where('code', strtoupper((string) $payload['code']))
            ->exists();

        if ($exists) {
            return redirect()->route('operations.map')->with('error', 'Ja existe uma vaga com este codigo no setor.');
        }

        ParkingSpot::query()->create([
            'parking_sector_id' => (int) $payload['parking_sector_id'],
            'code' => strtoupper((string) $payload['code']),
            'vehicle_type' => $payload['vehicle_type'],
            'status' => ParkingSpot::STATUS_AVAILABLE,
        ]);

        return redirect()->route('operations.map')->with('create', 'Vaga criada com sucesso.');
    }

    public function updateSpotStatus(Request $request, ParkingSpot $spot): RedirectResponse
    {
        $payload = $request->validate([
            'status' => ['required', 'in:available,reserved,occupied,blocked,maintenance'],
        ]);

        $spot->status = $payload['status'];

        if ($spot->status !== ParkingSpot::STATUS_OCCUPIED) {
            $spot->current_car_id = null;
            $spot->occupied_since = null;
        }

        if (!in_array($spot->status, [ParkingSpot::STATUS_RESERVED, ParkingSpot::STATUS_OCCUPIED], true)) {
            $spot->current_reservation_id = null;
        }

        $spot->save();

        return redirect()->route('operations.map')->with('create', 'Status da vaga atualizado.');
    }
}
