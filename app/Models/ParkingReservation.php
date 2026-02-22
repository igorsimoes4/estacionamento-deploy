<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class ParkingReservation extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_CHECKED_IN = 'checked_in';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_NO_SHOW = 'no_show';

    protected $fillable = [
        'reference',
        'customer_name',
        'customer_email',
        'customer_phone',
        'vehicle_plate',
        'vehicle_model',
        'vehicle_type',
        'parking_sector_id',
        'parking_spot_id',
        'starts_at',
        'ends_at',
        'status',
        'estimated_amount_cents',
        'prepaid_amount_cents',
        'payment_status',
        'payment_provider',
        'external_payment_reference',
        'notes',
        'checked_in_at',
        'checked_out_at',
    ];

    protected $casts = [
        'parking_sector_id' => 'integer',
        'parking_spot_id' => 'integer',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'estimated_amount_cents' => 'integer',
        'prepaid_amount_cents' => 'integer',
        'checked_in_at' => 'datetime',
        'checked_out_at' => 'datetime',
    ];

    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->whereIn('status', [self::STATUS_PENDING, self::STATUS_CONFIRMED])
            ->where('starts_at', '>=', now())
            ->orderBy('starts_at');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', [self::STATUS_PENDING, self::STATUS_CONFIRMED, self::STATUS_CHECKED_IN]);
    }

    public function sector(): BelongsTo
    {
        return $this->belongsTo(ParkingSector::class, 'parking_sector_id');
    }

    public function spot(): BelongsTo
    {
        return $this->belongsTo(ParkingSpot::class, 'parking_spot_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(PaymentTransaction::class, 'parking_reservation_id');
    }
}
