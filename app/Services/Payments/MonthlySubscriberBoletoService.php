<?php

namespace App\Services\Payments;

use App\Models\MonthlySubscriber;
use App\Models\Settings;
use Carbon\Carbon;
use Throwable;

class MonthlySubscriberBoletoService
{
    public function generateForSubscriber(MonthlySubscriber $subscriber, bool $force = false): array
    {
        $settings = Settings::firstOrCreate(['id' => 1], []);
        $reference = $this->buildMonthlyReference($subscriber);
        $amount = round((float) $subscriber->monthly_fee, 2);
        $amountCents = max(0, (int) round($amount * 100));
        $dueDate = Carbon::today()->addDays(max(1, (int) ($settings->boleto_due_days ?: 3)));

        if (
            !$force
            && (string) $subscriber->boleto_reference === $reference
            && (int) ($subscriber->boleto_amount_cents ?? 0) === $amountCents
            && !empty($subscriber->boleto_generated_at)
            && (
                trim((string) $subscriber->boleto_barcode) !== ''
                || trim((string) $subscriber->boleto_digitable_line) !== ''
            )
        ) {
            return $this->extractStoredBoleto($subscriber, $dueDate);
        }

        $provider = strtolower(trim((string) ($settings->payment_provider_default ?: 'manual')));
        $payload = [
            'reference' => $reference,
            'amount' => $amount,
            'amount_cents' => $amountCents,
            'description' => 'Mensalidade estacionamento ' . now()->format('m/Y'),
            'customer_name' => (string) $subscriber->name,
            'customer_email' => (string) ($subscriber->email ?: ''),
            'customer_tax_id' => $this->sanitizeDigits((string) $subscriber->cpf, 11),
            'beneficiary_name' => (string) ($settings->nome_da_empresa ?: 'Estacionamento'),
        ];

        $result = [];
        $warning = '';

        if (in_array($provider, ['pagbank', 'cielo'], true)) {
            try {
                $result = app(CheckoutGatewayService::class)->createBoleto($provider, $settings, $payload);
            } catch (Throwable $e) {
                $warning = $e->getMessage();
                $result = $this->manualBoletoData($reference, $amountCents, $dueDate, $warning);
            }
        } else {
            $result = $this->manualBoletoData($reference, $amountCents, $dueDate, 'Gateway sem suporte de boleto automatico.');
        }

        $resolvedDueDate = $this->normalizeDueDate((string) ($result['boleto_due_date'] ?? $dueDate->toDateString()), $dueDate);

        $subscriber->forceFill([
            'boleto_reference' => $reference,
            'boleto_provider' => (string) ($result['provider'] ?? $provider ?: 'manual'),
            'boleto_url' => (string) ($result['boleto_url'] ?? ''),
            'boleto_barcode' => (string) ($result['boleto_barcode'] ?? ''),
            'boleto_digitable_line' => (string) ($result['boleto_digitable_line'] ?? ''),
            'boleto_due_date' => $resolvedDueDate->toDateString(),
            'boleto_amount_cents' => $amountCents,
            'boleto_status' => strtoupper((string) ($result['status'] ?? 'PENDING')),
            'boleto_generated_at' => now(),
        ])->save();

        $subscriber->refresh();

        return array_merge($this->extractStoredBoleto($subscriber, $resolvedDueDate), [
            'warning' => $warning !== '' ? $warning : (string) ($result['warning'] ?? ''),
        ]);
    }

    private function extractStoredBoleto(MonthlySubscriber $subscriber, Carbon $defaultDueDate): array
    {
        $dueDate = $subscriber->boleto_due_date instanceof Carbon
            ? $subscriber->boleto_due_date
            : $defaultDueDate;

        return [
            'reference' => (string) ($subscriber->boleto_reference ?: $this->buildMonthlyReference($subscriber)),
            'provider' => (string) ($subscriber->boleto_provider ?: 'manual'),
            'url' => (string) ($subscriber->boleto_url ?: ''),
            'barcode' => (string) ($subscriber->boleto_barcode ?: ''),
            'digitable_line' => (string) ($subscriber->boleto_digitable_line ?: ''),
            'due_date' => $dueDate,
            'amount_cents' => (int) ($subscriber->boleto_amount_cents ?? 0),
            'amount' => ((int) ($subscriber->boleto_amount_cents ?? 0)) / 100,
            'status' => (string) ($subscriber->boleto_status ?: 'PENDING'),
            'generated_at' => $subscriber->boleto_generated_at,
            'warning' => '',
        ];
    }

    private function manualBoletoData(string $reference, int $amountCents, Carbon $dueDate, string $warning = ''): array
    {
        $digitableLine = $this->buildManualDigitableLine($reference, $amountCents, $dueDate);
        $barcode = preg_replace('/\D+/', '', $digitableLine) ?? '';

        return [
            'provider' => 'manual',
            'boleto_url' => '',
            'boleto_barcode' => $barcode,
            'boleto_digitable_line' => $digitableLine,
            'boleto_due_date' => $dueDate->toDateString(),
            'status' => 'PENDING',
            'warning' => $warning !== '' ? $warning : 'Boleto gerado em modo manual.',
        ];
    }

    private function buildManualDigitableLine(string $reference, int $amountCents, Carbon $dueDate): string
    {
        $digits = preg_replace('/\D+/', '', $reference . $dueDate->format('Ymd') . str_pad((string) $amountCents, 14, '0', STR_PAD_LEFT)) ?? '';
        $digits = str_pad(substr($digits, 0, 47), 47, '0');

        return substr($digits, 0, 5) . '.' . substr($digits, 5, 5)
            . ' ' . substr($digits, 10, 5) . '.' . substr($digits, 15, 6)
            . ' ' . substr($digits, 21, 5) . '.' . substr($digits, 26, 6)
            . ' ' . substr($digits, 32, 1)
            . ' ' . substr($digits, 33, 14);
    }

    private function buildMonthlyReference(MonthlySubscriber $subscriber): string
    {
        return 'MS-' . $subscriber->id . '-' . now()->format('Ym');
    }

    private function sanitizeDigits(string $value, int $fallbackSize): string
    {
        $digits = preg_replace('/\D+/', '', $value) ?? '';
        if ($digits === '') {
            return str_pad('', $fallbackSize, '0');
        }

        return $digits;
    }

    private function normalizeDueDate(string $raw, Carbon $fallback): Carbon
    {
        try {
            if (trim($raw) !== '') {
                return Carbon::parse($raw);
            }
        } catch (Throwable $e) {
            // Keep fallback date if parsing fails.
        }

        return $fallback;
    }
}
