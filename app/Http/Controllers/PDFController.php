<?php

namespace App\Http\Controllers;

use App\Models\Cars;
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

        $pdf = PDF::loadView("layouts.PDF.A4", ['cars' => $cars])->setPaper('a4', 'portrait');
        return $pdf->stream();
    }

    public function generatePDFMotorcycle() {

        $thirtyDaysAgo = Carbon::now()->subDays(30);
        $cars = Cars::where('created_at', '>=', $thirtyDaysAgo)
        ->where('tipo_car', 'moto')
        ->orderBy('created_at', 'desc')
        ->get();

        $pdf = PDF::loadView("layouts.PDF.A4", ['cars' => $cars])->setPaper('a4', 'portrait');
        return $pdf->stream();
    }

    public function generatePDFTruck() {

        $thirtyDaysAgo = Carbon::now()->subDays(30);
        $cars = Cars::where('created_at', '>=', $thirtyDaysAgo)
        ->where('tipo_car', 'caminhonete')
        ->orderBy('created_at', 'desc')
        ->get();

        $pdf = PDF::loadView("layouts.PDF.A4", ['cars' => $cars])->setPaper('a4', 'portrait');
        return $pdf->stream();
    }
    public function generatePDFVehiclesFinal() {
        $cars = Cars::where('status', 'finalizado')->get();
        $pdf = PDF::loadView("layouts.PDF.A4", ['cars' => $cars])->setPaper('a4', 'portrait');
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
                'route' => "1",

            ],
        ];

        return view('reports.index', compact('reports'));
    }


}


