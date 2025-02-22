<?php

namespace App\Http\Controllers;

use App\Models\Cars;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EstacionamentoController extends Controller
{
    public function index()
    {
        $data = [];

        $car_parking = Cars::where('tipo_car', 'carro')->count();
        $moto_parking = Cars::where('tipo_car', 'moto')->count();
        $caminhonete_parking = Cars::where('tipo_car', 'caminhonete')->count();

        $cars = Cars::paginate(10);

        // Monto as Informações para o grafico do Dashboard de 30 dias
        $interval = intval(30);
        $dateInterval = now()->subDays($interval)->toDateTimeString();
        $carPie = Cars::selectRaw('tipo_car, count(tipo_car) as c')
            ->where('created_at', '>=', $dateInterval)
            ->groupBy('tipo_car')
            ->pluck('c', 'tipo_car')
            ->toArray();

        $data['CarLabels'] = array_keys($carPie);
        $data['CarValues'] = array_values($carPie);

        // Obter o ano atual
        $currentYear = date('Y');

        // Definir o timestamp para o início do ano atual
        $startDateTimestamp = strtotime("$currentYear-01-01 00:00:00");

        // Consultar para obter a contagem de cada tipo de carro adicionado por mês no ano atual
        $carPieMonthYear = Cars::selectRaw('MONTH(created_at) as month, tipo_car, count(tipo_car) as c')
            ->where('created_at', '>=', $startDateTimestamp) // Filtrar registros desde o início do ano atual
            ->groupBy('month', 'tipo_car') // Agrupar por mês e tipo de carro
            ->orderBy('month') // Ordenar por mês
            ->get(); // Obter os resultados

        // Inicializar arrays para armazenar os dados do gráfico
        $data['CarLabelsYear'] = [];
        $data['CarValuesYear'] = [];

        // Preparar os dados para o gráfico
        foreach ($carPieMonthYear as $entry) {
            $month = $entry->month;
            $tipo_car = $entry->tipo_car;
            $count = $entry->c;

            // Inicializar o array do mês se ainda não existir
            if (!isset($data['CarValuesYear'][$month])) {
                $data['CarValuesYear'][$month] = [];
            }

            // Armazenar a contagem no array do mês e tipo de carro
            $data['CarValuesYear'][$month][$tipo_car] = $count;

            // Adicionar o tipo de carro à lista de rótulos se ainda não estiver nela
            if (!in_array($tipo_car, $data['CarLabelsYear'])) {
                $data['CarLabelsYear'][] = $tipo_car;
            }
        }

        // Ordenar os rótulos dos tipos de carros para consistência
        sort($data['CarLabelsYear']);

        // Inicializar array de valores formatados para o gráfico
        $formattedValues = [];

        // Garantir que todos os tipos de carros estejam presentes em cada mês com valor zero se necessário
        for ($month = 1; $month <= 12; $month++) {
            $formattedValues[$month] = [];
            foreach ($data['CarLabelsYear'] as $label) {
                $formattedValues[$month][] = $data['CarValuesYear'][$month][$label] ?? 0;
            }
        }

        // Armazenar os valores formatados no array de dados
        $data['CarValuesYear'] = $formattedValues;

        // Agora, $data['CarLabels'] contém os tipos de carros e $data['CarValues'] contém os valores para cada mês


        $data['cars'] = $cars;
        $data['car_parking'] = $car_parking;
        $data['moto_parking'] = $moto_parking;
        $data['caminhonete_parking'] = $caminhonete_parking;

        // Definir número total de vagas por tipo de veículo
        $total_car_vagas = 50; // Exemplo de número total de vagas para carros
        $total_moto_vagas = 30; // Exemplo de número total de vagas para motos
        $total_caminhonete_vagas = 20; // Exemplo de número total de vagas para caminhonetes

        // Contagem de mensalistas
        // $monthly_members = DB::table('mensalistas')->count();
        $monthly_members = 10; // Exemplo de número total de mensalistas

        $data['total_car_vagas'] = $total_car_vagas;
        $data['total_moto_vagas'] = $total_moto_vagas;
        $data['total_caminhonete_vagas'] = $total_caminhonete_vagas;
        $data['monthly_members'] = $monthly_members;

        return view('home', compact('data'));
    }
}
