<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PriceCar extends Model
{

    protected $table = "price_cars";

    protected $fillable = ['valorHora', 'valorMinimo', 'valorDiaria', 'taxaAdicional', 'taxaMensal'];

    use HasFactory;
}
