<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class CashShift extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'code',
        'opened_at',
        'closed_at',
        'opening_amount_cents',
        'expected_amount_cents',
        'counted_amount_cents',
        'difference_amount_cents',
        'status',
        'notes',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
        'opening_amount_cents' => 'integer',
        'expected_amount_cents' => 'integer',
        'counted_amount_cents' => 'integer',
        'difference_amount_cents' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function movements(): HasMany
    {
        return $this->hasMany(CashShiftMovement::class);
    }
}
