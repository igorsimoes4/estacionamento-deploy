<?php

namespace App\Http\Controllers;

use App\Models\Cars;
use App\Models\MonthlySubscriber;
use Carbon\Carbon;

class EstacionamentoController extends Controller
{
    private const TOTAL_SPOTS = [
        'carro' => 50,
        'moto' => 30,
        'caminhonete' => 20,
        'mensalistas' => 20,
    ];

    public function index()
    {
        $today = Carbon::today();
        $startOfMonth = Carbon::now()->startOfMonth();

        $data = [];

        $data['car_parking'] = Cars::parked()->where('tipo_car', 'carro')->count();
        $data['moto_parking'] = Cars::parked()->where('tipo_car', 'moto')->count();
        $data['caminhonete_parking'] = Cars::parked()->where('tipo_car', 'caminhonete')->count();

        $data['monthly_members'] = MonthlySubscriber::active()->count();
        $data['monthly_cars'] = MonthlySubscriber::active()->where('vehicle_type', 'carro')->count();
        $data['monthly_motos'] = MonthlySubscriber::active()->where('vehicle_type', 'moto')->count();
        $data['monthly_caminhonetes'] = MonthlySubscriber::active()->where('vehicle_type', 'caminhonete')->count();

        $data['cars'] = Cars::parked()
            ->latest('created_at')
            ->take(10)
            ->get();

        $last30Days = Carbon::now()->subDays(30);
        $carPie = [
            'carro' => Cars::where('tipo_car', 'carro')->where('created_at', '>=', $last30Days)->count(),
            'moto' => Cars::where('tipo_car', 'moto')->where('created_at', '>=', $last30Days)->count(),
            'caminhonete' => Cars::where('tipo_car', 'caminhonete')->where('created_at', '>=', $last30Days)->count(),
        ];

        $data['CarLabels'] = array_keys($carPie);
        $data['CarValues'] = array_values($carPie);

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

        $quarterStart = Carbon::now()->subMonths(3)->startOfMonth();
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

        $peakHours = [];
        $hours = range(0, 23);
        foreach ($hours as $hour) {
            $startTime = $today->copy()->addHours($hour);
            $endTime = $today->copy()->addHours($hour + 1);

            $peakHours[] = Cars::whereBetween('created_at', [$startTime, $endTime])->count();
        }

        $data['PeakHours'] = $peakHours;
        $data['HourLabels'] = array_map(static fn ($h) => sprintf('%02d:00', $h), $hours);

        $data['CarLabelsYear'] = ['carro', 'moto', 'caminhonete'];
        $data['CarValuesYear'] = [
            'carro' => $carData,
            'moto' => $motoData,
            'caminhonete' => $caminhoneteData,
        ];
        $data['QuarterLabels'] = $quarterLabels;
        $data['QuarterValues'] = [
            'carro' => $quarterCarData,
            'moto' => $quarterMotoData,
            'caminhonete' => $quarterCaminhoneteData,
        ];
        $data['MonthLabels'] = $months;

        $data['total_car_vagas'] = self::TOTAL_SPOTS['carro'];
        $data['total_moto_vagas'] = self::TOTAL_SPOTS['moto'];
        $data['total_caminhonete_vagas'] = self::TOTAL_SPOTS['caminhonete'];
        $data['total_mensalistas_vagas'] = self::TOTAL_SPOTS['mensalistas'];

        $data['car_entries_today'] = Cars::where('tipo_car', 'carro')->whereDate('created_at', $today)->count();
        $data['car_exits_today'] = Cars::finished()->where('tipo_car', 'carro')->whereDate('saida', $today)->count();
        $data['moto_entries_today'] = Cars::where('tipo_car', 'moto')->whereDate('created_at', $today)->count();
        $data['moto_exits_today'] = Cars::finished()->where('tipo_car', 'moto')->whereDate('saida', $today)->count();
        $data['caminhonete_entries_today'] = Cars::where('tipo_car', 'caminhonete')->whereDate('created_at', $today)->count();
        $data['caminhonete_exits_today'] = Cars::finished()->where('tipo_car', 'caminhonete')->whereDate('saida', $today)->count();

        $ticketsAvulsosMonth = (float) Cars::finished()->where('saida', '>=', $startOfMonth)->sum('preco');
        $mensalidadesMonth = (float) MonthlySubscriber::active()->sum('monthly_fee');
        $servicosAdicionais = 0.0;

        $data['revenue_total_month'] = $ticketsAvulsosMonth + $mensalidadesMonth + $servicosAdicionais;
        $data['revenue_avulsos_month'] = $ticketsAvulsosMonth;
        $data['revenue_mensalistas_month'] = $mensalidadesMonth;
        $data['revenue_services_month'] = $servicosAdicionais;

        if ($data['revenue_total_month'] > 0) {
            $totalRevenue = $data['revenue_total_month'];
            $data['revenue_avulsos_pct'] = (int) round(($ticketsAvulsosMonth / $totalRevenue) * 100);
            $data['revenue_mensalistas_pct'] = (int) round(($mensalidadesMonth / $totalRevenue) * 100);
            $data['revenue_services_pct'] = max(0, 100 - $data['revenue_avulsos_pct'] - $data['revenue_mensalistas_pct']);
        } else {
            $data['revenue_avulsos_pct'] = 0;
            $data['revenue_mensalistas_pct'] = 0;
            $data['revenue_services_pct'] = 0;
        }

        return view('home', compact('data'));
    }

    private function countVehiclesByTypeAndPeriod(string $type, Carbon $startDate, Carbon $endDate): int
    {
        return Cars::where('tipo_car', $type)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();
    }
}
