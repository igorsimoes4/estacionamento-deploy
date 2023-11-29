<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PriceMotorcycle extends Model
{

    protected $table = "price_motorcycles";

    protected $fillable = ['valorHora', 'valorMinimo', 'valorDiaria', 'taxaAdicional', 'taxaMensal'];

    use HasFactory;
}
