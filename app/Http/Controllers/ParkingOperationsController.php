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
    public function index(Request $request, DynamicPricingService $pricingService): View
    {
        $filters = [
            'sector_id' => (int) $request->query('sector_id', 0),
            'status' => trim((string) $request->query('status', '')),
            'vehicle_type' => trim((string) $request->query('vehicle_type', '')),
            'q' => strtoupper(trim((string) $request->query('q', ''))),
            'with_spots' => $request->boolean('with_spots', false),
        ];

        if (!in_array($filters['status'], ['', 'available', 'reserved', 'occupied', 'blocked', 'maintenance'], true)) {
            $filters['status'] = '';
        }

        if (!in_array($filters['vehicle_type'], ['', 'carro', 'moto', 'caminhonete', 'geral'], true)) {
            $filters['vehicle_type'] = '';
        }

        $spotQuery = function ($query) use ($filters): void {
            $query->orderBy('code')->with(['car:id,placa', 'reservation:id,reference']);

            if ($filters['status'] !== '') {
                $query->where('status', $filters['status']);
            }

            if ($filters['vehicle_type'] !== '') {
                $query->where('vehicle_type', $filters['vehicle_type']);
            }

            if ($filters['q'] !== '') {
                $query->where('code', 'like', '%' . $filters['q'] . '%');
            }
        };

        $sectors = ParkingSector::query()
            ->when($filters['sector_id'] > 0, fn ($query) => $query->whereKey($filters['sector_id']))
            ->with(['spots' => $spotQuery])
            ->orderBy('name')
            ->get();

        if ($filters['with_spots']) {
            $sectors = $sectors->filter(fn ($sector) => $sector->spots->isNotEmpty())->values();
        }

        $spotStatusTotals = [
            ParkingSpot::STATUS_AVAILABLE => (int) ParkingSpot::query()->where('status', ParkingSpot::STATUS_AVAILABLE)->count(),
            ParkingSpot::STATUS_RESERVED => (int) ParkingSpot::query()->where('status', ParkingSpot::STATUS_RESERVED)->count(),
            ParkingSpot::STATUS_OCCUPIED => (int) ParkingSpot::query()->where('status', ParkingSpot::STATUS_OCCUPIED)->count(),
            ParkingSpot::STATUS_BLOCKED => (int) ParkingSpot::query()->where('status', ParkingSpot::STATUS_BLOCKED)->count(),
            ParkingSpot::STATUS_MAINTENANCE => (int) ParkingSpot::query()->where('status', ParkingSpot::STATUS_MAINTENANCE)->count(),
        ];

        $totalSpots = array_sum($spotStatusTotals);

        $visibleSpots = $sectors->sum(fn ($sector) => $sector->spots->count());
        $visibleOccupied = $sectors->sum(fn ($sector) => $sector->spots->where('status', ParkingSpot::STATUS_OCCUPIED)->count());

        $summary = [
            'active_cars' => Cars::parked()->count(),
            'finished_today' => Cars::finished()->whereDate('saida', today())->count(),
            'occupancy_percent' => $pricingService->currentOccupancyPercent(),
            'occupied_spots' => $spotStatusTotals[ParkingSpot::STATUS_OCCUPIED],
            'available_spots' => $spotStatusTotals[ParkingSpot::STATUS_AVAILABLE],
            'reserved_spots' => $spotStatusTotals[ParkingSpot::STATUS_RESERVED],
            'blocked_spots' => $spotStatusTotals[ParkingSpot::STATUS_BLOCKED],
            'maintenance_spots' => $spotStatusTotals[ParkingSpot::STATUS_MAINTENANCE],
            'total_spots' => $totalSpots,
            'visible_spots' => $visibleSpots,
            'visible_occupancy_percent' => $visibleSpots > 0
                ? (int) round(($visibleOccupied / $visibleSpots) * 100)
                : 0,
        ];

        $statusOptions = [
            '' => 'Todos os status',
            ParkingSpot::STATUS_AVAILABLE => 'Livre',
            ParkingSpot::STATUS_RESERVED => 'Reservada',
            ParkingSpot::STATUS_OCCUPIED => 'Ocupada',
            ParkingSpot::STATUS_BLOCKED => 'Bloqueada',
            ParkingSpot::STATUS_MAINTENANCE => 'Manutencao',
        ];

        $vehicleTypeOptions = [
            '' => 'Todos os tipos',
            'carro' => 'Carro',
            'moto' => 'Moto',
            'caminhonete' => 'Caminhonete',
            'geral' => 'Geral',
        ];

        $allSectors = ParkingSector::query()->orderBy('name')->get(['id', 'name', 'code']);

        return view(
            'operations.map',
            compact('sectors', 'summary', 'filters', 'statusOptions', 'vehicleTypeOptions', 'allSectors')
        );
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

        return redirect()->back()->with('create', 'Setor criado com sucesso.');
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
            return redirect()->back()->with('error', 'Ja existe uma vaga com este codigo no setor.');
        }

        ParkingSpot::query()->create([
            'parking_sector_id' => (int) $payload['parking_sector_id'],
            'code' => strtoupper((string) $payload['code']),
            'vehicle_type' => $payload['vehicle_type'],
            'status' => ParkingSpot::STATUS_AVAILABLE,
        ]);

        return redirect()->back()->with('create', 'Vaga criada com sucesso.');
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

        return redirect()->back()->with('create', 'Status da vaga atualizado.');
    }
}
