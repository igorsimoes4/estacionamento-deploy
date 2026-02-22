<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class ParkingSector extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'capacity',
        'is_active',
        'color',
        'map_rows',
        'map_columns',
        'notes',
    ];

    protected $casts = [
        'capacity' => 'integer',
        'is_active' => 'boolean',
        'map_rows' => 'integer',
        'map_columns' => 'integer',
    ];

    public function spots(): HasMany
    {
        return $this->hasMany(ParkingSpot::class);
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(ParkingReservation::class);
    }
}
