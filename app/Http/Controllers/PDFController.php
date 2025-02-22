<?php

namespace App\Http\Controllers;

use App\Models\Cars;
use App\Models\Settings;
use App\Models\Payments;
use App\Models\Subscribers;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Carbon\Carbon as Carbon;
use Illuminate\Http\Request;

class PDFController extends Controller
{
    /**
     * Gera um PDF utilizando uma view e dados dinâmicos, incluindo o título do relatório.
     *
     * @param mixed  $data        Dados a serem passados para a view.
     * @param string $view        Caminho da view.
     * @param string $orientation Orientação do papel.
     * @param string $reportTitle Título dinâmico do relatório.
     * @param string $dataKey     Nome da chave para os dados na view (padrão: 'cars').
     *
     * @return \Barryvdh\DomPDF\PDF
     */
    private function generatePDF($data, $view = "Reports.layouts.PDF.A4", $orientation = "landscape", $reportTitle = '', $dataKey = 'cars')
    {
        $estacionamento = Settings::find(1);
        $pdf = PDF::loadView($view, [
            $dataKey       => $data,
            'estacionamento' => $estacionamento,
            'reportTitle'  => $reportTitle,
        ])->setPaper('A4', $orientation);
        return $pdf->stream();
    }

    public function generatePDFCars()
    {
        $cars = Cars::where('tipo_car', 'carro')
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->where('status', null)
            ->orderBy('created_at', 'desc')
            ->get();
        return $this->generatePDF($cars, 'Reports.layouts.PDF.A4', 'portrait', 'Relatório de Carros');
    }

    public function generatePDFMotorcycle()
    {
        $cars = Cars::where('tipo_car', 'moto')
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->orderBy('created_at', 'desc')
            ->get();
        return $this->generatePDF($cars, 'Reports.layouts.PDF.A4', 'portrait', 'Relatório de Motos');
    }

    public function generatePDFTruck()
    {
        $cars = Cars::where('tipo_car', 'caminhonete')
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->orderBy('created_at', 'desc')
            ->get();
        return $this->generatePDF($cars, 'Reports.layouts.PDF.A4', 'portrait', 'Relatório de Caminhonetes');
    }

    public function generatePDFFinishedVehicles()
    {
        $cars = Cars::where('status', 'finalizado')->get();
        return $this->generatePDF($cars, 'Reports.layouts.PDF.A4', 'landscape', 'Relatório de Veículos Finalizados');
    }

    public function generatePDFClientVehicles($client_id)
    {
        $cars = Cars::where('client_id', $client_id)->get();
        return $this->generatePDF($cars, 'Reports.layouts.PDF.A4', 'portrait', 'Relatório de Veículos por Cliente');
    }

    public function generatePDFEntryExit()
    {
        $cars = Cars::where('created_at', '>=', Carbon::now()->subDays(30))
            ->orderBy('created_at', 'desc')
            ->get();
        return $this->generatePDF($cars, 'Reports.layouts.PDF.A4', 'landscape', 'Relatório de Entrada e Saída de Veículos');
    }

    public function generatePDFFinancial()
    {
        $payments = Payments::where('created_at', '>=', Carbon::now()->subDays(30))->get();
        $estacionamento = Settings::find(1);
        $pdf = PDF::loadView("Reports.layouts.PDF.Financial", [
            'payments'       => $payments,
            'estacionamento' => $estacionamento,
            'reportTitle'    => 'Relatório Financeiro'
        ])->setPaper('a4', 'portrait');
        return $pdf->stream();
    }

    public function generatePDFActiveSubscribers()
    {
        $subscribers = Subscribers::where('created_at', '>=', Carbon::now()->subDays(30))->get();
        $estacionamento = Settings::find(1);
        $pdf = PDF::loadView("Reports.layouts.PDF.Subscribers", [
            'subscribers'    => $subscribers,
            'estacionamento' => $estacionamento,
            'reportTitle'    => 'Relatório de Mensalistas Ativos'
        ])->setPaper('a4', 'portrait');
        return $pdf->stream();
    }

    public function generatePDFParkingOccupancy()
    {
        $occupancy = Cars::where('created_at', '>=', Carbon::now()->subDays(30))->count();
        $estacionamento = Settings::find(1);
        $pdf = PDF::loadView("Reports.layouts.PDF.Occupancy", [
            'occupancy'      => $occupancy,
            'estacionamento' => $estacionamento,
            'reportTitle'    => 'Relatório de Ocupação do Estacionamento'
        ])->setPaper('a4', 'portrait');
        return $pdf->stream();
    }

    public function generatePDFCurrentlyParked()
    {
        $cars = Cars::whereNull('checkout_time')->get();
        return $this->generatePDF($cars, 'Reports.layouts.PDF.A4', 'portrait', 'Relatório de Veículos Estacionados no Momento');
    }

    public function generatePDFViolations()
    {
        $violations = Cars::where('status', 'infração')->get();
        $estacionamento = Settings::find(1);
        $pdf = PDF::loadView("Reports.layouts.PDF.Violations", [
            'violations'     => $violations,
            'estacionamento' => $estacionamento,
            'reportTitle'    => 'Relatório de Infrações ou Ocorrências'
        ])->setPaper('a4', 'portrait');
        return $pdf->stream();
    }

    public function showReports()
    {
        $reports = [
            ['name' => 'Relatório de Carros', 'description' => 'Lista todos os carros registrados nos últimos 30 dias.', 'route' => route('generatePDFCars')],
            ['name' => 'Relatório de Motos', 'description' => 'Lista todas as motos registradas nos últimos 30 dias.', 'route' => route('generatePDFMotorcycle')],
            ['name' => 'Relatório de Caminhonetes', 'description' => 'Lista todas as caminhonetes registradas nos últimos 30 dias.', 'route' => route('generatePDFTruck')],
            ['name' => 'Relatório de Veículos Finalizados', 'description' => 'Lista todos os veículos com status finalizado.', 'route' => route('generatePDFFinishedVehicles')],
            ['name' => 'Relatório de Veículos por Cliente', 'description' => 'Lista todos os veículos cadastrados para um determinado cliente.', 'route' => route('generatePDFClientVehicles', ['client_id' => 1])],
            ['name' => 'Relatório de Entrada e Saída de Veículos', 'description' => 'Histórico detalhado de entrada e saída de veículos nos últimos 30 dias.', 'route' => route('generatePDFEntryExit')],
            ['name' => 'Relatório Financeiro', 'description' => 'Resumo das receitas do estacionamento nos últimos 30 dias, incluindo pagamentos avulsos e mensalidades.', 'route' => route('generatePDFFinancial')],
            ['name' => 'Relatório de Mensalistas Ativos', 'description' => 'Lista dos clientes com planos de mensalidade ativos.', 'route' => route('generatePDFActiveSubscribers')],
            ['name' => 'Relatório de Ocupação do Estacionamento', 'description' => 'Média diária de ocupação do estacionamento nos últimos 30 dias.', 'route' => route('generatePDFParkingOccupancy')],
            ['name' => 'Relatório de Veículos Estacionados no Momento', 'description' => 'Lista dos veículos que estão atualmente no estacionamento.', 'route' => route('generatePDFCurrentlyParked')],
            ['name' => 'Relatório de Infrações ou Ocorrências', 'description' => 'Ocorrências registradas, como estadia excedida ou veículos sem pagamento.', 'route' => route('generatePDFViolations')],
        ];

        return view('Reports.index', compact('reports'));
    }
}
