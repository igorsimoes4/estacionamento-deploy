<?php

namespace App\Http\Controllers;

use App\Models\Cars;
use App\Models\ParkingReservation;
use App\Models\ParkingSector;
use App\Models\PaymentTransaction;
use App\Models\PriceCar;
use App\Models\PriceMotorcycle;
use App\Models\PriceTruck;
use App\Services\Parking\DynamicPricingService;
use App\Services\Parking\ParkingSpotAllocatorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ParkingReservationController extends Controller
{
    public function index(Request $request): View
    {
        $filters = [
            'status' => trim((string) $request->query('status', 'active')),
            'q' => trim((string) $request->query('q', '')),
            'vehicle_type' => trim((string) $request->query('vehicle_type', '')),
            'payment_status' => trim((string) $request->query('payment_status', '')),
            'sector_id' => (int) $request->query('sector_id', 0),
            'date_from' => trim((string) $request->query('date_from', '')),
            'date_to' => trim((string) $request->query('date_to', '')),
            'per_page' => (int) $request->query('per_page', 20),
        ];

        $allowedStatus = [
            'all',
            'active',
            ParkingReservation::STATUS_PENDING,
            ParkingReservation::STATUS_CONFIRMED,
            ParkingReservation::STATUS_CHECKED_IN,
            ParkingReservation::STATUS_COMPLETED,
            ParkingReservation::STATUS_CANCELLED,
            ParkingReservation::STATUS_NO_SHOW,
            'upcoming',
        ];

        if (!in_array($filters['status'], $allowedStatus, true)) {
            $filters['status'] = 'active';
        }

        if (!in_array($filters['vehicle_type'], ['', 'carro', 'moto', 'caminhonete'], true)) {
            $filters['vehicle_type'] = '';
        }

        if (!in_array($filters['payment_status'], ['', 'pending', 'paid', 'failed', 'cancelled', 'refunded'], true)) {
            $filters['payment_status'] = '';
        }

        if (!in_array($filters['per_page'], [10, 20, 30, 50], true)) {
            $filters['per_page'] = 20;
        }

        $query = ParkingReservation::query()
            ->with(['sector:id,name,code', 'spot:id,code']);

        if ($filters['status'] === 'active') {
            $query->active();
        } elseif ($filters['status'] === 'upcoming') {
            $query->upcoming();
        } elseif ($filters['status'] !== 'all') {
            $query->where('status', $filters['status']);
        }

        if ($filters['q'] !== '') {
            $term = '%' . $filters['q'] . '%';
            $query->where(function ($inner) use ($term): void {
                $inner->where('reference', 'like', $term)
                    ->orWhere('customer_name', 'like', $term)
                    ->orWhere('customer_email', 'like', $term)
                    ->orWhere('customer_phone', 'like', $term)
                    ->orWhere('vehicle_plate', 'like', $term);
            });
        }

        if ($filters['vehicle_type'] !== '') {
            $query->where('vehicle_type', $filters['vehicle_type']);
        }

        if ($filters['payment_status'] !== '') {
            $query->where('payment_status', $filters['payment_status']);
        }

        if ($filters['sector_id'] > 0) {
            $query->where('parking_sector_id', $filters['sector_id']);
        }

        if ($filters['date_from'] !== '') {
            $query->whereDate('starts_at', '>=', $filters['date_from']);
        }

        if ($filters['date_to'] !== '') {
            $query->whereDate('starts_at', '<=', $filters['date_to']);
        }

        $filteredTotal = (clone $query)->count();

        $reservations = $query
            ->orderBy('starts_at')
            ->paginate($filters['per_page'])
            ->withQueryString();

        $stats = [
            'total' => ParkingReservation::query()->count(),
            'active' => ParkingReservation::query()->active()->count(),
            'upcoming' => ParkingReservation::query()->upcoming()->count(),
            'checked_in' => ParkingReservation::query()->where('status', ParkingReservation::STATUS_CHECKED_IN)->count(),
            'cancelled' => ParkingReservation::query()->where('status', ParkingReservation::STATUS_CANCELLED)->count(),
            'estimated_active_cents' => (int) ParkingReservation::query()
                ->whereIn('status', [
                    ParkingReservation::STATUS_PENDING,
                    ParkingReservation::STATUS_CONFIRMED,
                    ParkingReservation::STATUS_CHECKED_IN,
                ])
                ->sum('estimated_amount_cents'),
            'filtered_total' => $filteredTotal,
        ];

        $statusOptions = [
            'active' => 'Ativas',
            'upcoming' => 'Proximas',
            'all' => 'Todas',
            ParkingReservation::STATUS_PENDING => 'Pendente',
            ParkingReservation::STATUS_CONFIRMED => 'Confirmada',
            ParkingReservation::STATUS_CHECKED_IN => 'Check-in',
            ParkingReservation::STATUS_COMPLETED => 'Concluida',
            ParkingReservation::STATUS_CANCELLED => 'Cancelada',
            ParkingReservation::STATUS_NO_SHOW => 'No-show',
        ];

        $sectors = ParkingSector::query()->orderBy('name')->get(['id', 'name', 'code']);

        return view('reservations.index', compact('reservations', 'filters', 'stats', 'statusOptions', 'sectors'));
    }

    public function store(Request $request, DynamicPricingService $dynamicPricingService, ParkingSpotAllocatorService $allocator): RedirectResponse
    {
        $payload = $request->validate([
            'customer_name' => ['required', 'string', 'max:120'],
            'customer_email' => ['nullable', 'email', 'max:255'],
            'customer_phone' => ['nullable', 'string', 'max:30'],
            'vehicle_plate' => ['required', 'string', 'max:12'],
            'vehicle_model' => ['nullable', 'string', 'max:120'],
            'vehicle_type' => ['required', 'in:carro,moto,caminhonete'],
            'parking_sector_id' => ['nullable', 'integer', 'exists:parking_sectors,id'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
            'prepaid' => ['nullable', 'boolean'],
            'payment_method' => ['nullable', 'in:pix,cartao_credito,cartao_debito,boleto,dinheiro'],
            'notes' => ['nullable', 'string'],
        ]);

        $base = $this->estimateBasePrice($payload['vehicle_type']);
        $estimated = $dynamicPricingService->apply($base, $payload['vehicle_type'], now());
        $estimatedCents = (int) round($estimated * 100);

        $reservation = ParkingReservation::query()->create([
            'reference' => 'RES-' . now()->format('YmdHis') . '-' . random_int(100, 999),
            'customer_name' => $payload['customer_name'],
            'customer_email' => $payload['customer_email'] ?? null,
            'customer_phone' => $payload['customer_phone'] ?? null,
            'vehicle_plate' => strtoupper((string) $payload['vehicle_plate']),
            'vehicle_model' => $payload['vehicle_model'] ?? null,
            'vehicle_type' => $payload['vehicle_type'],
            'parking_sector_id' => isset($payload['parking_sector_id']) ? (int) $payload['parking_sector_id'] : null,
            'starts_at' => $payload['starts_at'],
            'ends_at' => $payload['ends_at'],
            'status' => ParkingReservation::STATUS_PENDING,
            'estimated_amount_cents' => $estimatedCents,
            'prepaid_amount_cents' => 0,
            'payment_status' => 'pending',
            'payment_provider' => 'manual',
            'notes' => $payload['notes'] ?? null,
        ]);

        $allocator->reserveSpotForReservation($reservation);

        if ($request->boolean('prepaid')) {
            PaymentTransaction::query()->create([
                'reference' => $reservation->reference . '-PRE',
                'provider' => 'manual',
                'method' => $payload['payment_method'] ?? 'pix',
                'status' => 'pending',
                'type' => 'reservation',
                'amount_cents' => $estimatedCents,
                'currency' => 'BRL',
                'parking_reservation_id' => $reservation->id,
                'due_date' => now()->toDateString(),
            ]);
        }

        return redirect()->route('reservations.index')->with('create', 'Reserva criada com sucesso.');
    }

    public function checkIn(ParkingReservation $reservation, ParkingSpotAllocatorService $allocator): RedirectResponse
    {
        if (!in_array($reservation->status, [ParkingReservation::STATUS_PENDING, ParkingReservation::STATUS_CONFIRMED], true)) {
            return back()->with('error', 'Reserva nao esta apta para check-in.');
        }

        $existingCar = Cars::parked()->where('placa', strtoupper((string) $reservation->vehicle_plate))->first();

        if ($existingCar) {
            return back()->with('error', 'Ja existe veiculo ativo com essa placa.');
        }

        $car = Cars::query()->create([
            'modelo' => $reservation->vehicle_model ?: 'Reserva',
            'placa' => strtoupper((string) $reservation->vehicle_plate),
            'entrada' => now()->format('H:i:s'),
            'tipo_car' => $reservation->vehicle_type,
            'preco' => 0,
            'parking_reservation_id' => $reservation->id,
            'entry_source' => 'reserva',
        ]);

        $allocator->occupySpotForCar($car, $reservation->parking_sector_id, $reservation->parking_spot_id);

        $reservation->status = ParkingReservation::STATUS_CHECKED_IN;
        $reservation->checked_in_at = now();
        $reservation->save();

        return back()->with('create', 'Check-in da reserva realizado.');
    }

    public function cancel(ParkingReservation $reservation): RedirectResponse
    {
        if (in_array($reservation->status, [ParkingReservation::STATUS_COMPLETED, ParkingReservation::STATUS_CANCELLED], true)) {
            return back()->with('error', 'Reserva nao pode ser cancelada.');
        }

        $reservation->status = ParkingReservation::STATUS_CANCELLED;
        $reservation->save();

        return back()->with('create', 'Reserva cancelada com sucesso.');
    }

    private function estimateBasePrice(string $vehicleType): float
    {
        return match ($vehicleType) {
            'moto' => (float) (PriceMotorcycle::query()->first()->valorMinimo ?? 5),
            'caminhonete' => (float) (PriceTruck::query()->first()->valorMinimo ?? 15),
            default => (float) (PriceCar::query()->first()->valorMinimo ?? 10),
        };
    }
}
