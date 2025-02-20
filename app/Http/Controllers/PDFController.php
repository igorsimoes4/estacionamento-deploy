<?php

namespace App\Http\Controllers;

use App\Models\Cars;
use App\Models\Settings;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Carbon\Carbon as Carbon;
use Illuminate\Http\Request;

class PDFController extends Controller
{
    public function generatePDFCars() {

        $thirtyDaysAgo = Carbon::now()->subDays(30);
        $cars = Cars::where('tipo_car', 'carro')
        ->where('created_at', '>=', $thirtyDaysAgo)
        ->orderBy('created_at', 'desc')
        ->get();

        $estacionamento = Settings::find(1);


        $pdf = PDF::loadView("layouts.PDF.A4", ['cars' => $cars, 'estacionamento' => $estacionamento])->setPaper('a4', 'portrait');
        return $pdf->stream();
    }

    public function generatePDFMotorcycle() {

        $thirtyDaysAgo = Carbon::now()->subDays(30);
        $cars = Cars::where('created_at', '>=', $thirtyDaysAgo)
        ->where('tipo_car', 'moto')
        ->orderBy('created_at', 'desc')
        ->get();

        $estacionamento = Settings::find(1);

        $pdf = PDF::loadView("layouts.PDF.A4", ['cars' => $cars, 'estacionamento' => $estacionamento])->setPaper('a4', 'portrait');
        return $pdf->stream();
    }

    public function generatePDFTruck() {

        $thirtyDaysAgo = Carbon::now()->subDays(30);
        $cars = Cars::where('created_at', '>=', $thirtyDaysAgo)
        ->where('tipo_car', 'caminhonete')
        ->orderBy('created_at', 'desc')
        ->get();

        $estacionamento = Settings::find(1);

        $pdf = PDF::loadView("layouts.PDF.A4", ['cars' => $cars, 'estacionamento' => $estacionamento])->setPaper('a4', 'portrait');
        return $pdf->stream();
    }
    public function generatePDFVehiclesFinal() {
        $cars = Cars::where('status', 'finalizado')->get();
        $estacionamento = Settings::find(1);
        $pdf = PDF::loadView("layouts.PDF.A4", ['cars' => $cars, 'estacionamento' => $estacionamento])->setPaper('a4', 'portrait');
        return $pdf->stream();
    }
    public function showReports() {
        $reports = [
            [
                'name' => 'Relatório de Carros',
                'description' => 'Lista todos os carros registrados nos últimos 30 dias.',
                'route' => route('generatePDFCars'),
            ],
            [
                'name' => 'Relatório de Motos',
                'description' => 'Lista todas as motos registradas nos últimos 30 dias.',
                'route' => route('generatePDFMotorcycle'),
            ],
            [
                'name' => 'Relatório de Caminhonetes',
                'description' => 'Lista todas as caminhonetes registradas nos últimos 30 dias.',
                'route' => route('generatePDFTruck'),
            ],
            [
                'name' => 'Relatório de Veículos Finalizados',
                'description' => 'Lista todos os veículos com status finalizado.',
                // 'route' => route('generatePDFFinishedVehicles'),
                'route' => "#",
            ],
            [
                'name' => 'Relatório de Veículos por Cliente',
                'description' => 'Lista todos os veículos cadastrados para um determinado cliente.',
                // 'route' => route('generatePDFClientVehicles'),
                'route' => "#",
            ],
            [
                'name' => 'Relatório de Entrada e Saída de Veículos',
                'description' => 'Mostra um histórico detalhado de entrada e saída de veículos nos últimos 30 dias.',
                // 'route' => route('generatePDFEntryExit'),
                'route' => "#",
            ],
            [
                'name' => 'Relatório Financeiro',
                'description' => 'Resumo das receitas do estacionamento nos últimos 30 dias, incluindo pagamentos avulsos e mensalidades.',
                // 'route' => route('generatePDFFinancial'),
                'route' => "#",
            ],
            [
                'name' => 'Relatório de Mensalistas Ativos',
                'description' => 'Lista todos os clientes com planos de mensalidade ativos.',
                // 'route' => route('generatePDFActiveSubscribers'),
                'route' => "#",
            ],
            [
                'name' => 'Relatório de Ocupação do Estacionamento',
                'description' => 'Mostra a média diária de ocupação do estacionamento nos últimos 30 dias.',
                // 'route' => route('generatePDFParkingOccupancy'),
                'route' => "#",
            ],
            [
                'name' => 'Relatório de Veículos Estacionados no Momento',
                'description' => 'Exibe a lista de todos os veículos que estão atualmente no estacionamento.',
                // 'route' => route('generatePDFCurrentlyParked'),
                'route' => "#",
            ],
            [
                'name' => 'Relatório de Infrações ou Ocorrências',
                'description' => 'Lista todas as ocorrências registradas no estacionamento, como estadia excedida ou veículos sem pagamento.',
                // 'route' => route('generatePDFViolations'),
                'route' => "#",
            ],
        ];

        return view('reports.index', compact('reports'));
    }



}


