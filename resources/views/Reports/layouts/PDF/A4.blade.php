<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório de Veículos - {{ $estacionamento->nome_da_empresa }}</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @page {
            margin: 10mm 15mm 10mm 0mm;
            background-color: #fff;
        }

        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #fff;
            color: #343a40;
        }

        .container {
            background-color: #fff;
            max-width: 100%;
            padding: 30px;
            margin: 20px auto;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        header {
            text-align: center;
            margin-bottom: 20px;
        }

        header h2 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 5px;
        }

        header p {
            margin: 0;
            font-size: 14px;
            color: #6c757d;
        }

        header h4 {
            margin-top: 20px;
            font-size: 20px;
            font-weight: 600;
        }

        .table {
            margin-bottom: 20px;
        }

        .table th {
            background-color: #007bff;
            color: #fff;
            font-size: 16px;
            vertical-align: middle;
            text-align: center;
        }

        .table td {
            font-size: 14px;
            vertical-align: middle;
            text-align: center;
        }

        footer {
            text-align: center;
            font-size: 13px;
            color: #6c757d;
            border-top: 1px solid #dee2e6;
            padding-top: 10px;
            margin-top: 20px;
        }

        @media print {
            body {
                background-color: #fff;
                font-size: 12px;
            }

            .container {
                box-shadow: none;
                margin: 0;
                padding: 15px;
            }

            footer {
                position: fixed;
                bottom: 10mm;
                left: 10mm;
                right: 10mm;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <header>
            <h2>{{ $estacionamento->nome_da_empresa }}</h2>
            <p>Endereço: {{ $estacionamento->endereco }}, {{ $estacionamento->cidade }}, {{ $estacionamento->estado }}
            </p>
            <p>Telefone: {{ $estacionamento->telefone_da_empresa }} | Email: {{ $estacionamento->email_da_empresa }}</p>
            <h4>{{ $reportTitle }}</h4>
            <p>Gerado em: {{ date('d/m/Y H:i') }}</p>
        </header>

        <main>
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Modelo</th>
                        <th>Placa</th>
                        <th>Data de Entrada</th>
                        @if ($cars->contains(fn($car) => $car->status === 'finalizado'))
                            <th>Data de Saída</th>
                            <th>Preço</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @php $totalPrice = 0; @endphp
                    @foreach ($cars as $car)
                        <tr>
                            <td>{{ $car->modelo }}</td>
                            <td>{{ $car->placa }}</td>
                            <td>{{ $car->created_at->format('d/m/Y H:i') }}</td>
                            @if ($car->status === 'finalizado')
                                <td>{{ $car->updated_at->format('d/m/Y H:i') }}</td>
                                <td>R$ {{ number_format($car->preco, 2, ',', '.') }}</td>
                                @php $totalPrice += $car->preco; @endphp
                            @endif
                        </tr>
                    @endforeach
                </tbody>
                @if ($cars->contains(fn($car) => $car->status === 'finalizado'))
                    <tfoot>
                        <tr>
                            <td colspan="4" class="text-right"><strong>Total:</strong></td>
                            <td><strong>R$ {{ number_format($totalPrice, 2, ',', '.') }}</strong></td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </main>

        <footer>
            <p>Relatório gerado automaticamente pelo sistema.</p>
        </footer>
    </div>
</body>

</html>
