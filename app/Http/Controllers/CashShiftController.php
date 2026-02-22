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
    public function index(): View
    {
        $openShift = CashShift::query()->where('status', 'open')->latest('opened_at')->with('movements')->first();
        $history = CashShift::query()->latest('opened_at')->paginate(20);

        return view('cash_shifts.index', compact('openShift', 'history'));
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
