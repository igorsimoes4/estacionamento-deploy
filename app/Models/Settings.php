<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Settings extends Model
{

    protected $table = 'settings';

    protected $fillable = [
            'nome_da_Empresa',
            'endereco',
            'cidade',
            'estado',
            'cep',
            'telefone_da_empresa',
            'email_da_empresa',
            'numero_de_registro_da_Empresa',
            'cnpj_Cpf_da_empresa',
            'descricao_da_empresa',
            'coordenadas_gps',
    ];

    use HasFactory;
}
