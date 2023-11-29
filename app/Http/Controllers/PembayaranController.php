<?php

namespace App\Http\Controllers;

use App\Models\Estacionamento;
use App\Models\Settings;
use App\Pembayaran;
use Exception;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Mike42\Escpos;
use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\PrintConnectors\FilePrintConnector;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;

class PembayaranController extends Controller
{
    public function print(Request $request) {
        $car = $request->all();
        $estacionamento = Settings::find(1);
        try {
            $connector = new WindowsPrintConnector("Canon MP250 series Printer");
            $printer = new Printer($connector);
            $printer -> setJustification(Printer::JUSTIFY_CENTER);
            $printer -> text("------------------------------------------\n");
            $printer -> text($estacionamento->nome_da_Empresa."\n");
            $printer -> text($estacionamento->endereco."\n");
            $printer -> text($estacionamento->cnpj_Cpf_da_empresa."\n");
            $printer -> text($estacionamento->telefone_da_empresa."\n");
            $printer -> text("Data: ".$car['data']."   Hora: ".$car['hora']."\n");
            $printer -> text("------------------------------------------\n");
            $printer -> text("Veículo:  ".$car['tipo_car']."\n");
            $printer -> text("Placa:    ".$car['placa']."\n");
            $printer -> text("Entrada:  ".$car['entrada']."\n");
            $printer -> text("------------------------------------------\n");
            $printer -> text("Guarde este ticket consigo.\n");
            $printer -> text("Não deixe-o no interior do veículo.\n");
            $printer -> text("O veículo será entregue ao portador.\n");
            $printer -> text("Seg a Sex das 08:00 as 19:30\n");
            $printer -> text("Sábado das 08:00 as 18:00\n");
            $printer -> text("\n");
            $printer -> text(">--\n");
            $printer -> cut();
            $printer -> close();
        } catch(Exception $e) {
            echo "Não foi possível imprimir nesta impressora: " . $e -> getMessage() . "\n";
        }
    }
}
