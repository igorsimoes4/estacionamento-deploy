<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $reportTitle }} - {{ $estacionamento->nome_da_empresa }}</title>
    <style>
        @page {
            margin: 12mm;
        }

        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            color: #1f2a37;
            font-size: 11px;
            margin: 0;
        }

        .report {
            border: 1px solid #d9e5f0;
            border-radius: 8px;
            overflow: hidden;
        }

        .header {
            padding: 16px 18px;
            background: #0f6c74;
            color: #fff;
        }

        .header h2 {
            margin: 0;
            font-size: 20px;
            font-weight: 700;
        }

        .header p {
            margin: 4px 0 0;
            font-size: 11px;
            opacity: .95;
        }

        .meta {
            padding: 12px 18px;
            border-bottom: 1px solid #e4edf5;
            background: #f7fbff;
        }

        .meta h3 {
            margin: 0;
            font-size: 16px;
            color: #123f5b;
        }

        .meta p {
            margin: 5px 0 0;
            font-size: 11px;
            color: #5b6b7b;
        }

        .content {
            padding: 14px 18px 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #dbe6f1;
            padding: 7px;
            text-align: center;
            vertical-align: middle;
        }

        th {
            background: #e9f2fa;
            color: #204766;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: .3px;
        }

        tbody tr:nth-child(even) {
            background: #f9fcff;
        }

        .empty {
            text-align: center;
            padding: 16px;
            color: #5b6b7b;
            border: 1px solid #dbe6f1;
        }

        .footer {
            padding: 10px 18px;
            border-top: 1px solid #e4edf5;
            color: #607182;
            font-size: 10px;
            background: #fbfdff;
        }
    </style>
</head>

<body>
    <div class="report">
        <div class="header">
            <h2>{{ $estacionamento->nome_da_empresa }}</h2>
            <p>{{ $estacionamento->endereco }}, {{ $estacionamento->cidade }} - {{ $estacionamento->estado }}</p>
            <p>Telefone: {{ $estacionamento->telefone_da_empresa }} | Email: {{ $estacionamento->email_da_empresa }}</p>
        </div>

        <div class="meta">
            <h3>{{ $reportTitle }}</h3>
            <p>Gerado em: {{ date('d/m/Y H:i') }}</p>
        </div>

        <div class="content">
            @if ($rows->isEmpty())
                <div class="empty">Nenhum registro encontrado para o periodo selecionado.</div>
            @else
                <table>
                    <thead>
                        <tr>
                            @foreach ($columns as $column)
                                <th>{{ $column['label'] }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($rows as $row)
                            <tr>
                                @foreach ($columns as $column)
                                    <td>{{ $row[$column['key']] ?? '-' }}</td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>

        <div class="footer">
            Relatorio gerado automaticamente pelo sistema.
        </div>
    </div>
</body>

</html>
