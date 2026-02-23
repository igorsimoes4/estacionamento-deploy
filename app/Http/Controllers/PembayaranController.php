<?php

namespace App\Http\Controllers;

use App\Models\Settings;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Mike42\Escpos\PrintConnectors\CupsPrintConnector;
use Mike42\Escpos\PrintConnectors\FilePrintConnector;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
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
        $data = $this->buildTicketData($estacionamento, $car);

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
        $data = $this->buildTicketData($estacionamento, $car);

        try {
            if (!$estacionamento->ticket_print_enabled) {
                return redirect()->route('cars.index')->with('delete_car', 'Impressao direta desativada. Ative em Configuracoes > Pagamentos.');
            }

            $connector = $this->resolveTicketConnector($estacionamento);
            $printer = new Printer($connector);
            $this->printTicketLayout($printer, $data, $estacionamento);

            return redirect()->route('cars.index')->with('create', 'Ticket impresso com sucesso.');
        } catch (Exception $e) {
            Log::error('Nao foi possivel imprimir nesta impressora', ['error' => $e->getMessage()]);
            return redirect()->route('cars.index')->with('delete_car', 'Nao foi possivel imprimir nesta impressora.');
        }
    }

    private function buildTicketData(Settings $settings, array $car): array
    {
        return [
            'empresa' => $settings->nome_da_empresa,
            'endereco' => $settings->endereco,
            'cnpj_cpf' => $settings->cnpj_cpf_da_empresa,
            'telefone' => $settings->telefone_da_empresa,
            'data' => $car['data'],
            'hora' => $car['hora'],
            'tipo_car' => $car['tipo_car'],
            'placa' => $car['placa'],
            'entrada' => $car['data'] . ' ' . $car['hora'],
        ];
    }

    private function resolveTicketConnector(Settings $settings): mixed
    {
        $driver = strtolower(trim((string) ($settings->ticket_printer_driver ?: 'windows')));
        $target = trim((string) ($settings->ticket_printer_target ?: ''));

        if ($target === '') {
            throw new Exception('Destino da impressora nao configurado.');
        }

        return match ($driver) {
            'windows' => new WindowsPrintConnector($target),
            'cups' => new CupsPrintConnector($target),
            'network' => $this->buildNetworkConnector($target, (int) ($settings->ticket_printer_port ?: 9100), (int) ($settings->ticket_printer_timeout ?: 10)),
            'file' => new FilePrintConnector($target),
            default => throw new Exception('Driver de impressora invalido: ' . $driver),
        };
    }

    private function buildNetworkConnector(string $target, int $port, int $timeout): NetworkPrintConnector
    {
        if (str_contains($target, ':')) {
            [$host, $inlinePort] = explode(':', $target, 2);
            $target = trim($host);
            if (is_numeric($inlinePort)) {
                $port = (int) $inlinePort;
            }
        }

        return new NetworkPrintConnector($target, $port > 0 ? $port : 9100, $timeout > 0 ? $timeout : 10);
    }

    private function printTicketLayout(Printer $printer, array $data, Settings $settings): void
    {
        $lineWidth = (int) ($settings->ticket_line_width ?: 42);
        $lineWidth = max(16, min(64, $lineWidth));
        $separator = str_repeat('-', $lineWidth);
        $copies = max(1, min(5, (int) ($settings->ticket_print_copies ?: 1)));

        for ($copy = 1; $copy <= $copies; $copy++) {
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->text($separator . "\n");
            $printer->text(($data['empresa'] ?: 'Estacionamento') . "\n");
            $printer->text('Endereco: ' . ($data['endereco'] ?: '-') . "\n");
            $printer->text('CNPJ: ' . ($data['cnpj_cpf'] ?: '-') . "\n");
            $printer->text('Telefone: ' . ($data['telefone'] ?: '-') . "\n");
            $printer->text('Data: ' . $data['data'] . '   Hora: ' . $data['hora'] . "\n");
            $printer->text($separator . "\n");
            $printer->text('Veiculo:  ' . $data['tipo_car'] . "\n");
            $printer->text('Placa:    ' . $data['placa'] . "\n");
            $printer->text('Entrada:  ' . $data['data'] . '   Hora: ' . $data['hora'] . "\n");
            $printer->text($separator . "\n");
            $printer->text("Guarde este ticket consigo.\n");
            $printer->text("Nao deixe-o no interior do veiculo.\n");
            $printer->text("O veiculo sera entregue ao portador.\n");
            $printer->text("Seg a Sex das 08:00 as 19:30\n");
            $printer->text("Sabado das 08:00 as 18:00\n");
            $printer->text("Via " . $copy . " de " . $copies . "\n\n");
            $printer->cut();
        }

        $printer->close();
    }
}
