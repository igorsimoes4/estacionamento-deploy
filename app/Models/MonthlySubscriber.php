<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class MonthlySubscriber extends Model
{
    use HasFactory;

    protected $table = 'monthly_subscribers';

    protected $fillable = [
        'name',
        'cpf',
        'phone',
        'email',
        'vehicle_plate',
        'vehicle_model',
        'vehicle_color',
        'vehicle_type',
        'start_date',
        'end_date',
        'monthly_fee',
        'is_active',
        'observations',
        'access_enabled',
        'access_password',
        'access_last_login_at',
        'boleto_reference',
        'boleto_provider',
        'boleto_url',
        'boleto_barcode',
        'boleto_digitable_line',
        'boleto_due_date',
        'boleto_amount_cents',
        'boleto_status',
        'boleto_generated_at',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'monthly_fee' => 'decimal:2',
        'is_active' => 'boolean',
        'access_enabled' => 'boolean',
        'access_last_login_at' => 'datetime',
        'boleto_due_date' => 'date',
        'boleto_generated_at' => 'datetime',
        'boleto_amount_cents' => 'integer',
    ];

    protected $hidden = [
        'access_password',
    ];

    public static function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'cpf' => 'required|string|unique:monthly_subscribers,cpf',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'vehicle_plate' => 'required|string|unique:monthly_subscribers,vehicle_plate',
            'vehicle_model' => 'nullable|string|max:255',
            'vehicle_color' => 'nullable|string|max:50',
            'vehicle_type' => 'required|in:carro,moto,caminhonete,caminhao',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'monthly_fee' => 'required|numeric|min:0',
            'observations' => 'nullable|string'
        ];
    }

    protected static function booted()
    {
        static::saving(function (MonthlySubscriber $subscriber) {
            if ($subscriber->vehicle_type === 'caminhao') {
                $subscriber->vehicle_type = 'caminhonete';
            }

            $endDate = $subscriber->end_date instanceof Carbon
                ? $subscriber->end_date
                : Carbon::parse($subscriber->end_date);

            $subscriber->is_active = $endDate->isFuture() || $endDate->isToday();
        });
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeExpiringSoon(Builder $query, int $days = 7): Builder
    {
        return $query->whereBetween('end_date', [Carbon::today(), Carbon::today()->addDays($days)]);
    }

    public function setAccessPasswordAttribute($value): void
    {
        if ($value === null || $value === '') {
            return;
        }

        $this->attributes['access_password'] = Hash::make((string) $value);
    }

    public function hasPortalAccessConfigured(): bool
    {
        return $this->access_enabled && !empty($this->access_password);
    }
} 
