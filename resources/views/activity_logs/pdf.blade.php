<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Relatorio de Auditoria</title>
    <style>
        * { box-sizing: border-box; font-family: DejaVu Sans, Arial, sans-serif; }
        body { margin: 0; color: #1f2937; font-size: 10px; }
        .sheet { padding: 14px; }
        .header {
            border: 1px solid #d1deeb;
            border-radius: 8px;
            padding: 10px 12px;
            margin-bottom: 10px;
        }
        .title { margin: 0; font-size: 15px; color: #0f4454; }
        .subtitle { margin: 4px 0 0; color: #4b5563; font-size: 10px; }
        .filters { margin: 8px 0 10px; color: #4b5563; font-size: 9px; }
        table { width: 100%; border-collapse: collapse; }
        th, td {
            border: 1px solid #d1deeb;
            padding: 5px 6px;
            vertical-align: top;
            word-break: break-word;
        }
        th {
            background: #eef5fb;
            text-transform: uppercase;
            font-size: 9px;
            color: #1f4058;
        }
        .muted { color: #6b7280; }
        .nowrap { white-space: nowrap; }
    </style>
</head>
<body>
    <div class="sheet">
        <div class="header">
            <h1 class="title">Relatorio de Auditoria do Sistema</h1>
            <p class="subtitle">Gerado em {{ $generatedAt->format('d/m/Y H:i:s') }}</p>
        </div>

        <div class="filters">
            Filtros:
            Evento={{ $filters['event'] ?: 'todos' }};
            Nivel={{ $filters['level'] ?: 'todos' }};
            Caminho={{ $filters['path'] ?: 'todos' }};
            Ator={{ $filters['actor_id'] ?: 'todos' }};
            Status={{ $filters['status_code'] ?: 'todos' }};
            De={{ $filters['from'] ?: '-' }};
            Ate={{ $filters['to'] ?: '-' }}.
        </div>

        <table>
            <thead>
                <tr>
                    <th width="5%">ID</th>
                    <th width="12%">Evento</th>
                    <th width="7%">Nivel</th>
                    <th width="26%">Descricao</th>
                    <th width="10%">Ator</th>
                    <th width="16%">Rota</th>
                    <th width="6%">Status</th>
                    <th width="18%">Data</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($logs as $log)
                    <tr>
                        <td class="nowrap">{{ $log->id }}</td>
                        <td>{{ $log->event }}</td>
                        <td>{{ strtoupper((string) $log->level) }}</td>
                        <td>
                            {{ $log->description ?: '-' }}
                            @if (!empty($log->subject_type))
                                <div class="muted">{{ class_basename((string) $log->subject_type) }} #{{ $log->subject_id ?: '-' }}</div>
                            @endif
                        </td>
                        <td>
                            {{ $log->actor_id ? '#' . $log->actor_id : '-' }}
                            <div class="muted">{{ $log->actor_type ? class_basename((string) $log->actor_type) : '-' }}</div>
                        </td>
                        <td>
                            {{ $log->request_method ?: '-' }}
                            <div class="muted">{{ $log->request_path ?: '-' }}</div>
                        </td>
                        <td>{{ $log->status_code ?: '-' }}</td>
                        <td class="nowrap">{{ optional($log->created_at)->format('d/m/Y H:i:s') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="muted">Nenhum registro encontrado para os filtros informados.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</body>
</html>

