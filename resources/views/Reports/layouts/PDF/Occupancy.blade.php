<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>{{ $reportTitle }} - {{ $estacionamento->nome_da_empresa }}</title>
    <style>
        @page {
            margin: 12mm;
        }

        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            margin: 0;
            color: #1f2a37;
            font-size: 12px;
        }

        .wrapper {
            border: 1px solid #d9e5f0;
            border-radius: 8px;
            overflow: hidden;
        }

        .header {
            background: #0f6c74;
            color: #fff;
            padding: 16px 18px;
        }

        .header h1 {
            margin: 0;
            font-size: 20px;
        }

        .header p {
            margin: 4px 0 0;
            font-size: 11px;
            opacity: .95;
        }

        .title {
            padding: 12px 18px;
            border-bottom: 1px solid #e3edf6;
            background: #f7fbff;
        }

        .title h2 {
            margin: 0;
            font-size: 16px;
            color: #123f5b;
        }

        .title p {
            margin: 5px 0 0;
            font-size: 11px;
            color: #607182;
        }

        .content {
            padding: 24px 18px;
            text-align: center;
        }

        .value-box {
            display: inline-block;
            min-width: 220px;
            border: 1px solid #cfe2f2;
            border-radius: 12px;
            padding: 20px 24px;
            background: linear-gradient(160deg, #ffffff 0%, #f3f9ff 100%);
        }

        .value-box h3 {
            margin: 0;
            font-size: 44px;
            line-height: 1;
            color: #0f6c74;
        }

        .value-box p {
            margin: 8px 0 0;
            color: #526273;
            font-size: 13px;
        }

        .footer {
            border-top: 1px solid #e3edf6;
            background: #fbfdff;
            padding: 10px 18px;
            font-size: 10px;
            color: #607182;
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="wrapper">
        <div class="header">
            <h1>{{ $estacionamento->nome_da_empresa }}</h1>
            <p>{{ $estacionamento->endereco }}, {{ $estacionamento->cidade }} - {{ $estacionamento->estado }}</p>
            <p>Telefone: {{ $estacionamento->telefone_da_empresa }} | Email: {{ $estacionamento->email_da_empresa }}</p>
        </div>

        <div class="title">
            <h2>{{ $reportTitle }}</h2>
            <p>Gerado em: {{ date('d/m/Y H:i') }}</p>
        </div>

        <div class="content">
            <div class="value-box">
                <h3>{{ $occupancy }}</h3>
                <p>Veiculos ocupando vagas no momento</p>
            </div>
        </div>

        <div class="footer">
            Relatorio gerado automaticamente pelo sistema.
        </div>
    </div>
</body>

</html>
