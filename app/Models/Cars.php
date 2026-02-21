<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Cars extends Model
{
    use HasFactory;

    public const PAYMENT_PROVIDERS = [
        'manual',
        'stone',
        'cielo',
        'rede',
        'getnet',
        'pagbank',
    ];

    public const PAYMENT_METHODS = [
        'dinheiro',
        'pix',
        'boleto',
        'cartao_credito',
        'cartao_debito',
        'transferencia',
        'boleto',
        'outro',
    ];

    protected $table = 'cars';

    protected $fillable = [
        'modelo',
        'placa',
        'entrada',
        'preco',
        'tipo_car',
        'status',
        'saida',
        'payment_method',
        'payment_provider',
        'payment_status',
        'external_payment_id',
        'payment_url',
        'payment_reference',
        'paid_at',
    ];

    protected $casts = [
        'preco' => 'decimal:2',
        'saida' => 'datetime',
        'paid_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function scopeParked(Builder $query): Builder
    {
        return $query
            ->whereNull('status')
            ->whereNull('saida');
    }

    public function scopeFinished(Builder $query): Builder
    {
        return $query->where('status', 'finalizado');
    }

    public static function paymentMethodLabel(?string $method): string
    {
        return match ($method) {
            'dinheiro' => 'Dinheiro',
            'pix' => 'Pix',
            'boleto' => 'Boleto',
            'cartao' => 'Cartao',
            'cartao_credito' => 'Cartao credito',
            'cartao_debito' => 'Cartao debito',
            'transferencia' => 'Transferencia',
            'boleto' => 'Boleto',
            'outro' => 'Outro',
            default => 'Nao informado',
        };
    }

    public static function paymentProviderLabel(?string $provider): string
    {
        return match ($provider) {
            'stone' => 'Stone',
            'cielo' => 'Cielo',
            'rede' => 'Rede',
            'getnet' => 'Getnet',
            'pagbank' => 'PagBank',
            'manual' => 'Manual',
            default => 'Nao informado',
        };
    }
}
