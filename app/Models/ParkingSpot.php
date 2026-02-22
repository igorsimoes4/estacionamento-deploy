<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class ParkingSpot extends Model
{
    use HasFactory;

    public const STATUS_AVAILABLE = 'available';
    public const STATUS_RESERVED = 'reserved';
    public const STATUS_OCCUPIED = 'occupied';
    public const STATUS_BLOCKED = 'blocked';
    public const STATUS_MAINTENANCE = 'maintenance';

    protected $fillable = [
        'parking_sector_id',
        'code',
        'vehicle_type',
        'status',
        'current_car_id',
        'current_reservation_id',
        'occupied_since',
        'metadata',
    ];

    protected $casts = [
        'current_car_id' => 'integer',
        'current_reservation_id' => 'integer',
        'occupied_since' => 'datetime',
        'metadata' => 'array',
    ];

    public function sector(): BelongsTo
    {
        return $this->belongsTo(ParkingSector::class, 'parking_sector_id');
    }

    public function car(): BelongsTo
    {
        return $this->belongsTo(Cars::class, 'current_car_id');
    }

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(ParkingReservation::class, 'current_reservation_id');
    }
}
