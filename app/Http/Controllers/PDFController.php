<?php

namespace App\Http\Controllers;

use App\Models\Cars;
use App\Models\MonthlySubscriber;
use App\Models\Settings;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class PDFController extends Controller
{
    private function parkingSettings(): Settings
    {
        return Settings::firstOrCreate(['id' => 1], []);
    }

    private function generateCarsPDF(Collection $cars, string $reportTitle, string $orientation = 'portrait')
    {
        $pdf = PDF::loadView('Reports.layouts.PDF.A4', [
            'cars' => $cars,
            'estacionamento' => $this->parkingSettings(),
            'reportTitle' => $reportTitle,
        ])->setPaper('A4', $orientation);

        return $pdf->stream(Str::slug($reportTitle) . '.pdf');
    }

    private function generateTablePDF(Collection $rows, array $columns, string $reportTitle, string $orientation = 'portrait')
    {
        $pdf = PDF::loadView('Reports.layouts.PDF.Table', [
            'rows' => $rows,
            'columns' => $columns,
            'estacionamento' => $this->parkingSettings(),
            'reportTitle' => $reportTitle,
        ])->setPaper('A4', $orientation);

        return $pdf->stream(Str::slug($reportTitle) . '.pdf');
    }

    private function csvResponse(string $filename, array $headers, Collection $rows)
    {
        return response()->streamDownload(function () use ($headers, $rows) {
            $output = fopen('php://output', 'w');

            fputcsv($output, $headers, ';');

            foreach ($rows as $row) {
                fputcsv($output, $row, ';');
            }

            fclose($output);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function last30DaysCarsByType(string $type): Collection
    {
        return Cars::where('tipo_car', $type)
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->orderByDesc('created_at')
            ->get();
    }

    private function vehicleTypeLabel(?string $type): string
    {
        return match ($type) {
            'carro' => 'Carro',
            'moto' => 'Moto',
            'caminhao', 'caminhonete' => 'Caminhonete',
            default => 'Nao informado',
        };
    }

    private function formatCurrency(float $value): string
    {
        return 'R$ ' . number_format($value, 2, ',', '.');
    }

    private function paymentMethodLabel(?string $method): string
    {
        return Cars::paymentMethodLabel($method);
    }

    public function generatePDFCars()
    {
        $cars = $this->last30DaysCarsByType('carro');
        return $this->generateCarsPDF($cars, 'Relatorio de Carros');
    }

    public function generateExcelCars()
    {
        $cars = $this->last30DaysCarsByType('carro');

        return $this->csvResponse('relatorio-carros.csv', ['Modelo', 'Placa', 'Entrada', 'Status', 'Preco'], $cars->map(function ($car) {
            return [
                $car->modelo,
                $car->placa,
                optional($car->created_at)->format('d/m/Y H:i:s'),
                $car->status ?? 'ativo',
                number_format((float) $car->preco, 2, ',', '.'),
            ];
        }));
    }

    public function generatePDFMotorcycle()
    {
        $cars = $this->last30DaysCarsByType('moto');
        return $this->generateCarsPDF($cars, 'Relatorio de Motos');
    }

    public function generateExcelMotorcycle()
    {
        $cars = $this->last30DaysCarsByType('moto');

        return $this->csvResponse('relatorio-motos.csv', ['Modelo', 'Placa', 'Entrada', 'Status', 'Preco'], $cars->map(function ($car) {
            return [
                $car->modelo,
                $car->placa,
                optional($car->created_at)->format('d/m/Y H:i:s'),
                $car->status ?? 'ativo',
                number_format((float) $car->preco, 2, ',', '.'),
            ];
        }));
    }

    public function generatePDFTruck()
    {
        $cars = $this->last30DaysCarsByType('caminhonete');
        return $this->generateCarsPDF($cars, 'Relatorio de Caminhonetes');
    }

    public function generateExcelTruck()
    {
        $cars = $this->last30DaysCarsByType('caminhonete');

        return $this->csvResponse('relatorio-caminhonetes.csv', ['Modelo', 'Placa', 'Entrada', 'Status', 'Preco'], $cars->map(function ($car) {
            return [
                $car->modelo,
                $car->placa,
                optional($car->created_at)->format('d/m/Y H:i:s'),
                $car->status ?? 'ativo',
                number_format((float) $car->preco, 2, ',', '.'),
            ];
        }));
    }

    public function generatePDFFinishedVehicles()
    {
        $cars = Cars::finished()->orderByDesc('saida')->get();
        return $this->generateCarsPDF($cars, 'Relatorio de Veiculos Finalizados', 'landscape');
    }

    public function generatePDFClientVehicles($client_id)
    {
        $query = Cars::query();

        if (Schema::hasColumn('cars', 'client_id')) {
            $query->where('client_id', $client_id);
        } else {
            $query->where('id', $client_id);
        }

        $cars = $query->get();

        return $this->generateCarsPDF($cars, 'Relatorio de Veiculos por Cliente');
    }

    public function generatePDFEntryExit()
    {
        $cars = Cars::where('created_at', '>=', Carbon::now()->subDays(30))
            ->orderByDesc('created_at')
            ->get();

        return $this->generateCarsPDF($cars, 'Relatorio de Entrada e Saida', 'landscape');
    }

    public function generatePDFFinancial()
    {
        $cars = Cars::finished()
            ->where('saida', '>=', Carbon::now()->subDays(30))
            ->orderByDesc('saida')
            ->get();

        return $this->generateCarsPDF($cars, 'Relatorio Financeiro', 'landscape');
    }

    public function generatePDFActiveSubscribers()
    {
        $rows = MonthlySubscriber::active()
            ->orderBy('name')
            ->get()
            ->map(function (MonthlySubscriber $subscriber) {
                return (object) [
                    'modelo' => $subscriber->vehicle_model ?: $subscriber->name,
                    'placa' => $subscriber->vehicle_plate,
                    'created_at' => Carbon::parse($subscriber->start_date),
                    'status' => null,
                    'preco' => $subscriber->monthly_fee,
                    'updated_at' => Carbon::parse($subscriber->end_date),
                ];
            });

        return $this->generateCarsPDF($rows, 'Relatorio de Mensalistas Ativos');
    }

    public function generatePDFParkingOccupancy()
    {
        $occupancy = Cars::parked()->count();

        $pdf = PDF::loadView('Reports.layouts.PDF.Occupancy', [
            'occupancy' => $occupancy,
            'estacionamento' => $this->parkingSettings(),
            'reportTitle' => 'Relatorio de Ocupacao do Estacionamento',
        ])->setPaper('A4', 'portrait');

        return $pdf->stream('relatorio-ocupacao.pdf');
    }

    public function generatePDFCurrentlyParked()
    {
        $cars = Cars::parked()->orderByDesc('created_at')->get();
        return $this->generateCarsPDF($cars, 'Relatorio de Veiculos Estacionados no Momento');
    }

    public function generatePDFViolations()
    {
        $cars = Cars::parked()
            ->where('created_at', '<=', Carbon::now()->subHours(24))
            ->orderBy('created_at')
            ->get();

        return $this->generateCarsPDF($cars, 'Relatorio de Permanencia Acima de 24h');
    }

    public function generatePDFFinishedVehiclesToday()
    {
        $cars = Cars::finished()
            ->whereDate('saida', Carbon::today())
            ->orderByDesc('saida')
            ->get();

        return $this->generateCarsPDF($cars, 'Relatorio de Veiculos Finalizados Hoje', 'landscape');
    }

    public function generateExcelFinishedVehiclesToday()
    {
        $cars = Cars::finished()
            ->whereDate('saida', Carbon::today())
            ->orderByDesc('saida')
            ->get();

        return $this->csvResponse('relatorio-veiculos-finalizados-hoje.csv', ['Modelo', 'Placa', 'Entrada', 'Saida', 'Pagamento', 'Gateway', 'Preco'], $cars->map(function ($car) {
            return [
                $car->modelo,
                $car->placa,
                optional($car->created_at)->format('d/m/Y H:i:s'),
                optional($car->saida)->format('d/m/Y H:i:s'),
                $this->paymentMethodLabel($car->payment_method),
                Cars::paymentProviderLabel($car->payment_provider),
                number_format((float) $car->preco, 2, ',', '.'),
            ];
        }));
    }

    public function generatePDFTopRevenueVehicles()
    {
        $cars = Cars::finished()
            ->where('saida', '>=', Carbon::now()->subDays(30))
            ->orderByDesc('preco')
            ->orderByDesc('saida')
            ->limit(20)
            ->get();

        return $this->generateCarsPDF($cars, 'Relatorio de Top 20 Veiculos por Faturamento (30 dias)', 'landscape');
    }

    public function generateExcelTopRevenueVehicles()
    {
        $cars = Cars::finished()
            ->where('saida', '>=', Carbon::now()->subDays(30))
            ->orderByDesc('preco')
            ->orderByDesc('saida')
            ->limit(20)
            ->get();

        return $this->csvResponse('relatorio-top-faturamento-veiculos.csv', ['Modelo', 'Placa', 'Entrada', 'Saida', 'Pagamento', 'Gateway', 'Preco'], $cars->map(function ($car) {
            return [
                $car->modelo,
                $car->placa,
                optional($car->created_at)->format('d/m/Y H:i:s'),
                optional($car->saida)->format('d/m/Y H:i:s'),
                $this->paymentMethodLabel($car->payment_method),
                Cars::paymentProviderLabel($car->payment_provider),
                number_format((float) $car->preco, 2, ',', '.'),
            ];
        }));
    }

    private function paymentMethodRows(int $days = 30): Collection
    {
        return Cars::finished()
            ->where('saida', '>=', Carbon::now()->subDays($days))
            ->selectRaw("COALESCE(NULLIF(payment_method, ''), 'nao_informado') as payment_method_key")
            ->selectRaw('COUNT(*) as total_transacoes')
            ->selectRaw('SUM(preco) as total_valor')
            ->selectRaw('AVG(preco) as ticket_medio')
            ->groupBy('payment_method_key')
            ->orderByDesc('total_valor')
            ->get()
            ->map(function ($item) {
                return [
                    'metodo' => $this->paymentMethodLabel($item->payment_method_key === 'nao_informado' ? null : $item->payment_method_key),
                    'transacoes' => (int) $item->total_transacoes,
                    'valor_total' => $this->formatCurrency((float) $item->total_valor),
                    'ticket_medio' => $this->formatCurrency((float) $item->ticket_medio),
                ];
            });
    }

    public function generatePDFPaymentMethods()
    {
        $rows = $this->paymentMethodRows(30);

        return $this->generateTablePDF($rows, [
            ['key' => 'metodo', 'label' => 'Metodo de Pagamento'],
            ['key' => 'transacoes', 'label' => 'Transacoes'],
            ['key' => 'valor_total', 'label' => 'Valor Total'],
            ['key' => 'ticket_medio', 'label' => 'Ticket Medio'],
        ], 'Relatorio de Pagamentos por Metodo (30 dias)', 'landscape');
    }

    public function generateExcelPaymentMethods()
    {
        $rows = $this->paymentMethodRows(30)->map(function ($row) {
            return [
                $row['metodo'],
                $row['transacoes'],
                str_replace('R$ ', '', $row['valor_total']),
                str_replace('R$ ', '', $row['ticket_medio']),
            ];
        });

        return $this->csvResponse('relatorio-pagamentos-por-metodo.csv', ['Metodo de Pagamento', 'Transacoes', 'Valor Total', 'Ticket Medio'], $rows);
    }

    private function revenueByTypeRows(): Collection
    {
        return Cars::finished()
            ->where('saida', '>=', Carbon::now()->subDays(30))
            ->selectRaw("COALESCE(NULLIF(tipo_car, ''), 'nao-informado') as tipo")
            ->selectRaw('COUNT(*) as total_veiculos')
            ->selectRaw('SUM(preco) as total_faturamento')
            ->selectRaw('AVG(preco) as ticket_medio')
            ->groupBy('tipo')
            ->orderByDesc('total_faturamento')
            ->get()
            ->map(function ($item) {
                return [
                    'tipo' => $this->vehicleTypeLabel($item->tipo),
                    'total_veiculos' => (int) $item->total_veiculos,
                    'total_faturamento' => $this->formatCurrency((float) $item->total_faturamento),
                    'ticket_medio' => $this->formatCurrency((float) $item->ticket_medio),
                ];
            });
    }

    public function generatePDFRevenueByType()
    {
        $rows = $this->revenueByTypeRows();

        return $this->generateTablePDF($rows, [
            ['key' => 'tipo', 'label' => 'Tipo'],
            ['key' => 'total_veiculos', 'label' => 'Veiculos Finalizados'],
            ['key' => 'total_faturamento', 'label' => 'Faturamento Total'],
            ['key' => 'ticket_medio', 'label' => 'Ticket Medio'],
        ], 'Relatorio de Faturamento por Tipo (30 dias)', 'landscape');
    }

    public function generateExcelRevenueByType()
    {
        $rows = Cars::finished()
            ->where('saida', '>=', Carbon::now()->subDays(30))
            ->selectRaw("COALESCE(NULLIF(tipo_car, ''), 'nao-informado') as tipo")
            ->selectRaw('COUNT(*) as total_veiculos')
            ->selectRaw('SUM(preco) as total_faturamento')
            ->selectRaw('AVG(preco) as ticket_medio')
            ->groupBy('tipo')
            ->orderByDesc('total_faturamento')
            ->get()
            ->map(function ($item) {
                return [
                    $this->vehicleTypeLabel($item->tipo),
                    (int) $item->total_veiculos,
                    number_format((float) $item->total_faturamento, 2, ',', '.'),
                    number_format((float) $item->ticket_medio, 2, ',', '.'),
                ];
            });

        return $this->csvResponse('relatorio-faturamento-por-tipo.csv', ['Tipo', 'Veiculos Finalizados', 'Faturamento Total', 'Ticket Medio'], $rows);
    }

    private function dailyMovementRows(int $days = 30): Collection
    {
        $startDate = Carbon::today()->subDays($days - 1);

        $entries = Cars::query()
            ->where('created_at', '>=', $startDate->copy()->startOfDay())
            ->selectRaw('DATE(created_at) as dia')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('dia')
            ->pluck('total', 'dia');

        $exits = Cars::finished()
            ->where('saida', '>=', $startDate->copy()->startOfDay())
            ->selectRaw('DATE(saida) as dia')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('dia')
            ->pluck('total', 'dia');

        return collect(range(0, $days - 1))->map(function ($offset) use ($startDate, $entries, $exits) {
            $date = $startDate->copy()->addDays($offset);
            $key = $date->toDateString();
            $totalEntries = (int) ($entries[$key] ?? 0);
            $totalExits = (int) ($exits[$key] ?? 0);

            return [
                'data' => $date->format('d/m/Y'),
                'entradas' => $totalEntries,
                'saidas' => $totalExits,
                'saldo_dia' => $totalEntries - $totalExits,
            ];
        });
    }

    public function generatePDFDailyMovement()
    {
        $rows = $this->dailyMovementRows(30);

        return $this->generateTablePDF($rows, [
            ['key' => 'data', 'label' => 'Data'],
            ['key' => 'entradas', 'label' => 'Entradas'],
            ['key' => 'saidas', 'label' => 'Saidas'],
            ['key' => 'saldo_dia', 'label' => 'Saldo do Dia'],
        ], 'Relatorio de Movimentacao Diaria (30 dias)', 'landscape');
    }

    public function generateExcelDailyMovement()
    {
        $rows = $this->dailyMovementRows(30)->map(function ($row) {
            return [
                $row['data'],
                $row['entradas'],
                $row['saidas'],
                $row['saldo_dia'],
            ];
        });

        return $this->csvResponse('relatorio-movimentacao-diaria.csv', ['Data', 'Entradas', 'Saidas', 'Saldo do Dia'], $rows);
    }

    private function expiringSubscribersRows(int $days = 7): Collection
    {
        return MonthlySubscriber::expiringSoon($days)
            ->orderBy('end_date')
            ->get()
            ->map(function (MonthlySubscriber $subscriber) {
                $remainingDays = max(Carbon::today()->diffInDays(Carbon::parse($subscriber->end_date), false), 0);

                return [
                    'nome' => $subscriber->name,
                    'placa' => $subscriber->vehicle_plate,
                    'tipo' => $this->vehicleTypeLabel($subscriber->vehicle_type),
                    'vencimento' => optional($subscriber->end_date)->format('d/m/Y'),
                    'mensalidade' => $this->formatCurrency((float) $subscriber->monthly_fee),
                    'situacao' => $remainingDays === 0 ? 'Vence hoje' : "Vence em {$remainingDays} dia(s)",
                ];
            });
    }

    public function generatePDFExpiringSubscribers()
    {
        $rows = $this->expiringSubscribersRows(7);

        return $this->generateTablePDF($rows, [
            ['key' => 'nome', 'label' => 'Nome'],
            ['key' => 'placa', 'label' => 'Placa'],
            ['key' => 'tipo', 'label' => 'Tipo'],
            ['key' => 'vencimento', 'label' => 'Vencimento'],
            ['key' => 'mensalidade', 'label' => 'Mensalidade'],
            ['key' => 'situacao', 'label' => 'Situacao'],
        ], 'Relatorio de Mensalistas Vencendo em 7 dias', 'landscape');
    }

    public function generateExcelExpiringSubscribers()
    {
        $rows = MonthlySubscriber::expiringSoon(7)
            ->orderBy('end_date')
            ->get()
            ->map(function (MonthlySubscriber $subscriber) {
                $remainingDays = max(Carbon::today()->diffInDays(Carbon::parse($subscriber->end_date), false), 0);

                return [
                    $subscriber->name,
                    $subscriber->vehicle_plate,
                    $this->vehicleTypeLabel($subscriber->vehicle_type),
                    optional($subscriber->end_date)->format('d/m/Y'),
                    number_format((float) $subscriber->monthly_fee, 2, ',', '.'),
                    $remainingDays === 0 ? 'Vence hoje' : "Vence em {$remainingDays} dia(s)",
                ];
            });

        return $this->csvResponse('relatorio-mensalistas-vencendo.csv', ['Nome', 'Placa', 'Tipo', 'Vencimento', 'Mensalidade', 'Situacao'], $rows);
    }

    private function inactiveSubscribersRows(): Collection
    {
        return MonthlySubscriber::query()
            ->where('is_active', false)
            ->orderByDesc('end_date')
            ->get()
            ->map(function (MonthlySubscriber $subscriber) {
                return [
                    'nome' => $subscriber->name,
                    'placa' => $subscriber->vehicle_plate,
                    'tipo' => $this->vehicleTypeLabel($subscriber->vehicle_type),
                    'inicio' => optional($subscriber->start_date)->format('d/m/Y'),
                    'vencimento' => optional($subscriber->end_date)->format('d/m/Y'),
                    'mensalidade' => $this->formatCurrency((float) $subscriber->monthly_fee),
                ];
            });
    }

    public function generatePDFInactiveSubscribers()
    {
        $rows = $this->inactiveSubscribersRows();

        return $this->generateTablePDF($rows, [
            ['key' => 'nome', 'label' => 'Nome'],
            ['key' => 'placa', 'label' => 'Placa'],
            ['key' => 'tipo', 'label' => 'Tipo'],
            ['key' => 'inicio', 'label' => 'Inicio'],
            ['key' => 'vencimento', 'label' => 'Vencimento'],
            ['key' => 'mensalidade', 'label' => 'Mensalidade'],
        ], 'Relatorio de Mensalistas Inativos', 'landscape');
    }

    public function generateExcelInactiveSubscribers()
    {
        $rows = MonthlySubscriber::query()
            ->where('is_active', false)
            ->orderByDesc('end_date')
            ->get()
            ->map(function (MonthlySubscriber $subscriber) {
                return [
                    $subscriber->name,
                    $subscriber->vehicle_plate,
                    $this->vehicleTypeLabel($subscriber->vehicle_type),
                    optional($subscriber->start_date)->format('d/m/Y'),
                    optional($subscriber->end_date)->format('d/m/Y'),
                    number_format((float) $subscriber->monthly_fee, 2, ',', '.'),
                ];
            });

        return $this->csvResponse('relatorio-mensalistas-inativos.csv', ['Nome', 'Placa', 'Tipo', 'Inicio', 'Vencimento', 'Mensalidade'], $rows);
    }

    public function showReports()
    {
        $reports = [
            [
                'name' => 'Relatorio de Carros',
                'description' => 'Lista os carros registrados nos ultimos 30 dias.',
                'pdf_route' => route('generatePDFCars'),
                'excel_route' => route('generateExcelCars'),
            ],
            [
                'name' => 'Relatorio de Motos',
                'description' => 'Lista as motos registradas nos ultimos 30 dias.',
                'pdf_route' => route('generatePDFMotorcycle'),
                'excel_route' => route('generateExcelMotorcycle'),
            ],
            [
                'name' => 'Relatorio de Caminhonetes',
                'description' => 'Lista as caminhonetes registradas nos ultimos 30 dias.',
                'pdf_route' => route('generatePDFTruck'),
                'excel_route' => route('generateExcelTruck'),
            ],
            [
                'name' => 'Relatorio de Veiculos Finalizados',
                'description' => 'Lista os veiculos com status finalizado.',
                'pdf_route' => route('generatePDFFinishedVehicles'),
                'excel_route' => null,
            ],
            [
                'name' => 'Relatorio de Entrada e Saida',
                'description' => 'Historico de entrada e saida dos ultimos 30 dias.',
                'pdf_route' => route('generatePDFEntryExit'),
                'excel_route' => null,
            ],
            [
                'name' => 'Relatorio Financeiro',
                'description' => 'Consolidado financeiro baseado em veiculos finalizados.',
                'pdf_route' => route('generatePDFFinancial'),
                'excel_route' => null,
            ],
            [
                'name' => 'Relatorio de Pagamentos por Metodo',
                'description' => 'Distribuicao dos recebimentos por forma de pagamento nos ultimos 30 dias.',
                'pdf_route' => route('generatePDFPaymentMethods'),
                'excel_route' => route('generateExcelPaymentMethods'),
            ],
            [
                'name' => 'Relatorio de Mensalistas Ativos',
                'description' => 'Lista de mensalistas ativos no momento.',
                'pdf_route' => route('generatePDFActiveSubscribers'),
                'excel_route' => null,
            ],
            [
                'name' => 'Relatorio de Ocupacao',
                'description' => 'Quantidade atual de veiculos ocupando vagas.',
                'pdf_route' => route('generatePDFParkingOccupancy'),
                'excel_route' => null,
            ],
            [
                'name' => 'Relatorio de Veiculos Estacionados',
                'description' => 'Veiculos que continuam no patio.',
                'pdf_route' => route('generatePDFCurrentlyParked'),
                'excel_route' => null,
            ],
            [
                'name' => 'Relatorio de Permanencia Acima de 24h',
                'description' => 'Veiculos ativos estacionados ha mais de 24 horas.',
                'pdf_route' => route('generatePDFViolations'),
                'excel_route' => null,
            ],
            [
                'name' => 'Relatorio de Veiculos Finalizados Hoje',
                'description' => 'Veiculos finalizados no dia atual.',
                'pdf_route' => route('generatePDFFinishedVehiclesToday'),
                'excel_route' => route('generateExcelFinishedVehiclesToday'),
            ],
            [
                'name' => 'Relatorio Top 20 por Faturamento',
                'description' => 'Ranking dos 20 veiculos com maior valor nos ultimos 30 dias.',
                'pdf_route' => route('generatePDFTopRevenueVehicles'),
                'excel_route' => route('generateExcelTopRevenueVehicles'),
            ],
            [
                'name' => 'Relatorio de Faturamento por Tipo',
                'description' => 'Consolidado por tipo de veiculo nos ultimos 30 dias.',
                'pdf_route' => route('generatePDFRevenueByType'),
                'excel_route' => route('generateExcelRevenueByType'),
            ],
            [
                'name' => 'Relatorio de Movimentacao Diaria',
                'description' => 'Entradas e saidas consolidadas dos ultimos 30 dias.',
                'pdf_route' => route('generatePDFDailyMovement'),
                'excel_route' => route('generateExcelDailyMovement'),
            ],
            [
                'name' => 'Relatorio de Mensalistas Vencendo',
                'description' => 'Mensalistas com vencimento previsto para os proximos 7 dias.',
                'pdf_route' => route('generatePDFExpiringSubscribers'),
                'excel_route' => route('generateExcelExpiringSubscribers'),
            ],
            [
                'name' => 'Relatorio de Mensalistas Inativos',
                'description' => 'Mensalistas vencidos/inativos para acompanhamento administrativo.',
                'pdf_route' => route('generatePDFInactiveSubscribers'),
                'excel_route' => route('generateExcelInactiveSubscribers'),
            ],
        ];

        return view('Reports.index', compact('reports'));
    }
}
