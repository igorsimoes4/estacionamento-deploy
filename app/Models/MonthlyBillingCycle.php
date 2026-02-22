<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class MonthlyBillingCycle extends Model
{
    use HasFactory;

    protected $fillable = [
        'monthly_subscriber_id',
        'reference',
        'competency',
        'due_date',
        'amount_cents',
        'fine_cents',
        'interest_cents',
        'total_amount_cents',
        'status',
        'payment_transaction_id',
        'notified_at',
        'blocked_at',
        'paid_at',
    ];

    protected $casts = [
        'monthly_subscriber_id' => 'integer',
        'due_date' => 'date',
        'amount_cents' => 'integer',
        'fine_cents' => 'integer',
        'interest_cents' => 'integer',
        'total_amount_cents' => 'integer',
        'payment_transaction_id' => 'integer',
        'notified_at' => 'datetime',
        'blocked_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereIn('status', ['pending', 'overdue']);
    }

    public function subscriber(): BelongsTo
    {
        return $this->belongsTo(MonthlySubscriber::class, 'monthly_subscriber_id');
    }

    public function paymentTransaction(): BelongsTo
    {
        return $this->belongsTo(PaymentTransaction::class, 'payment_transaction_id');
    }
}
