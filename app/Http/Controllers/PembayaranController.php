<?php

namespace App\Http\Controllers;

use App\Models\Settings;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\Printer;

class PembayaranController extends Controller
{
    public function print(Request $request)
    {
        $car = $request->validate([
            'tipo_car' => ['required', 'string'],
            'placa' => ['required', 'string'],
            'data' => ['required', 'string'],
            'hora' => ['required', 'string'],
        ]);

        $estacionamento = Settings::firstOrCreate(['id' => 1], []);

        $data = [
            'empresa' => $estacionamento->nome_da_empresa,
            'endereco' => $estacionamento->endereco,
            'cnpj_cpf' => $estacionamento->cnpj_cpf_da_empresa,
            'telefone' => $estacionamento->telefone_da_empresa,
            'data' => $car['data'],
            'hora' => $car['hora'],
            'tipo_car' => $car['tipo_car'],
            'placa' => $car['placa'],
            'entrada' => $car['data'] . ' ' . $car['hora'],
        ];

        try {
            $pdf = PDF::loadView('Reports.layouts.PDF.thermal_ticket', $data)
                ->setPaper([0, 0, 226, 1000]);

            return $pdf->stream('ticket.pdf');
        } catch (Exception $e) {
            Log::error('Nao foi possivel gerar o PDF', ['error' => $e->getMessage()]);
            return redirect()->route('cars.index')->with('delete_car', 'Nao foi possivel gerar o PDF.');
        }
    }

    public function printTicket(Request $request)
    {
        $car = $request->validate([
            'tipo_car' => ['required', 'string'],
            'placa' => ['required', 'string'],
            'data' => ['required', 'string'],
            'hora' => ['required', 'string'],
        ]);

        $estacionamento = Settings::firstOrCreate(['id' => 1], []);

        $data = [
            'empresa' => $estacionamento->nome_da_empresa,
            'endereco' => $estacionamento->endereco,
            'cnpj_cpf' => $estacionamento->cnpj_cpf_da_empresa,
            'telefone' => $estacionamento->telefone_da_empresa,
            'data' => $car['data'],
            'hora' => $car['hora'],
            'tipo_car' => $car['tipo_car'],
            'placa' => $car['placa'],
            'entrada' => $car['data'] . ' ' . $car['hora'],
        ];

        try {
            $connector = new WindowsPrintConnector('PDF Architect 9');
            $printer = new Printer($connector);

            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->text("------------------------------------------\n");
            $printer->text($data['empresa'] . "\n");
            $printer->text('Endereco: ' . $data['endereco'] . "\n");
            $printer->text('CNPJ: ' . $data['cnpj_cpf'] . "\n");
            $printer->text('Telefone: ' . $data['telefone'] . "\n");
            $printer->text('Data: ' . $data['data'] . '   Hora: ' . $data['hora'] . "\n");
            $printer->text("------------------------------------------\n");
            $printer->text('Veiculo:  ' . $data['tipo_car'] . "\n");
            $printer->text('Placa:    ' . $data['placa'] . "\n");
            $printer->text('Entrada:  ' . $data['data'] . '   Hora: ' . $data['hora'] . "\n");
            $printer->text("------------------------------------------\n");
            $printer->text("Guarde este ticket consigo.\n");
            $printer->text("Nao deixe-o no interior do veiculo.\n");
            $printer->text("O veiculo sera entregue ao portador.\n");
            $printer->text("Seg a Sex das 08:00 as 19:30\n");
            $printer->text("Sabado das 08:00 as 18:00\n\n");
            $printer->cut();
            $printer->close();

            return redirect()->route('cars.index')->with('create', 'Ticket impresso com sucesso.');
        } catch (Exception $e) {
            Log::error('Nao foi possivel imprimir nesta impressora', ['error' => $e->getMessage()]);
            return redirect()->route('cars.index')->with('delete_car', 'Nao foi possivel imprimir nesta impressora.');
        }
    }
}
