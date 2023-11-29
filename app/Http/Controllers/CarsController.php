<?php

namespace App\Http\Controllers;

use App\Models\Cars;
use App\Models\PriceCar;
use App\Models\PriceMotorcycle;
use App\Models\PriceTruck;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CarsController extends Controller
{
    public function price($id) {
        $valor = 0;

        $car = Cars::find($id);

        date_default_timezone_set('America/Sao_Paulo');
        $saida = new DateTime();
        $entrada = new DateTime($car->created_at);
        $tempo = date_diff($entrada, $saida);

        $hora = $tempo->h;
        $minuto = $tempo->i;
        $dia = $tempo->d;
        $mes = $tempo->m;

        switch ($car->tipo_car) {
            case 'carro':
                $priceCar = PriceCar::find(1);
                $valorHora = number_format($priceCar->valorHora, 0, ',', '.');
                $valorMinimo = number_format($priceCar->valorMinimo, 0, ',', '.');
                $valorDiaria = number_format($priceCar->valorDiaria, 0, ',', '.');
                $taxaAdicional = number_format($priceCar->taxaAdicional, 0, ',', '.');
                $taxaMensal = number_format($priceCar->taxaMensal, 0, ',', '.');
                break;
            case 'moto':
                $priceMotorcycle = PriceMotorcycle::find(1);
                $valorHora = number_format($priceMotorcycle->valorHora, 0, ',', '.');
                $valorMinimo = number_format($priceMotorcycle->valorMinimo, 0, ',', '.');
                $valorDiaria = number_format($priceMotorcycle->valorDiaria, 0, ',', '.');
                $taxaAdicional = number_format($priceMotorcycle->taxaAdicional, 0, ',', '.');
                $taxaMensal = number_format($priceMotorcycle->taxaMensal, 0, ',', '.');
                break;
            case 'caminhonete':
                $priceTruck = PriceTruck::find(1);
                $valorHora = number_format($priceTruck->valorHora, 0, ',', '.');
                $valorMinimo = number_format($priceTruck->valorMinimo, 0, ',', '.');
                $valorDiaria = number_format($priceTruck->valorDiaria, 0, ',', '.');
                $taxaAdicional = number_format($priceTruck->taxaAdicional, 0, ',', '.');
                $taxaMensal = number_format($priceTruck->taxaMensal, 0, ',', '.');
                break;
        }

        if($mes >= 1) {
            if ($dia >= 1) {
                if ($hora >= 1) {
                    if($hora > 1) {
                        $valor = (($hora - 1) * $valorHora) + ($valorDiaria * $dia) + $taxaAdicional + ($taxaMensal * $mes);
                    } else {
                        $valor = $valorHora + ($valorDiaria * $dia) + $taxaAdicional + ($taxaMensal * $mes);
                    }
                } else  {
                    if($hora < 1 && $minuto <= 30) {
                        $valor = ($valorDiaria * $dia) + $valorMinimo + ($taxaMensal * $mes);
                    } else {
                        $valor = ($minuto - 1) * $valorHora + ($valorDiaria * $dia) + $taxaAdicional + ($taxaMensal * $mes);
                    }
                }
            } else {
                if ($hora >= 1) {
                    if($hora > 1) {
                        $valor = (($hora - 1) * $valorHora) + $valorDiaria + $taxaAdicional + ($taxaMensal * $mes);
                    } else {
                        $valor = $valorHora + $valorDiaria + $taxaAdicional + ($taxaMensal * $mes);
                    }
                } elseif ($hora < 1 && $minuto <= 30) {
                    $valor = $valorMinimo + ($taxaMensal * $mes);
                } elseif ($hora < 1 && $minuto >= 31 && $minuto <= 60) {
                    $valor = ($minuto - 1) * $valorHora + $taxaAdicional + ($taxaMensal * $mes);
                }
            }
        } else {
            if ($dia >= 1) {
                if ($hora >= 1) {
                    if($hora > 1) {
                        $valor = (($hora - 1) * $valorHora) + ($valorDiaria * $dia) + $taxaAdicional;
                    } else {
                        $valor = $valorHora + ($valorDiaria * $dia) + $taxaAdicional;
                    }
                } else {
                    if($hora < 1 && $minuto <= 30) {
                        $valor = ($valorDiaria * $dia) + $valorMinimo;
                    } else {
                        $valor = ($minuto - 1) * $valorHora + ($valorDiaria * $dia) + $taxaAdicional;
                    }
                }
            } else {
                if ($hora >= 1) {
                    if($hora > 1) {
                        $valor = (($hora - 1) * $valorHora) + $taxaAdicional;
                    } else {
                        $valor = $valorHora + $taxaAdicional;
                    }
                } elseif ($hora < 1 && $minuto <= 30) {
                    $valor = $valorMinimo;
                } elseif ($hora < 1 && $minuto >= 31 && $minuto <= 60) {
                    $valor = ($minuto - 1) * $valorHora + $taxaAdicional;
                }
            }
        }
        return $valor;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = [];
        $cars = Cars::paginate(6);

        foreach ($cars as $car) {
            $car['price'] = $this->price($car->id);
        }

        $data['cars'] = $cars;
        // return view('cars', $data);
        return response()->json($data);
    }

    public function search(Request $req) {

        $data = $req->only([
            'search'
        ]);

        if(empty($data['search'])) {
            return redirect(route('cars.index'))->withInput();
        }

        $validator = Validator::make($data, [
            'search' => ['string', 'max:8']
        ]);

        if($validator->fails()) {
            return redirect(route('cars.index'))->withErrors($validator)->withInput();
        }
        // Obter a consulta digitada pelo usuário
        $search = $data['search'];

        // Realizar a lógica de pesquisa no seu modelo ou na fonte de dados desejada
        // Suponhamos que você tenha um modelo chamado "Veiculo" para pesquisa
        $cars = Cars::where('placa', 'LIKE', '%' . $search . '%')->paginate(6);

        foreach ($cars as $car) {
            $car['price'] = $this->price($car->id);
        }

        $data['cars'] = $cars;

        session()->flash('create', "Carros Localizados com sucesso contendo $search na Placa");

        return view('cars', $data)->with('create', "Carros Localizados contendo $search na Placa");
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('cars_add');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = $request->only([
            'modelo',
            'placa',
            'entrada',
            'tipo_car'
        ]);

        $validator = Validator::make($data, [
            'modelo' => ['required', 'string', 'max:64'],
            'placa' => ['required', 'string', 'max:8'],
            'entrada' => ['required'],
            'tipo_car' => ['required', 'string', Rule::in(['carro', 'moto', 'caminhonete'])]
        ]);

        if($validator->fails()) {
            return redirect(route('cars.create'))->withErrors($validator)->withInput();
        }

        $car = new Cars();
        $car->modelo = $data['modelo'];
        $car->placa = $data['placa'];
        $car->entrada = $data['entrada'];
        $car->tipo_car = $data['tipo_car'];
        $car->preco = 0;
        $car->save();

        return redirect(route('cars.index'))->with('create', 'Carro adicionado com sucesso');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $car = Cars::find($id);

        $car['price'] = $this->price($car->id);
        $car['entrada'] = new DateTime($car->created_at);

        if($car) {
            return response()->json(['success' => true, 'html' => view('modal_cars_edit', ['car' => $car])->render()]);
        }

        return redirect(route('cars.index'));
    }

    public function showModal($id) {
        $car = Cars::find($id);

        date_default_timezone_set('America/Sao_Paulo');
        $saida = new DateTime();
        $entrada = new DateTime($car->created_at);
        $tempo = date_diff($entrada, $saida);

        $hora = $tempo->h;
        $minuto = $tempo->i;
        $dia = $tempo->d;
        $mes = $tempo->m;

        $car['price'] = $this->price($id);
        $car['entrada'] = $car->created_at->format('d/m/y \à\s H \h\o\r\a i \m\i\n\u\t\o\s');
        $car['horaT'] = $hora;
        $car['minutoT'] = $minuto;
        $car['diaT'] = $dia;
        $car['mesT'] = $mes;

        return response()->json($car);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $car = Cars::find($id);

        if($car) {
            $car['preco2'] = number_format($car['preco'], 2, ',', '.');
            $car['price'] = $this->price($car->id);
            return view('cars_edit', ['car' => $car]);
        }

        return redirect(route('cars.index'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }




}
