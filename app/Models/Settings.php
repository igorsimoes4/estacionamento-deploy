<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Settings extends Model
{

    protected $table = 'settings';

    protected $fillable = [
            'nome_da_empresa',
            'endereco',
            'cidade',
            'estado',
            'cep',
            'telefone_da_empresa',
            'email_da_empresa',
            'numero_de_registro_da_empresa',
            'cnpj_cpf_da_empresa',
            'descricao_da_empresa',
            'coordenadas_gps',
            'pix_key',
            'pix_beneficiary_name',
            'pix_city',
            'pix_description',
            'card_machine_instructions',
            'payment_provider_default',
            'payment_environment',
            'pagbank_token',
            'pagbank_api_base_url',
            'cielo_merchant_id',
            'cielo_merchant_key',
            'cielo_api_base_url',
            'stone_api_token',
            'stone_api_base_url',
            'rede_api_token',
            'rede_api_base_url',
            'getnet_client_id',
            'getnet_client_secret',
            'getnet_seller_id',
            'getnet_api_base_url',
            'boleto_due_days',
            'ticket_print_enabled',
            'ticket_printer_driver',
            'ticket_printer_target',
            'ticket_printer_port',
            'ticket_printer_timeout',
            'ticket_print_copies',
            'ticket_line_width',
    ];

    protected $casts = [
        'boleto_due_days' => 'integer',
        'ticket_print_enabled' => 'boolean',
        'ticket_printer_port' => 'integer',
        'ticket_printer_timeout' => 'integer',
        'ticket_print_copies' => 'integer',
        'ticket_line_width' => 'integer',
    ];

    use HasFactory;
}
