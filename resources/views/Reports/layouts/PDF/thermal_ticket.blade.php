<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket de Estacionamento</title>
    <style>
        @page {
            margin: 4mm;
        }

        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            margin: 0;
            padding: 0;
            font-size: 10px;
            color: #111;
        }

        .ticket {
            border: 1px solid #111;
            padding: 8px;
        }

        .center {
            text-align: center;
        }

        .line {
            border-top: 1px dashed #111;
            margin: 8px 0;
        }

        p {
            margin: 2px 0;
        }

        .title {
            font-size: 12px;
            font-weight: 700;
            letter-spacing: .2px;
            margin-bottom: 4px;
        }

        .muted {
            color: #333;
        }

        .strong {
            font-weight: 700;
        }
    </style>
</head>

<body>
    <div class="ticket">
        <div class="center">
            <p class="title">{{ $empresa }}</p>
            <p><span class="strong">Endereco:</span> {{ $endereco }}</p>
            <p><span class="strong">CNPJ:</span> {{ $cnpj_cpf }}</p>
            <p><span class="strong">Telefone:</span> {{ $telefone }}</p>
            <p><span class="strong">Data:</span> {{ $data }} <span class="strong">Hora:</span> {{ $hora }}</p>
        </div>

        <div class="line"></div>

        <p><span class="strong">Veiculo:</span> {{ $tipo_car }}</p>
        <p><span class="strong">Placa:</span> {{ $placa }}</p>
        <p><span class="strong">Entrada:</span> {{ $data }} {{ $hora }}</p>

        <div class="line"></div>

        <div class="center muted">
            <p>Guarde este ticket consigo.</p>
            <p>Nao deixe no interior do veiculo.</p>
            <p>O veiculo sera entregue ao portador.</p>
            <p>Seg a Sex: 08:00 as 19:30</p>
            <p>Sabado: 08:00 as 18:00</p>
        </div>
    </div>
</body>

</html>
