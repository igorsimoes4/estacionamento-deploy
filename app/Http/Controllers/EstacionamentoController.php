<?php

namespace App\Http\Controllers;

use App\Models\Cars;
use App\Models\Estacionamento;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EstacionamentoController extends Controller
{
    public function index() {
        $data = [];

        $car_parking = Cars::where('tipo_car', 'carro')->count();
        $moto_parking = Cars::where('tipo_car', 'moto')->count();
        $caminhonete_parking = Cars::where('tipo_car', 'caminhonete')->count();

        $cars = Cars::paginate(10);

        // Monto as Informações para o grafico do Dashboard de 30 dias
        $interval = intval(120);
        $dateInterval = now()->subDays($interval)->toDateTimeString();
        $carPie = Cars::selectRaw('tipo_car, count(tipo_car) as c')
            ->where('created_at', '>=', $dateInterval)
            ->groupBy('tipo_car')
            ->pluck('c', 'tipo_car')
            ->toArray();

        $data['CarLabels'] = array_keys($carPie);
        $data['CarValues'] = array_values($carPie);

        // Monto as Informações para o grafico do Dashboard de um Ano
        $currentYear = date('Y');
        $startDateTimestamp = strtotime("$currentYear-01-01 00:00:00");
        $carPieYear = Cars::selectRaw('tipo_car, count(tipo_car) as c')
            ->where('created_at', '>=', $startDateTimestamp)
            ->groupBy('tipo_car')
            ->pluck('c', 'tipo_car')
            ->toArray();
        $data['CarLabelsYear'] = array_keys($carPieYear);
        $data['CarValuesYear'] = array_values($carPieYear);

        $data['cars'] = $cars;
        $data['car_parking'] = $car_parking;
        $data['moto_parking'] = $moto_parking;
        $data['caminhonete_parking'] = $caminhonete_parking;

        return view('home', compact('data'));

    }

}
