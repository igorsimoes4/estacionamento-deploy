<?php

namespace App\Http\Controllers;

use App\Models\FiscalDocument;
use App\Models\MonthlyBillingCycle;
use App\Models\PaymentTransaction;
use App\Services\Parking\DelinquencyService;
use App\Services\Parking\FiscalDocumentService;
use App\Services\Parking\RecurringBillingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OperationsFinanceController extends Controller
{
    public function index(Request $request): View
    {
        $transactions = PaymentTransaction::query()->latest('id')->paginate(15, ['*'], 'transactions_page');
        $billingCycles = MonthlyBillingCycle::query()->latest('id')->paginate(15, ['*'], 'cycles_page');
        $fiscalDocuments = FiscalDocument::query()->latest('id')->paginate(15, ['*'], 'fiscal_page');

        $summary = [
            'pending_transactions' => PaymentTransaction::query()->where('status', 'pending')->count(),
            'overdue_cycles' => MonthlyBillingCycle::query()->where('status', 'overdue')->count(),
            'paid_today' => (int) PaymentTransaction::query()->whereDate('paid_at', today())->sum('amount_cents'),
            'fiscal_pending' => FiscalDocument::query()->where('status', 'pending')->count(),
        ];

        return view('operations.finance', compact('transactions', 'billingCycles', 'fiscalDocuments', 'summary'));
    }

    public function runRecurringBilling(RecurringBillingService $service): RedirectResponse
    {
        $cycles = $service->run();

        return back()->with('create', 'Rotina de cobranca executada. Ciclos gerados: ' . $cycles->count());
    }

    public function runDelinquency(DelinquencyService $service): RedirectResponse
    {
        $result = $service->process();

        return back()->with('create', 'Inadimplencia processada. Ciclos: ' . (int) ($result['cycles_updated'] ?? 0));
    }

    public function issueFiscal(Request $request, FiscalDocumentService $service): RedirectResponse
    {
        $payload = $request->validate([
            'transaction_id' => ['required', 'integer', 'exists:payment_transactions,id'],
            'document_type' => ['nullable', 'in:nfce,nfse'],
        ]);

        $transaction = PaymentTransaction::query()->findOrFail((int) $payload['transaction_id']);
        $type = $payload['document_type'] ?? 'nfce';

        $service->issueForTransaction($transaction, $type);

        return back()->with('create', 'Documento fiscal solicitado com sucesso.');
    }
}
