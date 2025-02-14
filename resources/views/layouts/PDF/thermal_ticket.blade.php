<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket de Estacionamento</title>
    <!-- Bootstrap CSS -->
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            margin: 0;
            padding: 10px;
            font-size: 10px;
        }
        .container {
            padding: 5px;
        }
        .line {
            width: 100%;
            border-top: 1px dashed black;
            margin: 10px 0;
        }
        .text-center {
            text-align: center;
        }
        .mb-1 {
            margin-bottom: 5px !important;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="text-center mb-3">
            <p class="mb-1">{{ $empresa }}</p>
            <p class="mb-1"><strong>Endereço: </strong>{{ $endereco }}</p>
            <p class="mb-1"><strong>CNPJ: </strong>{{ $cnpj_cpf }}</p>
            <p class="mb-1"><strong>Telefone: </strong>{{ $telefone }}</p>
            <p class="mb-1">Data: {{ $data }} Hora: {{ $hora }}</p>
        </div>
        <div class="line"></div>
        <p class="mb-1"><strong>Veículo:</strong> {{ $tipo_car }}</p>
        <p class="mb-1"><strong>Placa:</strong> {{ $placa }}</p>
        <p class="mb-1"><strong>Entrada:</strong> {{ $data }} Hora: {{ $hora }}</p>
        <div class="line"></div>
        <div class="text-center">
            <p class="mb-1">Guarde este ticket consigo.</p>
            <p class="mb-1">Não deixe-o no interior do veículo.</p>
            <p class="mb-1">O veículo será entregue ao portador.</p>
            <p class="mb-1">Seg a Sex das 08:00 as 19:30</p>
            <p class="mb-1">Sábado das 08:00 as 18:00</p>
        </div>
    </div>
</body>
</html>
