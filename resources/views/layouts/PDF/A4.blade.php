<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório de Veículos</title>
    <!-- Link para o CSS do Bootstrap -->
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            margin: 40px;
            padding: 20px;
        }
        .header, .footer {
            text-align: center;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 5px;
            margin-bottom: 30px;
        }
        .footer {
            margin-top: 30px;
            font-size: 14px;
            color: #6c757d;
        }
        .table {
            width: 100%;
            table-layout: fixed;
        }
        .table th, .table td {
            word-wrap: break-word;
            text-align: center;
            vertical-align: middle;
            padding: 15px;
        }
        @media print {
            body {
                margin: 0;
                padding: 0;
            }
            .container {
                width: 100%;
                max-width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="header">
            <h2>Relatório de Veículos</h2>
            <p>Gerado em: {{ date('d/m/Y H:i') }}</p>
        </div>

        <table class="table table-bordered table-striped">
            <thead class="thead-dark">
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

        <div class="footer">
            <p>Relatório gerado automaticamente pelo sistema.</p>
        </div>
    </div>
</body>
</html>
