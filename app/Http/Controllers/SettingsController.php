<?php

namespace App\Http\Controllers;

use App\Models\PriceCar;
use App\Models\PriceMotorcycle;
use App\Models\PriceTruck;
use App\Models\Settings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SettingsController extends Controller
{
    public function index()
    {
        $estacionamento = Settings::firstOrCreate(['id' => 1], []);

        return view('settings', [
            'estacionamento' => $estacionamento,
            'route' => 'editSettings',
        ]);
    }

    public function paymentSettings()
    {
        $estacionamento = Settings::firstOrCreate(['id' => 1], []);

        return view('settings_payments', [
            'estacionamento' => $estacionamento,
            'route' => 'editPaymentSettings',
        ]);
    }

    public function editSettings(Request $req)
    {
        $data = $req->only([
            'nomeDaEmpresa',
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
        ]);

        $validator = Validator::make($data, [
            'nomeDaEmpresa' => ['required', 'string', 'max:255'],
            'endereco' => ['required', 'string', 'max:255'],
            'cidade' => ['required', 'string', 'max:100'],
            'estado' => ['required', 'string', 'max:50'],
            'cep' => ['required', 'string', 'max:20'],
            'telefone_da_empresa' => ['required', 'string', 'max:30'],
            'email_da_empresa' => ['required', 'email', 'max:255'],
            'numero_de_registro_da_Empresa' => ['required', 'string', 'max:255'],
            'cnpj_Cpf_da_empresa' => ['required', 'string', 'max:30'],
            'descricao_da_empresa' => ['nullable', 'string'],
            'coordenadas_gps' => ['nullable', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            return redirect()->route('settings')->withErrors($validator)->withInput();
        }

        $estacionamento = Settings::firstOrCreate(['id' => 1], []);
        $estacionamento->nome_da_empresa = $data['nomeDaEmpresa'];
        $estacionamento->endereco = $data['endereco'];
        $estacionamento->cidade = $data['cidade'];
        $estacionamento->estado = $data['estado'];
        $estacionamento->cep = $data['cep'];
        $estacionamento->telefone_da_empresa = $data['telefone_da_empresa'];
        $estacionamento->email_da_empresa = $data['email_da_empresa'];
        $estacionamento->numero_de_registro_da_empresa = $data['numero_de_registro_da_Empresa'];
        $estacionamento->cnpj_cpf_da_empresa = $data['cnpj_Cpf_da_empresa'];
        $estacionamento->descricao_da_empresa = $data['descricao_da_empresa'];
        $estacionamento->coordenadas_gps = $data['coordenadas_gps'];
        $estacionamento->save();

        return redirect()->route('settings')->with('create', 'Informacoes atualizadas com sucesso.');
    }

    public function editPaymentSettings(Request $req)
    {
        $data = $req->only([
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
        ]);

        $validator = Validator::make($data, [
            'pix_key' => ['nullable', 'string', 'max:255'],
            'pix_beneficiary_name' => ['nullable', 'string', 'max:255'],
            'pix_city' => ['nullable', 'string', 'max:100'],
            'pix_description' => ['nullable', 'string', 'max:80'],
            'card_machine_instructions' => ['nullable', 'string', 'max:2000'],
            'payment_provider_default' => ['nullable', 'in:manual,stone,cielo,rede,getnet,pagbank'],
            'payment_environment' => ['nullable', 'in:sandbox,production'],
            'pagbank_token' => ['nullable', 'string', 'max:255'],
            'pagbank_api_base_url' => ['nullable', 'url', 'max:255'],
            'cielo_merchant_id' => ['nullable', 'string', 'max:255'],
            'cielo_merchant_key' => ['nullable', 'string', 'max:255'],
            'cielo_api_base_url' => ['nullable', 'url', 'max:255'],
            'stone_api_token' => ['nullable', 'string', 'max:255'],
            'stone_api_base_url' => ['nullable', 'url', 'max:255'],
            'rede_api_token' => ['nullable', 'string', 'max:255'],
            'rede_api_base_url' => ['nullable', 'url', 'max:255'],
            'getnet_client_id' => ['nullable', 'string', 'max:255'],
            'getnet_client_secret' => ['nullable', 'string', 'max:255'],
            'getnet_seller_id' => ['nullable', 'string', 'max:255'],
            'getnet_api_base_url' => ['nullable', 'url', 'max:255'],
            'boleto_due_days' => ['nullable', 'integer', 'min:1', 'max:30'],
            'ticket_print_enabled' => ['nullable', 'boolean'],
            'ticket_printer_driver' => ['nullable', 'in:windows,cups,network,file'],
            'ticket_printer_target' => ['nullable', 'string', 'max:255'],
            'ticket_printer_port' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'ticket_printer_timeout' => ['nullable', 'integer', 'min:1', 'max:60'],
            'ticket_print_copies' => ['nullable', 'integer', 'min:1', 'max:5'],
            'ticket_line_width' => ['nullable', 'integer', 'min:16', 'max:64'],
        ]);

        if ($validator->fails()) {
            return redirect()->route('paymentSettings')->withErrors($validator)->withInput();
        }

        $estacionamento = Settings::firstOrCreate(['id' => 1], []);
        $estacionamento->pix_key = $data['pix_key'] ?? null;
        $estacionamento->pix_beneficiary_name = $data['pix_beneficiary_name'] ?? null;
        $estacionamento->pix_city = $data['pix_city'] ?? null;
        $estacionamento->pix_description = $data['pix_description'] ?? null;
        $estacionamento->card_machine_instructions = $data['card_machine_instructions'] ?? null;
        $estacionamento->payment_provider_default = $data['payment_provider_default'] ?? 'manual';
        $estacionamento->payment_environment = $data['payment_environment'] ?? 'sandbox';
        $estacionamento->pagbank_token = $data['pagbank_token'] ?? null;
        $estacionamento->pagbank_api_base_url = $data['pagbank_api_base_url'] ?? null;
        $estacionamento->cielo_merchant_id = $data['cielo_merchant_id'] ?? null;
        $estacionamento->cielo_merchant_key = $data['cielo_merchant_key'] ?? null;
        $estacionamento->cielo_api_base_url = $data['cielo_api_base_url'] ?? null;
        $estacionamento->stone_api_token = $data['stone_api_token'] ?? null;
        $estacionamento->stone_api_base_url = $data['stone_api_base_url'] ?? null;
        $estacionamento->rede_api_token = $data['rede_api_token'] ?? null;
        $estacionamento->rede_api_base_url = $data['rede_api_base_url'] ?? null;
        $estacionamento->getnet_client_id = $data['getnet_client_id'] ?? null;
        $estacionamento->getnet_client_secret = $data['getnet_client_secret'] ?? null;
        $estacionamento->getnet_seller_id = $data['getnet_seller_id'] ?? null;
        $estacionamento->getnet_api_base_url = $data['getnet_api_base_url'] ?? null;
        $estacionamento->boleto_due_days = isset($data['boleto_due_days']) ? (int) $data['boleto_due_days'] : 3;
        $estacionamento->ticket_print_enabled = $req->boolean('ticket_print_enabled', false);
        $estacionamento->ticket_printer_driver = $data['ticket_printer_driver'] ?? 'windows';
        $estacionamento->ticket_printer_target = $data['ticket_printer_target'] ?? null;
        $estacionamento->ticket_printer_port = isset($data['ticket_printer_port']) ? (int) $data['ticket_printer_port'] : null;
        $estacionamento->ticket_printer_timeout = isset($data['ticket_printer_timeout']) ? (int) $data['ticket_printer_timeout'] : 10;
        $estacionamento->ticket_print_copies = isset($data['ticket_print_copies']) ? (int) $data['ticket_print_copies'] : 1;
        $estacionamento->ticket_line_width = isset($data['ticket_line_width']) ? (int) $data['ticket_line_width'] : 42;
        $estacionamento->save();

        return redirect()->route('paymentSettings')->with('create', 'Configuracoes de pagamento atualizadas com sucesso.');
    }

    public function priceCar()
    {
        $priceCar = $this->ensurePriceRecord(PriceCar::class, [
            'valorHora' => 5,
            'valorMinimo' => 10,
            'valorDiaria' => 50,
            'taxaAdicional' => 17,
            'taxaMensal' => 400,
        ]);

        return view('priceCar', [
            'priceCar' => $priceCar,
            'route' => 'editPriceCar',
        ]);
    }

    public function editPriceCar(Request $req)
    {
        return $this->updatePriceRecord(
            $req,
            PriceCar::class,
            'priceCar',
            'Valores de carros atualizados com sucesso.'
        );
    }

    public function priceMotorcycle()
    {
        $priceMotorcycle = $this->ensurePriceRecord(PriceMotorcycle::class, [
            'valorHora' => 1,
            'valorMinimo' => 5,
            'valorDiaria' => 14,
            'taxaAdicional' => 8,
            'taxaMensal' => 100,
        ]);

        return view('priceMotorcycle', [
            'priceMotorcycle' => $priceMotorcycle,
            'route' => 'editPriceMotorcycle',
        ]);
    }

    public function editPriceMotorcycle(Request $req)
    {
        return $this->updatePriceRecord(
            $req,
            PriceMotorcycle::class,
            'priceMotorcycle',
            'Valores de motos atualizados com sucesso.'
        );
    }

    public function priceTruck()
    {
        $priceTruck = $this->ensurePriceRecord(PriceTruck::class, [
            'valorHora' => 5,
            'valorMinimo' => 15,
            'valorDiaria' => 60,
            'taxaAdicional' => 20,
            'taxaMensal' => 600,
        ]);

        return view('priceTruck', [
            'priceTruck' => $priceTruck,
            'route' => 'editPriceTruck',
        ]);
    }

    public function editPriceTruck(Request $req)
    {
        return $this->updatePriceRecord(
            $req,
            PriceTruck::class,
            'priceTruck',
            'Valores de caminhonetes atualizados com sucesso.'
        );
    }

    private function ensurePriceRecord(string $modelClass, array $defaults)
    {
        return $modelClass::query()->firstOrCreate([], $defaults);
    }

    private function updatePriceRecord(Request $request, string $modelClass, string $routeName, string $successMessage)
    {
        $data = $request->only([
            'valorHora',
            'valorMinimo',
            'valorDiaria',
            'taxaAdicional',
            'taxaMensal',
        ]);

        $validator = Validator::make($data, [
            'valorHora' => ['required', 'numeric', 'min:0'],
            'valorMinimo' => ['required', 'numeric', 'min:0'],
            'valorDiaria' => ['required', 'numeric', 'min:0'],
            'taxaAdicional' => ['required', 'numeric', 'min:0'],
            'taxaMensal' => ['required', 'numeric', 'min:0'],
        ]);

        if ($validator->fails()) {
            return redirect()->route($routeName)->withErrors($validator)->withInput();
        }

        $priceRecord = $this->ensurePriceRecord($modelClass, []);
        $priceRecord->fill($data);
        $priceRecord->save();

        return redirect()->route($routeName)->with('create', $successMessage);
    }
}
