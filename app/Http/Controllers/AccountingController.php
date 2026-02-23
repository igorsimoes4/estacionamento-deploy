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
        $paymentMethod = trim((string) $request->query('payment_method', ''));
        $search = trim((string) $request->query('q', ''));
        $perPage = (int) $request->query('per_page', 20);

        if (preg_match('/^\d{4}-\d{2}$/', $period) !== 1) {
            $period = now()->format('Y-m');
        }

        if (!in_array($type, ['all', 'receita', 'despesa'], true)) {
            $type = 'all';
        }

        if (!in_array($paymentMethod, ['', 'dinheiro', 'pix', 'cartao_credito', 'cartao_debito', 'transferencia', 'boleto', 'outro'], true)) {
            $paymentMethod = '';
        }

        if (!in_array($perPage, [10, 20, 30, 50], true)) {
            $perPage = 20;
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

        $entriesBase = AccountingEntry::query()
            ->whereBetween('occurred_at', [$start->toDateString(), $end->toDateString()])
            ->when($type !== 'all', fn ($query) => $query->where('type', $type))
            ->when($paymentMethod !== '', fn ($query) => $query->where('payment_method', $paymentMethod))
            ->when($search !== '', function ($query) use ($search): void {
                $term = '%' . $search . '%';
                $query->where(function ($inner) use ($term): void {
                    $inner->where('category', 'like', $term)
                        ->orWhere('description', 'like', $term)
                        ->orWhere('notes', 'like', $term);
                });
            });

        $entries = (clone $entriesBase)
            ->orderByDesc('occurred_at')
            ->orderByDesc('id')
            ->paginate($perPage)
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

        $manualByMethod = AccountingEntry::query()
            ->whereBetween('occurred_at', [$start->toDateString(), $end->toDateString()])
            ->when($type !== 'all', fn ($query) => $query->where('type', $type))
            ->when($paymentMethod !== '', fn ($query) => $query->where('payment_method', $paymentMethod))
            ->when($search !== '', function ($query) use ($search): void {
                $term = '%' . $search . '%';
                $query->where(function ($inner) use ($term): void {
                    $inner->where('category', 'like', $term)
                        ->orWhere('description', 'like', $term)
                        ->orWhere('notes', 'like', $term);
                });
            })
            ->selectRaw("COALESCE(NULLIF(payment_method, ''), 'nao_informado') as payment_method_key")
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('SUM(amount) as amount')
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

        $topCategories = (clone $entriesBase)
            ->selectRaw("COALESCE(NULLIF(category, ''), 'Sem categoria') as category_name")
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("SUM(CASE WHEN type = 'receita' THEN amount ELSE 0 END) as revenue_amount")
            ->selectRaw("SUM(CASE WHEN type = 'despesa' THEN amount ELSE 0 END) as expense_amount")
            ->groupBy('category_name')
            ->orderByDesc('total')
            ->limit(6)
            ->get()
            ->map(function ($row) {
                return (object) [
                    'category' => (string) $row->category_name,
                    'total' => (int) $row->total,
                    'revenue_amount' => (float) $row->revenue_amount,
                    'expense_amount' => (float) $row->expense_amount,
                    'balance_amount' => (float) $row->revenue_amount - (float) $row->expense_amount,
                ];
            });

        $filteredRevenue = (float) (clone $entriesBase)->where('type', 'receita')->sum('amount');
        $filteredExpense = (float) (clone $entriesBase)->where('type', 'despesa')->sum('amount');
        $filteredBalance = $filteredRevenue - $filteredExpense;
        $filteredCount = (int) (clone $entriesBase)->count();

        $paymentMethodOptions = [
            '' => 'Todos',
            'dinheiro' => Cars::paymentMethodLabel('dinheiro'),
            'pix' => Cars::paymentMethodLabel('pix'),
            'cartao_credito' => Cars::paymentMethodLabel('cartao_credito'),
            'cartao_debito' => Cars::paymentMethodLabel('cartao_debito'),
            'transferencia' => Cars::paymentMethodLabel('transferencia'),
            'boleto' => Cars::paymentMethodLabel('boleto'),
            'outro' => Cars::paymentMethodLabel('outro'),
        ];

        $filters = [
            'period' => $period,
            'type' => $type,
            'payment_method' => $paymentMethod,
            'q' => $search,
            'per_page' => $perPage,
        ];

        return view('accounting.index', [
            'entries' => $entries,
            'period' => $period,
            'type' => $type,
            'operationalByMethod' => $operationalByMethod,
            'manualByMethod' => $manualByMethod,
            'topCategories' => $topCategories,
            'paymentMethodOptions' => $paymentMethodOptions,
            'filters' => $filters,
            'stats' => [
                'operational_revenue' => $operationalRevenue,
                'manual_revenue' => $manualRevenue,
                'manual_expense' => $manualExpense,
                'total_revenue' => $totalRevenue,
                'net_result' => $netResult,
                'all_time_balance' => $allTimeBalance,
                'filtered_revenue' => $filteredRevenue,
                'filtered_expense' => $filteredExpense,
                'filtered_balance' => $filteredBalance,
                'filtered_count' => $filteredCount,
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
