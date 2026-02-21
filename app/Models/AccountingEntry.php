<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class AccountingEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'category',
        'description',
        'amount',
        'occurred_at',
        'payment_method',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'occurred_at' => 'date',
    ];

    public function scopeRevenue(Builder $query): Builder
    {
        return $query->where('type', 'receita');
    }

    public function scopeExpense(Builder $query): Builder
    {
        return $query->where('type', 'despesa');
    }
}

