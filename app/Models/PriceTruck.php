<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PriceTruck extends Model
{

    protected $table = "price_trucks";

    protected $fillable = ['valorHora', 'valorMinimo', 'valorDiaria', 'taxaAdicional', 'taxaMensal'];

    use HasFactory;
}
