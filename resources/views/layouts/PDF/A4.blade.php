<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório de Veículos</title>
    <!-- Link para o CSS do Bootstrap -->
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Configuração para A4 */
        @page {
            size: A4;
            margin: 10mm;
        }

        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            margin: 0;
            padding: 0;
            color: #333;
        }

        .container-fluid {
            max-width: 100%;
            margin: 0 auto;
        }

        .header, .footer {
            text-align: center;
            padding: 10px 0;
            background-color: #f8f9fa;
            border-radius: 5px;
        }

        .header h2 {
            margin: 0;
            font-size: 24px;
        }

        .header p {
            font-size: 14px;
            color: #6c757d;
        }

        .footer {
            font-size: 12px;
            color: #6c757d;
            margin-top: 20px;
        }

        .table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .table th, .table td {
            word-wrap: break-word;
            text-align: center;
            vertical-align: middle;
            padding: 12px;
            border: 1px solid #dee2e6;
        }

        .table th {
            background-color: #343a40;
            color: #fff;
        }

        .table td {
            background-color: #f8f9fa;
        }

        /* Ajustes de impressão */
        @media print {
            body {
                margin: 0;
                padding: 0;
                font-size: 12px;
            }

            .container-fluid {
                width: 100%;
                max-width: 100%;
                display: flex;
                justify-content: center;
                align-items: center;
                text-align: center;
            }

            .footer {
                font-size: 10px;
                color: #6c757d;
                position: absolute;
                bottom: 10mm;
                left: 10mm;
                right: 10mm;
            }
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <!-- Cabeçalho do Estacionamento -->
        <div class="header">
            <h2>{{$estacionamento->nome_da_empresa}}</h2>
            <p>Endereço: {{$estacionamento->endereco}}, {{$estacionamento->cidade}}, {{$estacionamento->estado}}</p>
            <p>Telefone: {{$estacionamento->telefone_da_empresa}}</p>
            <p>Email: {{$estacionamento->email_da_empresa}}</p>
            <p>Relatório de Veículos</p>
            <p>Gerado em: {{ date('d/m/Y H:i') }}</p>
        </div>

        <!-- Tabela de Dados -->
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Modelo</th>
                    <th>Placa</th>
                    <th>Data de Entrada</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($cars as $car)
                    <tr>
                        <td>{{ $car->modelo }}</td>
                        <td>{{ $car->placa }}</td>
                        <td>{{ $car->created_at->format('d/m/Y H:i') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Rodapé -->
        <div class="footer">
            <p>Relatório gerado automaticamente pelo sistema.</p>
        </div>
    </div>
</body>
</html>
