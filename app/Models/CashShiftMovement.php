<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class CashShiftMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'cash_shift_id',
        'user_id',
        'type',
        'method',
        'amount_cents',
        'description',
        'occurred_at',
    ];

    protected $casts = [
        'cash_shift_id' => 'integer',
        'user_id' => 'integer',
        'amount_cents' => 'integer',
        'occurred_at' => 'datetime',
    ];

    public function shift(): BelongsTo
    {
        return $this->belongsTo(CashShift::class, 'cash_shift_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
