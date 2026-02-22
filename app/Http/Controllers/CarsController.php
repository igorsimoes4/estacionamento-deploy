<?php

namespace App\Http\Controllers;

use App\Models\Cars;
use App\Models\ParkingSector;
use App\Models\PaymentTransaction;
use App\Models\PriceCar;
use App\Models\PriceMotorcycle;
use App\Models\PriceTruck;
use App\Services\Parking\DynamicPricingService;
use App\Services\Parking\ParkingSpotAllocatorService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CarsController extends Controller
{
    private const VEHICLE_TYPES = ['carro', 'moto', 'caminhonete'];

    private const DEFAULT_PRICES = [
        'carro' => [
            'valorHora' => 5,
            'valorMinimo' => 10,
            'valorDiaria' => 50,
            'taxaAdicional' => 17,
            'taxaMensal' => 400,
        ],
        'moto' => [
            'valorHora' => 1,
            'valorMinimo' => 5,
            'valorDiaria' => 14,
            'taxaAdicional' => 8,
            'taxaMensal' => 100,
        ],
        'caminhonete' => [
            'valorHora' => 5,
            'valorMinimo' => 15,
            'valorDiaria' => 60,
            'taxaAdicional' => 20,
            'taxaMensal' => 600,
        ],
    ];

    public function index(Request $request)
    {
        $filters = [
            'search' => trim((string) $request->query('search', '')),
            'tipo' => $request->query('tipo', ''),
            'status' => $request->query('status', 'ativo'),
        ];

        $query = Cars::query();

        if ($filters['search'] !== '') {
            $query->where(function ($q) use ($filters) {
                $term = '%' . $filters['search'] . '%';
                $q->where('placa', 'LIKE', $term)
                    ->orWhere('modelo', 'LIKE', $term);
            });
        }

        if (in_array($filters['tipo'], self::VEHICLE_TYPES, true)) {
            $query->where('tipo_car', $filters['tipo']);
        }

        if ($filters['status'] === 'finalizado') {
            $query->finished();
        } elseif ($filters['status'] !== 'todos') {
            $query->parked();
            $filters['status'] = 'ativo';
        }

        $cars = $query
            ->orderByDesc('created_at')
            ->get();

        $cars->transform(function (Cars $car) {
            $car->price = $this->priceForCar($car);
            $car->duration_human = $this->formatDuration($car->created_at, $car->saida);
            return $car;
        });

        if ($request->expectsJson()) {
            return response()->json([
                'filters' => $filters,
                'data' => $cars,
            ]);
        }

        $summary = [
            'active' => Cars::parked()->count(),
            'finished_today' => Cars::finished()->whereDate('saida', Carbon::today())->count(),
            'revenue_today' => (float) Cars::finished()->whereDate('saida', Carbon::today())->sum('preco'),
        ];

        return view('cars', [
            'cars' => $cars,
            'filters' => $filters,
            'summary' => $summary,
        ]);
    }

    public function search(Request $request)
    {
        $search = trim((string) $request->input('search', ''));

        if ($search === '') {
            return redirect()->route('cars.index');
        }

        return redirect()->route('cars.index', ['search' => $search]);
    }

    public function create()
    {
        $sectors = ParkingSector::query()->where('is_active', true)->orderBy('name')->get();
        return view('cars_add', compact('sectors'));
    }

    public function store(Request $request)
    {
        $data = $request->only([
            'modelo',
            'placa',
            'entrada',
            'tipo_car',
            'parking_sector_id',
        ]);

        $validator = Validator::make($data, [
            'modelo' => ['required', 'string', 'max:64'],
            'placa' => ['required', 'string', 'max:8'],
            'entrada' => ['nullable', 'date'],
            'tipo_car' => ['required', Rule::in(self::VEHICLE_TYPES)],
            'parking_sector_id' => ['nullable', 'integer', 'exists:parking_sectors,id'],
        ]);

        if ($validator->fails()) {
            return redirect()->route('cars.create')->withErrors($validator)->withInput();
        }

        $normalizedPlate = $this->normalizePlate($data['placa']);

        if ($normalizedPlate === null) {
            return redirect()->route('cars.create')
                ->withErrors(['placa' => 'A placa deve estar no formato AAA-1234 ou AAA1A23.'])
                ->withInput();
        }

        if (Cars::parked()->where('placa', $normalizedPlate)->exists()) {
            return redirect()->route('cars.create')
                ->withErrors(['placa' => 'Ja existe um veiculo ativo com esta placa.'])
                ->withInput();
        }

        $car = new Cars();
        $car->modelo = $data['modelo'];
        $car->placa = $normalizedPlate;
        $car->entrada = Carbon::parse($data['entrada'] ?? now())->format('H:i:s');
        $car->tipo_car = $data['tipo_car'];
        $car->parking_sector_id = isset($data['parking_sector_id']) ? (int) $data['parking_sector_id'] : null;
        $car->entry_source = 'manual';
        $car->preco = 0;

        if (!empty($data['entrada'])) {
            $car->created_at = Carbon::parse($data['entrada']);
        }

        $car->save();

        app(ParkingSpotAllocatorService::class)->occupySpotForCar($car, $car->parking_sector_id);

        Log::info('Veiculo adicionado', ['placa' => $car->placa, 'id' => $car->id]);

        return redirect()->route('cars.index')->with('create', 'Veiculo adicionado com sucesso.');
    }

    public function show($id)
    {
        $car = Cars::findOrFail($id);
        $car->price = $this->priceForCar($car);
        $car->duration_human = $this->formatDuration($car->created_at, $car->saida);

        if (request()->expectsJson()) {
            return response()->json($car);
        }

        return redirect()->route('cars.edit', $car->id);
    }

    public function showModal($id)
    {
        $car = Cars::findOrFail($id);

        $entrada = Carbon::parse($car->created_at);
        $saida = $car->saida ? Carbon::parse($car->saida) : now();
        $tempo = $entrada->diff($saida);

        return response()->json([
            'id' => $car->id,
            'modelo' => $car->modelo,
            'placa' => $car->placa,
            'tipo_car' => $car->tipo_car,
            'payment_method' => $car->payment_method,
            'payment_method_label' => Cars::paymentMethodLabel($car->payment_method),
            'price' => number_format($this->priceForCar($car), 2, ',', '.'),
            'entrada' => $entrada->format('d/m/Y H:i:s'),
            'horaT' => $tempo->h,
            'minutoT' => $tempo->i,
            'diaT' => $tempo->d,
            'mesT' => $tempo->m,
        ]);
    }

    public function edit($id)
    {
        $car = Cars::findOrFail($id);

        $car->price = $this->priceForCar($car);
        $sectors = ParkingSector::query()->where('is_active', true)->orderBy('name')->get();

        return view('cars_edit', ['car' => $car, 'sectors' => $sectors]);
    }

    public function update(Request $request, $id)
    {
        $car = Cars::findOrFail($id);

        $data = $request->only([
            'modelo',
            'placa',
            'entrada',
            'tipo_car',
            'parking_sector_id',
        ]);

        $validator = Validator::make($data, [
            'modelo' => ['required', 'string', 'max:64'],
            'placa' => ['required', 'string', 'max:8'],
            'entrada' => ['nullable', 'date'],
            'tipo_car' => ['required', Rule::in(self::VEHICLE_TYPES)],
            'parking_sector_id' => ['nullable', 'integer', 'exists:parking_sectors,id'],
        ]);

        if ($validator->fails()) {
            return redirect()->route('cars.edit', $car->id)->withErrors($validator)->withInput();
        }

        $normalizedPlate = $this->normalizePlate($data['placa']);

        if ($normalizedPlate === null) {
            return redirect()->route('cars.edit', $car->id)
                ->withErrors(['placa' => 'A placa deve estar no formato AAA-1234 ou AAA1A23.'])
                ->withInput();
        }

        $duplicatedActivePlate = Cars::parked()
            ->where('placa', $normalizedPlate)
            ->where('id', '!=', $car->id)
            ->exists();

        if ($duplicatedActivePlate) {
            return redirect()->route('cars.edit', $car->id)
                ->withErrors(['placa' => 'Ja existe um veiculo ativo com esta placa.'])
                ->withInput();
        }

        $car->modelo = $data['modelo'];
        $car->placa = $normalizedPlate;
        $car->tipo_car = $data['tipo_car'];
        $car->parking_sector_id = isset($data['parking_sector_id']) ? (int) $data['parking_sector_id'] : $car->parking_sector_id;

        if (!empty($data['entrada'])) {
            $entryDate = Carbon::parse($data['entrada']);
            $car->created_at = $entryDate;
            $car->entrada = $entryDate->format('H:i:s');
        }

        $car->save();

        return redirect()->route('cars.index')->with('create', 'Veiculo atualizado com sucesso.');
    }

    public function destroy(Request $request, $id)
    {
        $car = Cars::findOrFail($id);

        if ($car->status === 'finalizado') {
            return redirect()->route('cars.index')->with('delete_car', 'Este veiculo ja foi finalizado.');
        }

        $paymentData = validator($request->all(), [
            'payment_method' => ['nullable', Rule::in(Cars::PAYMENT_METHODS)],
            'payment_reference' => ['nullable', 'string', 'max:120'],
        ])->validate();

        $price = $this->priceForCar($car);
        $exitAt = now();

        $car->preco = $price;
        $car->status = 'finalizado';
        $car->saida = $exitAt;
        $car->payment_method = $paymentData['payment_method'] ?? 'dinheiro';
        $car->payment_provider = 'manual';
        $car->payment_status = 'paid';
        $car->external_payment_id = null;
        $car->payment_url = null;
        $car->payment_reference = $paymentData['payment_reference'] ?? null;
        $car->paid_at = $exitAt;
        $car->save();

        app(ParkingSpotAllocatorService::class)->releaseSpotByCar($car);

        PaymentTransaction::query()->create([
            'reference' => 'CAR-' . $car->id . '-' . now()->format('YmdHis'),
            'provider' => $car->payment_provider ?: 'manual',
            'method' => $car->payment_method ?: 'dinheiro',
            'status' => 'paid',
            'type' => 'one_time',
            'amount_cents' => (int) round(((float) $car->preco) * 100),
            'currency' => 'BRL',
            'car_id' => $car->id,
            'external_id' => $car->external_payment_id,
            'payment_url' => $car->payment_url,
            'paid_at' => $exitAt,
            'reconciled_at' => $exitAt,
        ]);

        Log::info('Veiculo finalizado', ['id' => $car->id, 'placa' => $car->placa, 'valor' => $price]);

        return redirect()->route('cars.index')->with('create', 'Veiculo finalizado com sucesso.');
    }

    public function price($id)
    {
        $car = Cars::findOrFail($id);
        return $this->priceForCar($car);
    }

    private function priceForCar(Cars $car): float
    {
        if ($car->status === 'finalizado' && $car->preco !== null) {
            return (float) $car->preco;
        }

        $prices = $this->resolvePriceByVehicleType($car->tipo_car);

        $entryAt = Carbon::parse($car->created_at);
        $exitAt = $car->saida ? Carbon::parse($car->saida) : now();

        return $this->calculatePrice($entryAt, $exitAt, $prices, $car->id, (string) $car->tipo_car);
    }

    private function calculatePrice(Carbon $entryAt, Carbon $exitAt, Collection $prices, int $carId, string $vehicleType): float
    {
        $minutes = max(1, $entryAt->diffInMinutes($exitAt));
        $totalDays = intdiv($minutes, 1440);
        $remainingMinutes = $minutes % 1440;

        $months = intdiv($totalDays, 30);
        $days = $totalDays % 30;

        $amount = 0.0;

        if ($months > 0) {
            $amount += $months * (float) $prices->get('taxaMensal');
        }

        if ($days > 0) {
            $amount += $days * (float) $prices->get('valorDiaria');
        }

        if ($remainingMinutes > 0) {
            if ($remainingMinutes <= 30 && $totalDays === 0 && $months === 0) {
                $amount += (float) $prices->get('valorMinimo');
            } else {
                $hourBlocks = (int) ceil($remainingMinutes / 60);
                $amount += $hourBlocks * (float) $prices->get('valorHora');

                if ($hourBlocks > 1) {
                    $amount += (float) $prices->get('taxaAdicional');
                }
            }
        }

        $amount = round($amount, 2);
        $amount = app(DynamicPricingService::class)->apply($amount, $vehicleType, $entryAt, $exitAt);

        Log::info('Calculo de preco executado', [
            'car_id' => $carId,
            'minutes' => $minutes,
            'months' => $months,
            'days' => $days,
            'amount' => $amount,
        ]);

        return $amount;
    }

    private function resolvePriceByVehicleType(string $vehicleType): Collection
    {
        switch ($vehicleType) {
            case 'carro':
                $model = PriceCar::query()->firstOrCreate([], self::DEFAULT_PRICES['carro']);
                break;
            case 'moto':
                $model = PriceMotorcycle::query()->firstOrCreate([], self::DEFAULT_PRICES['moto']);
                break;
            case 'caminhonete':
                $model = PriceTruck::query()->firstOrCreate([], self::DEFAULT_PRICES['caminhonete']);
                break;
            default:
                Log::warning('Tipo de veiculo desconhecido para precificacao', ['tipo_car' => $vehicleType]);
                return collect(self::DEFAULT_PRICES['carro']);
        }

        return collect([
            'valorHora' => (float) $model->valorHora,
            'valorMinimo' => (float) $model->valorMinimo,
            'valorDiaria' => (float) $model->valorDiaria,
            'taxaAdicional' => (float) $model->taxaAdicional,
            'taxaMensal' => (float) $model->taxaMensal,
        ]);
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

    private function formatDuration(Carbon $entryAt, ?Carbon $exitAt): string
    {
        $diff = $entryAt->diff($exitAt ?? now());

        $parts = [];

        if ($diff->m > 0) {
            $parts[] = $diff->m . ' mes(es)';
        }

        if ($diff->d > 0) {
            $parts[] = $diff->d . ' dia(s)';
        }

        if ($diff->h > 0) {
            $parts[] = $diff->h . ' hora(s)';
        }

        if ($diff->i > 0) {
            $parts[] = $diff->i . ' minuto(s)';
        }

        return empty($parts) ? '1 minuto' : implode(' ', $parts);
    }
}
