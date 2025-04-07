<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        'observations'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'monthly_fee' => 'decimal:2',
        'is_active' => 'boolean'
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
            'vehicle_type' => 'required|in:carro,moto,caminhao',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'monthly_fee' => 'required|numeric|min:0',
            'observations' => 'nullable|string'
        ];
    }
} 