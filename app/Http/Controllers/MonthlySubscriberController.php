<?php

namespace App\Http\Controllers;

use App\Models\MonthlySubscriber;
use App\Models\PriceCar;
use App\Models\PriceMotorcycle;
use App\Models\PriceTruck;
use App\Services\Parking\RecurringBillingService;
use App\Services\Payments\MonthlySubscriberBoletoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class MonthlySubscriberController extends Controller
{
    public function index(Request $request)
    {
        $status = trim((string) $request->query('status', 'todos'));
        $search = trim((string) $request->query('q', ''));
        $vehicleType = trim((string) $request->query('vehicle_type', ''));
        $perPage = (int) $request->query('per_page', 15);
        $perPage = in_array($perPage, [10, 15, 20, 50], true) ? $perPage : 15;

        $query = MonthlySubscriber::query();

        if ($search !== '') {
            $term = '%' . $search . '%';
            $query->where(function ($inner) use ($term): void {
                $inner->where('name', 'like', $term)
                    ->orWhere('email', 'like', $term)
                    ->orWhere('cpf', 'like', $term)
                    ->orWhere('vehicle_plate', 'like', $term);
            });
        }

        if (in_array($vehicleType, ['carro', 'moto', 'caminhonete'], true)) {
            $query->where('vehicle_type', $vehicleType);
        }

        if ($status === 'ativos') {
            $query->active();
        } elseif ($status === 'vencendo') {
            $query->expiringSoon(7);
        } elseif ($status === 'inadimplentes') {
            $query->whereNotNull('delinquent_since');
        } elseif ($status === 'inativos') {
            $query->where('is_active', false);
        } else {
            $status = 'todos';
        }

        $subscribers = $query
            ->orderBy('end_date')
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();

        $stats = [
            'total' => MonthlySubscriber::query()->count(),
            'active' => MonthlySubscriber::query()->where('is_active', true)->count(),
            'expiring' => MonthlySubscriber::query()->expiringSoon(7)->count(),
            'overdue' => MonthlySubscriber::query()->whereNotNull('delinquent_since')->count(),
            'mrr' => (float) MonthlySubscriber::query()
                ->where('is_active', true)
                ->sum('monthly_fee'),
        ];

        $filters = [
            'status' => $status,
            'q' => $search,
            'vehicle_type' => $vehicleType,
            'per_page' => $perPage,
        ];

        return view('monthly_subscribers.index', compact('subscribers', 'filters', 'stats'));
    }

    public function create()
    {
        return view('monthly_subscribers.create');
    }

    public function store(Request $request)
    {
        $payload = $this->normalizePayload($request);
        $rules = MonthlySubscriber::rules();
        $rules['access_enabled'] = ['nullable', 'boolean'];
        $rules['access_password'] = ['required', 'string', 'min:6', 'confirmed'];
        $rules['auto_renew_enabled'] = ['nullable', 'boolean'];
        $rules['recurring_payment_method'] = ['nullable', 'in:boleto,pix,cartao_credito,cartao_debito'];

        $validated = validator($payload, $rules)->validate();
        $subscriber = null;

        try {
            DB::beginTransaction();

            $subscriber = MonthlySubscriber::create($validated);

            DB::commit();
        } catch (\Throwable $e) {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }

            return back()->withInput()->with('error', 'Erro ao cadastrar mensalista.');
        }

        $boletoWarning = null;

        if ($subscriber !== null) {
            try {
                $boleto = app(MonthlySubscriberBoletoService::class)->generateForSubscriber($subscriber, true);
                $warningText = trim((string) ($boleto['warning'] ?? ''));
                if ($warningText !== '') {
                    $boletoWarning = 'Mensalista cadastrado. Boleto em modo manual: ' . $warningText;
                }

                app(RecurringBillingService::class)->run(now()->format('Y-m'));
            } catch (\Throwable $e) {
                $boletoWarning = 'Mensalista cadastrado, mas houve falha ao gerar boleto automaticamente.';
            }
        }

        $redirect = redirect()->route('monthly-subscribers.index')
            ->with('success', 'Mensalista cadastrado com sucesso.');

        if ($boletoWarning !== null) {
            $redirect->with('warning', $boletoWarning);
        }

        return $redirect;
    }

    public function show(MonthlySubscriber $monthlySubscriber)
    {
        return view('monthly_subscribers.show', compact('monthlySubscriber'));
    }

    public function edit(MonthlySubscriber $monthlySubscriber)
    {
        return view('monthly_subscribers.edit', compact('monthlySubscriber'));
    }

    public function update(Request $request, MonthlySubscriber $monthlySubscriber)
    {
        $payload = $this->normalizePayload($request);

        $rules = MonthlySubscriber::rules();
        $rules['cpf'] = ['required', 'string', Rule::unique('monthly_subscribers', 'cpf')->ignore($monthlySubscriber->id)];
        $rules['vehicle_plate'] = ['required', 'string', Rule::unique('monthly_subscribers', 'vehicle_plate')->ignore($monthlySubscriber->id)];
        $rules['access_enabled'] = ['nullable', 'boolean'];
        $rules['access_password'] = ['nullable', 'string', 'min:6', 'confirmed'];
        $rules['auto_renew_enabled'] = ['nullable', 'boolean'];
        $rules['recurring_payment_method'] = ['nullable', 'in:boleto,pix,cartao_credito,cartao_debito'];

        $validated = validator($payload, $rules)->validate();

        if (empty($validated['access_password'])) {
            unset($validated['access_password']);
        }

        try {
            DB::beginTransaction();

            $monthlySubscriber->update($validated);

            DB::commit();

            return redirect()->route('monthly-subscribers.index')
                ->with('success', 'Mensalista atualizado com sucesso.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->withInput()->with('error', 'Erro ao atualizar mensalista.');
        }
    }

    public function destroy(MonthlySubscriber $monthlySubscriber)
    {
        try {
            DB::beginTransaction();

            $monthlySubscriber->delete();

            DB::commit();

            return redirect()->route('monthly-subscribers.index')
                ->with('success', 'Mensalista removido com sucesso.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('error', 'Erro ao remover mensalista.');
        }
    }

    public function getVehiclePrice($type)
    {
        $type = strtolower((string) $type);

        if ($type === 'caminhao') {
            $type = 'caminhonete';
        }

        $price = 0.0;

        switch ($type) {
            case 'carro':
                $price = (float) optional(PriceCar::query()->first())->taxaMensal;
                break;
            case 'moto':
                $price = (float) optional(PriceMotorcycle::query()->first())->taxaMensal;
                break;
            case 'caminhonete':
                $price = (float) optional(PriceTruck::query()->first())->taxaMensal;
                break;
        }

        return response()->json(['price' => $price]);
    }

    private function normalizePayload(Request $request): array
    {
        $payload = $request->all();

        if (isset($payload['monthly_fee'])) {
            $payload['monthly_fee'] = $this->normalizeMoneyValue($payload['monthly_fee']);
        }

        if (!empty($payload['vehicle_plate'])) {
            $plate = strtoupper(trim((string) $payload['vehicle_plate']));
            $plate = preg_replace('/\s+/', '', $plate);

            if (preg_match('/^[A-Z]{3}\d[A-Z]\d{2}$/', $plate) === 1) {
                $plate = substr($plate, 0, 3) . '-' . substr($plate, 3);
            }

            $payload['vehicle_plate'] = $plate;
        }

        if (($payload['vehicle_type'] ?? null) === 'caminhao') {
            $payload['vehicle_type'] = 'caminhonete';
        }

        $payload['access_enabled'] = $request->boolean('access_enabled', true);
        $payload['auto_renew_enabled'] = $request->boolean('auto_renew_enabled', true);

        return $payload;
    }

    private function normalizeMoneyValue($rawValue): string
    {
        $value = trim((string) $rawValue);
        $value = preg_replace('/[^\d,.\-]/', '', $value) ?? '0';

        if ($value === '' || $value === '-' || $value === '-.' || $value === '-,') {
            return '0';
        }

        $lastComma = strrpos($value, ',');
        $lastDot = strrpos($value, '.');
        $decimalSeparator = null;

        if ($lastComma !== false && $lastDot !== false) {
            $decimalSeparator = $lastComma > $lastDot ? ',' : '.';
        } elseif ($lastComma !== false) {
            $fraction = substr($value, $lastComma + 1);
            $decimalSeparator = strlen($fraction) <= 2 ? ',' : null;
        } elseif ($lastDot !== false) {
            $fraction = substr($value, $lastDot + 1);
            $decimalSeparator = strlen($fraction) <= 2 ? '.' : null;
        }

        if ($decimalSeparator === ',') {
            $value = str_replace('.', '', $value);
            $value = str_replace(',', '.', $value);
        } elseif ($decimalSeparator === '.') {
            $value = str_replace(',', '', $value);
        } else {
            $value = str_replace([',', '.'], '', $value);
        }

        return is_numeric($value) ? (string) $value : '0';
    }
}
