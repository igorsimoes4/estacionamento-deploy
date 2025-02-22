<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>{{ $reportTitle }} - {{ $estacionamento->nome_da_empresa }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            margin: 0;
            padding: 0;
            color: #333;
            background-color: #fff;
        }

        .container {
            width: 100%;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
        }

        header {
            text-align: center;
            margin-bottom: 20px;
        }

        header h1 {
            font-size: 28px;
            font-weight: bold;
            margin: 0;
            color: #007bff;
        }

        header p {
            font-size: 14px;
            margin: 2px 0;
            color: #555;
        }

        header .report-title {
            font-size: 20px;
            margin-top: 10px;
            font-weight: bold;
            color: #007bff;
        }

        .content {
            text-align: center;
            margin-top: 20px;
        }

        .occupancy-box {
            display: inline-block;
            background-color: #e9ecef;
            padding: 25px 50px;
            border-radius: 8px;
            border: 1px solid #ddd;
        }

        .occupancy-box h2 {
            font-size: 40px;
            font-weight: bold;
            color: #28a745;
            margin: 0;
        }

        .occupancy-box p {
            margin-top: 15px;
            font-size: 16px;
            color: #495057;
        }

        footer {
            text-align: center;
            margin-top: 40px;
            font-size: 12px;
            color: #6c757d;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }

        .footer-text {
            margin: 0;
        }

        .footer-small {
            font-size: 10px;
            color: #888;
        }

        .page-break {
            page-break-before: always;
        }

        @media print {
            body {
                margin: 0;
                padding: 0;
                font-size: 12px;
            }

            header h1 {
                font-size: 24px;
            }

            .occupancy-box h2 {
                font-size: 32px;
            }

            footer {
                font-size: 10px;
            }

            .container {
                padding: 15px;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <header>
            <h1>{{ $estacionamento->nome_da_empresa }}</h1>
            <p>{{ $estacionamento->endereco }}, {{ $estacionamento->cidade }}, {{ $estacionamento->estado }}</p>
            <p>Telefone: {{ $estacionamento->telefone_da_empresa }} | Email: {{ $estacionamento->email_da_empresa }}</p>
            <div class="report-title">
                <p>{{ $reportTitle }}</p>
                <p>Gerado em: {{ date('d/m/Y H:i') }}</p>
            </div>
        </header>

        <div class="content">
            <div class="occupancy-box">
                <h2>{{ $occupancy }}</h2>
                <p>Veículos registrados nos últimos 30 dias</p>
            </div>
        </div>

        <footer>
            <p class="footer-text">Relatório gerado automaticamente pelo sistema.</p>
            <p class="footer-small">Este relatório é gerado com base nas informações mais recentes disponíveis.</p>
        </footer>
    </div>

    <!-- Adiciona uma quebra de página para melhorar a impressão, se necessário -->
    <div class="page-break"></div>
</body>

</html>
