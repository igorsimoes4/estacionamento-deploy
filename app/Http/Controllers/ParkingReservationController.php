<?php

namespace App\Http\Controllers;

use App\Models\Cars;
use App\Models\ParkingReservation;
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
        $status = (string) $request->query('status', 'active');

        $query = ParkingReservation::query()->with(['sector', 'spot'])->orderByDesc('starts_at');

        if ($status === 'active') {
            $query->active();
        } elseif ($status !== 'all') {
            $query->where('status', $status);
        }

        $reservations = $query->paginate(20)->withQueryString();

        return view('reservations.index', compact('reservations', 'status'));
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
