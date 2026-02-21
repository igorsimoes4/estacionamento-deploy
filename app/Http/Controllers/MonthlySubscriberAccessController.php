<?php

namespace App\Http\Controllers;

use App\Models\MonthlySubscriber;
use App\Models\Settings;
use App\Services\Payments\MonthlySubscriberBoletoService;
use App\Support\ItfBarcode;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class MonthlySubscriberAccessController extends Controller
{
    public const SESSION_KEY = 'monthly_subscriber_id';

    public function loginForm(Request $request): View|RedirectResponse
    {
        if ($request->session()->has(self::SESSION_KEY)) {
            return redirect()->route('monthly-access.dashboard');
        }

        return view('monthly_subscribers.portal.login');
    }

    public function authenticate(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'cpf' => ['required', 'string'],
            'access_password' => ['required', 'string'],
        ], [
            'cpf.required' => 'Informe o CPF.',
            'access_password.required' => 'Informe a senha.',
        ]);

        $cpfDigits = preg_replace('/\D+/', '', (string) $credentials['cpf']);
        $subscriber = MonthlySubscriber::query()
            ->whereRaw("REPLACE(REPLACE(cpf, '.', ''), '-', '') = ?", [$cpfDigits])
            ->first();

        if (!$subscriber || !$subscriber->access_enabled || empty($subscriber->access_password)) {
            return back()
                ->withInput($request->only('cpf'))
                ->withErrors(['cpf' => 'Acesso nao configurado para este mensalista.']);
        }

        if (!$subscriber->is_active) {
            return back()
                ->withInput($request->only('cpf'))
                ->withErrors(['cpf' => 'Mensalidade inativa. Procure a administracao.']);
        }

        if (!Hash::check((string) $credentials['access_password'], (string) $subscriber->access_password)) {
            return back()
                ->withInput($request->only('cpf'))
                ->withErrors(['access_password' => 'CPF ou senha invalidos.']);
        }

        $request->session()->regenerate();
        $request->session()->put(self::SESSION_KEY, $subscriber->id);

        $subscriber->forceFill([
            'access_last_login_at' => now(),
        ])->save();

        return redirect()->route('monthly-access.dashboard');
    }

    public function dashboard(Request $request): View
    {
        /** @var MonthlySubscriber $subscriber */
        $subscriber = $request->attributes->get('monthly_subscriber');

        return view('monthly_subscribers.portal.dashboard', [
            'subscriber' => $subscriber,
            'hasBoleto' => !empty($subscriber->boleto_reference),
        ]);
    }

    public function downloadBoleto(Request $request)
    {
        /** @var MonthlySubscriber $subscriber */
        $subscriber = $request->attributes->get('monthly_subscriber');

        $boleto = app(MonthlySubscriberBoletoService::class)->generateForSubscriber($subscriber);
        $barcodeDigits = preg_replace('/\D+/', '', (string) ($boleto['barcode'] ?: $boleto['digitable_line'])) ?? '';

        if ($barcodeDigits === '') {
            $fallback = preg_replace('/\D+/', '', (string) ($boleto['reference'] ?? ''))
                . optional($boleto['due_date'] ?? null)->format('Ymd')
                . str_pad((string) ((int) ($boleto['amount_cents'] ?? 0)), 14, '0', STR_PAD_LEFT);
            $barcodeDigits = substr($fallback, 0, 48);
        }

        $barcodeSvg = ItfBarcode::renderSvg($barcodeDigits);
        $barcodeSvgDataUri = $barcodeSvg !== ''
            ? 'data:image/svg+xml;base64,' . base64_encode($barcodeSvg)
            : '';

        $pdf = PDF::loadView('monthly_subscribers.portal.boleto', [
            'subscriber' => $subscriber,
            'settings' => Settings::firstOrCreate(['id' => 1], []),
            'boleto' => $boleto,
            'barcodeDigits' => $barcodeDigits,
            'barcodeSvg' => $barcodeSvg,
            'barcodeSvgDataUri' => $barcodeSvgDataUri,
        ])->setPaper('A4', 'portrait');

        $filename = 'boleto-mensalista-' . $subscriber->id . '-' . now()->format('Ym') . '.pdf';

        return $pdf->download($filename);
    }

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->forget(self::SESSION_KEY);
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('monthly-access.login')
            ->with('status', 'Sessao encerrada com sucesso.');
    }
}
