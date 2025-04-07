<?php

namespace App\Http\Controllers;

use App\Models\Cars;
use App\Models\MonthlySubscriber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class EstacionamentoController extends Controller
{
    public function index()
    {
        $data = [];

        // Contagem atual de veículos estacionados
        $data['car_parking'] = Cars::where('tipo_car', 'carro')->whereNull('status')->whereNull('saida')->count();
        $data['moto_parking'] = Cars::where('tipo_car', 'moto')->whereNull('status')->whereNull('saida')->count();
        $data['caminhonete_parking'] = Cars::where('tipo_car', 'caminhonete')->whereNull('status')->whereNull('saida')->count();

        // Contagem de mensalistas ativos
        $data['monthly_members'] = MonthlySubscriber::where('is_active', true)
            ->count();

        // Contagem de mensalistas por tipo de veículo
        $data['monthly_cars'] = Cars::where('tipo_car', 'carro')
            ->whereNull('status')
            ->whereNull('saida')
            ->count();

        $data['monthly_motos'] = Cars::where('tipo_car', 'moto')
            ->whereNull('status')
            ->whereNull('saida')
            ->count();

        $data['monthly_caminhonetes'] = Cars::where('tipo_car', 'caminhonete')
            ->whereNull('status')
            ->whereNull('saida')
            ->count();

        // Últimos 10 registros de carros ativos
        $data['cars'] = Cars::whereNull('status')
            ->whereNull('saida')
            ->latest()
            ->take(10)
            ->get();

        // Gráfico para os últimos 30 dias
        $last30Days = Carbon::now()->subDays(30);
        $carPie = [
            'carro' => Cars::where('tipo_car', 'carro')
                ->where('created_at', '>=', $last30Days)
                ->whereNull('status')
                ->whereNull('saida')
                ->count(),
            'moto' => Cars::where('tipo_car', 'moto')
                ->where('created_at', '>=', $last30Days)
                ->whereNull('status')
                ->whereNull('saida')
                ->count(),
            'caminhonete' => Cars::where('tipo_car', 'caminhonete')
                ->where('created_at', '>=', $last30Days)
                ->whereNull('status')
                ->whereNull('saida')
                ->count()
        ];

        $data['CarLabels'] = array_keys($carPie);
        $data['CarValues'] = array_values($carPie);

        // Dados para o gráfico anual
        $currentYear = Carbon::now()->year;
        $months = [];
        $carData = [];
        $motoData = [];
        $caminhoneteData = [];

        for ($month = 1; $month <= 12; $month++) {
            $start = Carbon::create($currentYear, $month)->startOfMonth();
            $end = Carbon::create($currentYear, $month)->endOfMonth();
            $months[] = $start->format('M');

            $carData[] = $this->countVehiclesByTypeAndPeriod('carro', $start, $end);
            $motoData[] = $this->countVehiclesByTypeAndPeriod('moto', $start, $end);
            $caminhoneteData[] = $this->countVehiclesByTypeAndPeriod('caminhonete', $start, $end);
        }

        // Dados para o último trimestre
        $now = Carbon::now();
        $quarterStart = $now->copy()->subMonths(3)->startOfMonth();
        $quarterLabels = [];
        $quarterCarData = [];
        $quarterMotoData = [];
        $quarterCaminhoneteData = [];

        for ($i = 0; $i < 3; $i++) {
            $start = $quarterStart->copy()->addMonths($i)->startOfMonth();
            $end = $start->copy()->endOfMonth();
            $quarterLabels[] = $start->format('M/Y');

            $quarterCarData[] = $this->countVehiclesByTypeAndPeriod('carro', $start, $end);
            $quarterMotoData[] = $this->countVehiclesByTypeAndPeriod('moto', $start, $end);
            $quarterCaminhoneteData[] = $this->countVehiclesByTypeAndPeriod('caminhonete', $start, $end);
        }

        // Gráfico de horários de pico
        $peakHours = [];
        $hours = range(0, 23);
        foreach ($hours as $hour) {
            $startTime = Carbon::now()->startOfDay()->addHours($hour);
            $endTime = Carbon::now()->startOfDay()->addHours($hour + 1);
            $peakHours[] = Cars::whereBetween('created_at', [$startTime, $endTime])
                ->whereNull('status')
                ->whereNull('saida')
                ->count();
        }

        $data['PeakHours'] = $peakHours;
        $data['HourLabels'] = array_map(fn($h) => sprintf('%02d:00', $h), $hours);

        // Preparar dados para o gráfico
        $data['CarLabelsYear'] = ['carro', 'moto', 'caminhonete'];
        $data['CarValuesYear'] = [
            'carro' => $carData,
            'moto' => $motoData,
            'caminhonete' => $caminhoneteData
        ];
        $data['QuarterLabels'] = $quarterLabels;
        $data['QuarterValues'] = [
            'carro' => $quarterCarData,
            'moto' => $quarterMotoData,
            'caminhonete' => $quarterCaminhoneteData
        ];
        $data['MonthLabels'] = $months;

        // Total de vagas por tipo de veículo
        $data['total_car_vagas'] = 50;
        $data['total_moto_vagas'] = 30;
        $data['total_caminhonete_vagas'] = 20;

        // Total de vagas para mensalistas
        $data['total_mensalistas_vagas'] = 20;

        return view('home', compact('data'));
    }

    private function countVehiclesByTypeAndPeriod($type, $startDate, $endDate)
    {
        return Cars::where('tipo_car', $type)
            ->where(function ($query) use ($startDate, $endDate) {
                $query->where(function ($q) use ($startDate, $endDate) {
                    $q->whereBetween('created_at', [$startDate, $endDate])
                      ->whereNull('saida');
                })
                ->orWhere(function ($q) use ($startDate, $endDate) {
                    $q->whereBetween('created_at', [$startDate, $endDate])
                      ->whereBetween('saida', [$startDate, $endDate]);
                });
            })
            ->whereNull('status')
            ->count();
    }
}
