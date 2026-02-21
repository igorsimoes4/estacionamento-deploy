<?php

namespace App\Http\Controllers;

use App\Models\AccountingEntry;
use App\Models\Cars;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AccountingController extends Controller
{
    public function index(Request $request)
    {
        $period = (string) $request->query('period', now()->format('Y-m'));
        $type = (string) $request->query('type', 'all');

        if (preg_match('/^\d{4}-\d{2}$/', $period) !== 1) {
            $period = now()->format('Y-m');
        }

        if (!in_array($type, ['all', 'receita', 'despesa'], true)) {
            $type = 'all';
        }

        try {
            $start = Carbon::createFromFormat('Y-m', $period)->startOfMonth();
            if ($start->format('Y-m') !== $period) {
                $start = now()->startOfMonth();
                $period = $start->format('Y-m');
            }
        } catch (\Throwable $e) {
            $start = now()->startOfMonth();
            $period = $start->format('Y-m');
        }

        $end = $start->copy()->endOfMonth();

        $entries = AccountingEntry::query()
            ->whereBetween('occurred_at', [$start->toDateString(), $end->toDateString()])
            ->when($type !== 'all', fn ($query) => $query->where('type', $type))
            ->orderByDesc('occurred_at')
            ->orderByDesc('id')
            ->paginate(20)
            ->appends($request->query());

        $operationalRevenue = (float) Cars::finished()
            ->whereBetween('saida', [$start->copy()->startOfDay(), $end->copy()->endOfDay()])
            ->sum('preco');

        $manualRevenue = (float) AccountingEntry::query()
            ->revenue()
            ->whereBetween('occurred_at', [$start->toDateString(), $end->toDateString()])
            ->sum('amount');

        $manualExpense = (float) AccountingEntry::query()
            ->expense()
            ->whereBetween('occurred_at', [$start->toDateString(), $end->toDateString()])
            ->sum('amount');

        $totalRevenue = $operationalRevenue + $manualRevenue;
        $netResult = $totalRevenue - $manualExpense;

        $allTimeBalance = (float) Cars::finished()->sum('preco')
            + (float) AccountingEntry::query()->revenue()->sum('amount')
            - (float) AccountingEntry::query()->expense()->sum('amount');

        $operationalByMethod = Cars::finished()
            ->whereBetween('saida', [$start->copy()->startOfDay(), $end->copy()->endOfDay()])
            ->selectRaw("COALESCE(NULLIF(payment_method, ''), 'nao_informado') as payment_method_key")
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('SUM(preco) as amount')
            ->groupBy('payment_method_key')
            ->orderByDesc('amount')
            ->get()
            ->map(function ($row) {
                return (object) [
                    'method' => Cars::paymentMethodLabel($row->payment_method_key === 'nao_informado' ? null : $row->payment_method_key),
                    'total' => (int) $row->total,
                    'amount' => (float) $row->amount,
                ];
            });

        return view('accounting.index', [
            'entries' => $entries,
            'period' => $period,
            'type' => $type,
            'operationalByMethod' => $operationalByMethod,
            'stats' => [
                'operational_revenue' => $operationalRevenue,
                'manual_revenue' => $manualRevenue,
                'manual_expense' => $manualExpense,
                'total_revenue' => $totalRevenue,
                'net_result' => $netResult,
                'all_time_balance' => $allTimeBalance,
            ],
        ]);
    }

    public function create()
    {
        return view('accounting.create');
    }

    public function store(Request $request)
    {
        $validated = $this->validatePayload($request);
        AccountingEntry::query()->create($validated);

        return redirect()
            ->route('accounting.index')
            ->with('success', 'Lancamento contabil criado com sucesso.');
    }

    public function edit(AccountingEntry $accounting)
    {
        return view('accounting.edit', ['entry' => $accounting]);
    }

    public function update(Request $request, AccountingEntry $accounting)
    {
        $validated = $this->validatePayload($request);
        $accounting->update($validated);

        return redirect()
            ->route('accounting.index')
            ->with('success', 'Lancamento contabil atualizado com sucesso.');
    }

    public function destroy(AccountingEntry $accounting)
    {
        $accounting->delete();

        return redirect()
            ->route('accounting.index')
            ->with('success', 'Lancamento contabil removido com sucesso.');
    }

    private function validatePayload(Request $request): array
    {
        $payload = $request->all();
        $payload['amount'] = $this->normalizeAmount($payload['amount'] ?? null);
        $payload['payment_method'] = $this->normalizePaymentMethod($payload['payment_method'] ?? null);

        return validator($payload, [
            'type' => ['required', 'in:receita,despesa'],
            'category' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'occurred_at' => ['required', 'date'],
            'payment_method' => ['nullable', 'in:dinheiro,pix,cartao_credito,cartao_debito,transferencia,boleto,outro'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ], [
            'type.in' => 'O tipo deve ser receita ou despesa.',
            'amount.numeric' => 'Informe um valor valido.',
            'amount.min' => 'O valor minimo para lancamento e 0,01.',
        ])->validate();
    }

    private function normalizeAmount($amount): float
    {
        if ($amount === null) {
            return 0.0;
        }

        $raw = trim((string) $amount);

        if ($raw === '') {
            return 0.0;
        }

        if (preg_match('/^-?\d+(\.\d+)?$/', $raw) === 1) {
            return (float) $raw;
        }

        $raw = str_replace('.', '', $raw);
        $raw = str_replace(',', '.', $raw);

        return (float) $raw;
    }

    private function normalizePaymentMethod(?string $method): ?string
    {
        if ($method === null || trim($method) === '') {
            return null;
        }

        $method = trim(strtolower($method));

        if ($method === 'cartao') {
            return 'cartao_credito';
        }

        return $method;
    }
}
