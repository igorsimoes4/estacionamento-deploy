<?php

namespace App\Services\Parking;

use App\Models\FiscalDocument;
use App\Models\IntegrationEndpoint;
use App\Models\PaymentTransaction;

class FiscalDocumentService
{
    public function issueForTransaction(PaymentTransaction $transaction, string $documentType = 'nfce'): FiscalDocument
    {
        $existing = FiscalDocument::query()
            ->where('source_type', PaymentTransaction::class)
            ->where('source_id', $transaction->id)
            ->where('type', $documentType)
            ->latest('id')
            ->first();

        if ($existing) {
            return $existing;
        }

        $integration = IntegrationEndpoint::query()
            ->where('type', 'fiscal')
            ->where('is_active', true)
            ->first();

        $status = $integration ? 'issued' : 'pending';

        return FiscalDocument::query()->create([
            'type' => $documentType,
            'number' => $integration ? (string) now()->format('YmdHis') : null,
            'series' => $integration ? '1' : null,
            'status' => $status,
            'source_type' => PaymentTransaction::class,
            'source_id' => $transaction->id,
            'customer_name' => 'Consumidor Final',
            'total_cents' => (int) $transaction->amount_cents,
            'issued_at' => $integration ? now() : null,
            'error_message' => $integration ? null : 'Integracao fiscal nao configurada.',
        ]);
    }
}
