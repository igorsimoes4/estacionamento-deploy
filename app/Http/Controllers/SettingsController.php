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
    public function index() {
        $estacionamento = Settings::find(1);
        $data['estacionamento'] = $estacionamento;
        $data['route'] = 'editSettings';
        return view('settings', $data);
    }

    public function editSettings(Request $req) {
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
            'nomeDaEmpresa'                 => ['required', 'string'],
            'endereco'                        => ['required', 'string'],
            'cidade'                          => ['required', 'string'],
            'estado'                          => ['required', 'string'],
            'cep'                             => ['required', 'string'],
            'telefone_da_empresa'             => ['required', 'string'],
            'email_da_empresa'                => ['required', 'email'],
            'numero_de_registro_da_Empresa'   => ['required', 'string'],
            'cnpj_Cpf_da_empresa'             => ['required', 'string'],
            'descricao_da_empresa'            => ['nullable', 'string'],
            'coordenadas_gps'                 => ['nullable', 'string']
        ]);


        if($validator->fails()) {
            return redirect(route('settings'))->withErrors($validator)->withInput();
        }

        $estacionamento = Settings::find(1);
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
        $estacionamento->update();

        return redirect(route('settings'))->with('create', 'Valores editados com sucesso');
    }

    public function priceCar() {
        $priceCar = PriceCar::find(1);
        $data['priceCar'] = $priceCar;
        $data['route'] = 'editPriceCar';
        return view('priceCar', $data);
    }

    public function editPriceCar(Request $req) {
            $data = $req->only([
                'valorHora',
                'valorMinimo',
                'valorDiaria',
                'taxaAdicional',
                'taxaMensal'
            ]);

            $validator = Validator::make($data, [
                'valorHora'     => [ 'required' ],
                'valorMinimo'   => [ 'required' ],
                'valorDiaria'   => [ 'required' ],
                'taxaAdicional' => [ 'required' ],
                'taxaMensal'    => [ 'required' ]
            ]);

            if($validator->fails()) {
                return redirect(route('priceCar'))->withErrors($validator)->withInput();
            }

            $priceCar = PriceCar::find(1);
            $priceCar->valorHora     =  $data['valorHora'];
            $priceCar->valorMinimo   =  $data['valorMinimo'];
            $priceCar->valorDiaria   =  $data['valorDiaria'];
            $priceCar->taxaAdicional =  $data['taxaAdicional'];
            $priceCar->taxaMensal    =  $data['taxaMensal'];
            $priceCar->update();

            return redirect(route('priceCar'))->with('create', 'Valores editados com sucesso');

    }

    public function priceMotorcycle() {
        $priceMotorcycle = PriceMotorcycle::find(1);
        $data['priceMotorcycle'] = $priceMotorcycle;
        $data['route'] = 'editPriceMotorcycle';
        return view('priceMotorcycle', $data);
    }

    public function editPriceMotorcycle(Request $req) {
        $data = $req->only([
            'valorHora',
            'valorMinimo',
            'valorDiaria',
            'taxaAdicional',
            'taxaMensal'
        ]);

        $validator = Validator::make($data, [
            'valorHora'     => [ 'required' ],
            'valorMinimo'   => [ 'required' ],
            'valorDiaria'   => [ 'required' ],
            'taxaAdicional' => [ 'required' ],
            'taxaMensal'    => [ 'required' ]
        ]);

        if($validator->fails()) {
            return redirect(route('priceMotorcycle'))->withErrors($validator)->withInput();
        }

        $priceMotorcycle = PriceMotorcycle::find(1);
        $priceMotorcycle->valorHora     =  $data['valorHora'];
        $priceMotorcycle->valorMinimo   =  $data['valorMinimo'];
        $priceMotorcycle->valorDiaria   =  $data['valorDiaria'];
        $priceMotorcycle->taxaAdicional =  $data['taxaAdicional'];
        $priceMotorcycle->taxaMensal    =  $data['taxaMensal'];
        $priceMotorcycle->update();

        return redirect(route('priceMotorcycle'))->with('create', 'Valores editados com sucesso');
    }

    public function priceTruck() {
        $PriceTruck = PriceTruck::find(1);
        $data['priceTruck'] = $PriceTruck;
        $data['route'] = 'editPriceTruck';
        return view('priceTruck', $data);
    }

    public function editPriceTruck(Request $req) {
        $data = $req->only([
            'valorHora',
            'valorMinimo',
            'valorDiaria',
            'taxaAdicional',
            'taxaMensal'
        ]);

        $validator = Validator::make($data, [
            'valorHora'     => [ 'required' ],
            'valorMinimo'   => [ 'required' ],
            'valorDiaria'   => [ 'required' ],
            'taxaAdicional' => [ 'required' ],
            'taxaMensal'    => [ 'required' ]
        ]);

        if($validator->fails()) {
            return redirect(route('priceTruck'))->withErrors($validator)->withInput();
        }

        $PriceTruck = PriceTruck::find(1);
        $PriceTruck->valorHora     =  $data['valorHora'];
        $PriceTruck->valorMinimo   =  $data['valorMinimo'];
        $PriceTruck->valorDiaria   =  $data['valorDiaria'];
        $PriceTruck->taxaAdicional =  $data['taxaAdicional'];
        $PriceTruck->taxaMensal    =  $data['taxaMensal'];
        $PriceTruck->update();

        return redirect(route('priceTruck'))->with('create', 'Valores editados com sucesso');
    }
}
