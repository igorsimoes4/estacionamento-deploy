<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Documento PDF</title>
    <!-- Link para o CSS do Bootstrap -->
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            margin: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="my-4">Detalhes do Veículo</h1>
        <table class="table table-bordered">
            <thead class="thead-light">
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
                        <td>{{ $car->created_at }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</body>
</html>
