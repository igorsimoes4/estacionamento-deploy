<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DynamicPricingRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'vehicle_type',
        'day_of_week',
        'starts_at',
        'ends_at',
        'occupancy_from',
        'occupancy_to',
        'multiplier',
        'flat_addition_cents',
        'is_active',
        'priority',
    ];

    protected $casts = [
        'day_of_week' => 'integer',
        'occupancy_from' => 'integer',
        'occupancy_to' => 'integer',
        'multiplier' => 'decimal:4',
        'flat_addition_cents' => 'integer',
        'is_active' => 'boolean',
        'priority' => 'integer',
    ];

    public function scopeEnabled(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('priority')->orderByDesc('id');
    }
}
