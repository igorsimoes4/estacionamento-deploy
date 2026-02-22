<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
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
        $periodMap = [
            '1d' => ['days' => 1, 'label' => 'Hoje'],
            '7d' => ['days' => 7, 'label' => 'Ultimos 7 dias'],
            '30d' => ['days' => 30, 'label' => 'Ultimos 30 dias'],
            '90d' => ['days' => 90, 'label' => 'Ultimos 90 dias'],
        ];
        $selectedPeriod = request()->query('period', '30d');
        if (!isset($periodMap[$selectedPeriod])) {
            $selectedPeriod = '30d';
        }

        $periodDays = $periodMap[$selectedPeriod]['days'];
        $periodLabel = $periodMap[$selectedPeriod]['label'];
        $periodStart = Carbon::now()->subDays($periodDays - 1)->startOfDay();
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

        $last30Days = $periodStart;
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

        $totalOperacionalVagas = self::TOTAL_SPOTS['carro'] + self::TOTAL_SPOTS['moto'] + self::TOTAL_SPOTS['caminhonete'];
        $ocupacaoAtual = $data['car_parking'] + $data['moto_parking'] + $data['caminhonete_parking'];

        $data['occupancy_by_type'] = [
            'carro' => [
                'used' => $data['car_parking'],
                'capacity' => self::TOTAL_SPOTS['carro'],
                'percent' => self::percent($data['car_parking'], self::TOTAL_SPOTS['carro']),
            ],
            'moto' => [
                'used' => $data['moto_parking'],
                'capacity' => self::TOTAL_SPOTS['moto'],
                'percent' => self::percent($data['moto_parking'], self::TOTAL_SPOTS['moto']),
            ],
            'caminhonete' => [
                'used' => $data['caminhonete_parking'],
                'capacity' => self::TOTAL_SPOTS['caminhonete'],
                'percent' => self::percent($data['caminhonete_parking'], self::TOTAL_SPOTS['caminhonete']),
            ],
        ];

        $data['occupancy_total_percent'] = self::percent($ocupacaoAtual, $totalOperacionalVagas);
        $data['occupancy_total_used'] = $ocupacaoAtual;
        $data['occupancy_total_capacity'] = $totalOperacionalVagas;

        $data['expiring_subscribers_count'] = MonthlySubscriber::query()
            ->whereDate('end_date', '>=', $today)
            ->whereDate('end_date', '<=', $today->copy()->addDays(7))
            ->count();

        $data['overdue_subscribers_count'] = MonthlySubscriber::query()
            ->whereDate('end_date', '<', $today)
            ->count();

        $data['expiring_subscribers'] = MonthlySubscriber::query()
            ->whereDate('end_date', '>=', $today)
            ->whereDate('end_date', '<=', $today->copy()->addDays(10))
            ->orderBy('end_date')
            ->limit(6)
            ->get(['id', 'name', 'vehicle_plate', 'end_date', 'monthly_fee']);

        $data['recent_activity'] = ActivityLog::query()
            ->where('created_at', '>=', $periodStart)
            ->orderByDesc('id')
            ->limit(8)
            ->get(['event', 'description', 'request_path', 'created_at', 'level']);

        $data['period_options'] = [
            '1d' => 'Hoje',
            '7d' => '7 dias',
            '30d' => '30 dias',
            '90d' => '90 dias',
        ];
        $data['selected_period'] = $selectedPeriod;
        $data['period_label'] = $periodLabel;
        $data['period_entries'] = Cars::query()
            ->where('created_at', '>=', $periodStart)
            ->count();
        $data['period_exits'] = Cars::finished()
            ->where('saida', '>=', $periodStart)
            ->count();
        $data['period_revenue'] = (float) Cars::finished()
            ->where('saida', '>=', $periodStart)
            ->sum('preco');
        $data['period_ticket_avg'] = (float) Cars::finished()
            ->where('saida', '>=', $periodStart)
            ->avg('preco');

        return view('home', compact('data'));
    }

    private static function percent(int|float $used, int|float $capacity): int
    {
        if ($capacity <= 0) {
            return 0;
        }

        return (int) max(0, min(100, round(($used / $capacity) * 100)));
    }

    private function countVehiclesByTypeAndPeriod(string $type, Carbon $startDate, Carbon $endDate): int
    {
        return Cars::where('tipo_car', $type)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();
    }
}
