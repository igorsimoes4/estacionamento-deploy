<?php

namespace App\Http\Controllers;

use App\Models\CashShift;
use App\Models\CashShiftMovement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CashShiftController extends Controller
{
    public function index(Request $request): View
    {
        $filters = [
            'status' => trim((string) $request->query('status', 'all')),
            'q' => trim((string) $request->query('q', '')),
            'date_from' => trim((string) $request->query('date_from', '')),
            'date_to' => trim((string) $request->query('date_to', '')),
            'per_page' => (int) $request->query('per_page', 20),
        ];

        if (!in_array($filters['status'], ['all', 'open', 'closed'], true)) {
            $filters['status'] = 'all';
        }

        if (!in_array($filters['per_page'], [10, 20, 30, 50], true)) {
            $filters['per_page'] = 20;
        }

        $openShift = CashShift::query()
            ->where('status', 'open')
            ->latest('opened_at')
            ->with([
                'user:id,name',
                'movements' => fn ($query) => $query->with('user:id,name')->latest('occurred_at'),
            ])
            ->first();

        $historyQuery = CashShift::query()
            ->with(['user:id,name'])
            ->withCount('movements');

        if ($filters['status'] !== 'all') {
            $historyQuery->where('status', $filters['status']);
        }

        if ($filters['q'] !== '') {
            $term = '%' . $filters['q'] . '%';
            $historyQuery->where(function ($inner) use ($term): void {
                $inner->where('code', 'like', $term)
                    ->orWhere('notes', 'like', $term)
                    ->orWhereHas('user', fn ($query) => $query->where('name', 'like', $term));
            });
        }

        if ($filters['date_from'] !== '') {
            $historyQuery->whereDate('opened_at', '>=', $filters['date_from']);
        }

        if ($filters['date_to'] !== '') {
            $historyQuery->whereDate('opened_at', '<=', $filters['date_to']);
        }

        $filteredTotal = (clone $historyQuery)->count();

        $history = $historyQuery
            ->latest('opened_at')
            ->paginate($filters['per_page'])
            ->withQueryString();

        $stats = [
            'open_count' => CashShift::query()->where('status', 'open')->count(),
            'closed_today' => CashShift::query()->where('status', 'closed')->whereDate('closed_at', today())->count(),
            'movements_today' => CashShiftMovement::query()->whereDate('occurred_at', today())->count(),
            'divergence_today_cents' => (int) CashShift::query()
                ->where('status', 'closed')
                ->whereDate('closed_at', today())
                ->sum('difference_amount_cents'),
            'filtered_total' => $filteredTotal,
        ];

        $movementTypeStats = [];
        $movementMethodStats = [];
        $movementTotals = [
            'entries_cents' => 0,
            'withdrawals_cents' => 0,
            'net_cents' => 0,
            'count' => 0,
        ];

        if ($openShift) {
            $movementTypeStats = $openShift->movements
                ->groupBy('type')
                ->map(fn ($items) => [
                    'count' => $items->count(),
                    'amount_cents' => (int) $items->sum('amount_cents'),
                ])
                ->sortKeys()
                ->toArray();

            $movementMethodStats = $openShift->movements
                ->groupBy(fn ($movement) => $movement->method ?: 'nao_informado')
                ->map(fn ($items) => [
                    'count' => $items->count(),
                    'amount_cents' => (int) $items->sum('amount_cents'),
                ])
                ->sortKeys()
                ->toArray();

            $entriesCents = (int) $openShift->movements
                ->whereNotIn('type', ['sangria', 'saida'])
                ->sum('amount_cents');
            $withdrawalsCents = (int) $openShift->movements
                ->whereIn('type', ['sangria', 'saida'])
                ->sum('amount_cents');

            $movementTotals = [
                'entries_cents' => $entriesCents,
                'withdrawals_cents' => $withdrawalsCents,
                'net_cents' => $entriesCents - $withdrawalsCents,
                'count' => $openShift->movements->count(),
            ];
        }

        return view(
            'cash_shifts.index',
            compact(
                'openShift',
                'history',
                'filters',
                'stats',
                'movementTypeStats',
                'movementMethodStats',
                'movementTotals'
            )
        );
    }

    public function open(Request $request): RedirectResponse
    {
        $payload = $request->validate([
            'opening_amount' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ]);

        $alreadyOpen = CashShift::query()->where('status', 'open')->exists();
        if ($alreadyOpen) {
            return back()->with('error', 'Ja existe um caixa aberto.');
        }

        $openingAmountCents = (int) round(((float) $payload['opening_amount']) * 100);

        CashShift::query()->create([
            'user_id' => Auth::id(),
            'code' => 'CX-' . now()->format('YmdHis'),
            'opened_at' => now(),
            'opening_amount_cents' => $openingAmountCents,
            'expected_amount_cents' => $openingAmountCents,
            'status' => 'open',
            'notes' => $payload['notes'] ?? null,
        ]);

        return back()->with('create', 'Caixa aberto com sucesso.');
    }

    public function addMovement(Request $request, CashShift $cashShift): RedirectResponse
    {
        if ($cashShift->status !== 'open') {
            return back()->with('error', 'Somente caixa aberto aceita movimentacao.');
        }

        $payload = $request->validate([
            'type' => ['required', 'in:venda,entrada,reforco,sangria,estorno,saida'],
            'method' => ['nullable', 'in:dinheiro,pix,boleto,cartao_credito,cartao_debito,transferencia,outro'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        $amountCents = (int) round(((float) $payload['amount']) * 100);

        CashShiftMovement::query()->create([
            'cash_shift_id' => $cashShift->id,
            'user_id' => Auth::id(),
            'type' => $payload['type'],
            'method' => $payload['method'] ?? null,
            'amount_cents' => $amountCents,
            'description' => $payload['description'] ?? null,
            'occurred_at' => now(),
        ]);

        if (in_array($payload['type'], ['sangria', 'saida'], true)) {
            $cashShift->expected_amount_cents -= $amountCents;
        } else {
            $cashShift->expected_amount_cents += $amountCents;
        }

        $cashShift->save();

        return back()->with('create', 'Movimentacao registrada.');
    }

    public function close(Request $request, CashShift $cashShift): RedirectResponse
    {
        if ($cashShift->status !== 'open') {
            return back()->with('error', 'Este caixa ja esta fechado.');
        }

        $payload = $request->validate([
            'counted_amount' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ]);

        $countedCents = (int) round(((float) $payload['counted_amount']) * 100);

        $cashShift->counted_amount_cents = $countedCents;
        $cashShift->difference_amount_cents = $countedCents - (int) $cashShift->expected_amount_cents;
        $cashShift->closed_at = now();
        $cashShift->status = 'closed';
        $cashShift->notes = trim((string) ($cashShift->notes . PHP_EOL . ($payload['notes'] ?? '')));
        $cashShift->save();

        return back()->with('create', 'Caixa fechado com sucesso.');
    }
}
