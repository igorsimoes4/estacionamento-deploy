@extends('adminlte::page')

@section('title', 'Auditoria | Sistema')

@section('content_header')
    <div class="d-flex flex-wrap justify-content-between align-items-center" style="gap: 10px;">
        <div>
            <h1 class="m-0">Auditoria do Sistema</h1>
            <p class="text-muted m-0">Historico de eventos, requisicoes e alteracoes de dados.</p>
        </div>
        <span class="badge badge-light p-2">Atualizado em {{ now()->format('d/m/Y H:i') }}</span>
    </div>
@endsection

@section('content')
    <div class="row mb-3">
        <div class="col-md-3 mb-2">
            <div class="theme-stat-card h-100">
                <p class="theme-stat-label">Total de Logs</p>
                <h3>{{ number_format((int) $metrics['total'], 0, ',', '.') }}</h3>
            </div>
        </div>
        <div class="col-md-3 mb-2">
            <div class="theme-stat-card h-100">
                <p class="theme-stat-label">Hoje</p>
                <h3>{{ number_format((int) $metrics['today'], 0, ',', '.') }}</h3>
            </div>
        </div>
        <div class="col-md-3 mb-2">
            <div class="theme-stat-card h-100">
                <p class="theme-stat-label">Erros / Alertas</p>
                <h3>{{ number_format((int) $metrics['errors'], 0, ',', '.') }}</h3>
            </div>
        </div>
        <div class="col-md-3 mb-2">
            <div class="theme-stat-card h-100">
                <p class="theme-stat-label">Mudancas de Modelo</p>
                <h3>{{ number_format((int) $metrics['model_changes'], 0, ',', '.') }}</h3>
            </div>
        </div>
    </div>

    <div class="card theme-card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('audit.index') }}">
                <div class="row">
                    <div class="col-md-3 mb-2">
                        <label class="small text-uppercase font-weight-bold">Evento</label>
                        <input type="text" name="event" class="form-control" value="{{ $filters['event'] }}"
                            placeholder="ex: model.updated">
                    </div>
                    <div class="col-md-2 mb-2">
                        <label class="small text-uppercase font-weight-bold">Nivel</label>
                        <select name="level" class="form-control">
                            <option value="">Todos</option>
                            @foreach ($levels as $level)
                                <option value="{{ $level }}" {{ $filters['level'] === $level ? 'selected' : '' }}>
                                    {{ strtoupper($level) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 mb-2">
                        <label class="small text-uppercase font-weight-bold">Status HTTP</label>
                        <input type="number" name="status_code" class="form-control" value="{{ $filters['status_code'] }}"
                            placeholder="200, 422, 500">
                    </div>
                    <div class="col-md-2 mb-2">
                        <label class="small text-uppercase font-weight-bold">ID Ator</label>
                        <input type="number" name="actor_id" class="form-control" value="{{ $filters['actor_id'] }}"
                            placeholder="ID usuario">
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="small text-uppercase font-weight-bold">Caminho</label>
                        <input type="text" name="path" class="form-control" value="{{ $filters['path'] }}"
                            placeholder="painel/cars">
                    </div>
                </div>

                <div class="row align-items-end">
                    <div class="col-md-2 mb-2">
                        <label class="small text-uppercase font-weight-bold">Data inicial</label>
                        <input type="date" name="from" class="form-control" value="{{ $filters['from'] }}">
                    </div>
                    <div class="col-md-2 mb-2">
                        <label class="small text-uppercase font-weight-bold">Data final</label>
                        <input type="date" name="to" class="form-control" value="{{ $filters['to'] }}">
                    </div>
                    <div class="col-md-2 mb-2">
                        <label class="small text-uppercase font-weight-bold">Por pagina</label>
                        <select name="per_page" class="form-control" onchange="this.form.submit()">
                            @foreach ([10, 20, 50, 100] as $pp)
                                <option value="{{ $pp }}"
                                    {{ (int) $filters['per_page'] === $pp ? 'selected' : '' }}>
                                    {{ $pp }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 mb-2 text-md-right">
                        <a href="{{ route('audit.export.csv', request()->query()) }}" class="btn btn-outline-success">
                            <i class="fas fa-file-csv mr-1"></i> Exportar CSV
                        </a>
                        <a href="{{ route('audit.export.pdf', request()->query()) }}" target="_blank"
                            class="btn btn-outline-danger">
                            <i class="fas fa-file-pdf mr-1"></i> Exportar PDF
                        </a>
                        <a href="{{ route('audit.index') }}" class="btn btn-outline-secondary">Limpar filtros</a>
                        <button type="submit" class="btn btn-theme">Filtrar logs</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card theme-card overflow-hidden">
        <div class="table-responsive">
            <table class="table table-theme mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Evento</th>
                        <th>Nivel</th>
                        <th>Descricao</th>
                        <th>Ator</th>
                        <th>Rota</th>
                        <th>Status</th>
                        <th>Data</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($logs as $log)
                        <tr>
                            <td>{{ $log->id }}</td>
                            <td>
                                <span class="badge badge-info">{{ $log->event }}</span>
                            </td>
                            <td>
                                <span
                                    class="badge {{ in_array($log->level, ['error', 'critical', 'alert', 'emergency'], true) ? 'badge-danger' : 'badge-secondary' }}">
                                    {{ strtoupper((string) $log->level) }}
                                </span>
                            </td>
                            <td>
                                <div class="font-weight-bold">{{ $log->description ?: '-' }}</div>
                                @if (!empty($log->subject_type))
                                    <div class="small text-muted">
                                        {{ class_basename((string) $log->subject_type) }} #{{ $log->subject_id ?: '-' }}
                                    </div>
                                @endif
                            </td>
                            <td>
                                <div>{{ $log->actor_id ? '#' . $log->actor_id : '-' }}</div>
                                <div class="small text-muted">
                                    {{ $log->actor_type ? class_basename((string) $log->actor_type) : '-' }}</div>
                            </td>
                            <td>
                                <div class="small text-muted">{{ $log->request_method ?: '-' }}</div>
                                <div>{{ $log->request_path ?: '-' }}</div>
                            </td>
                            <td>{{ $log->status_code ?: '-' }}</td>
                            <td>{{ optional($log->created_at)->format('d/m/Y H:i:s') }}</td>
                        </tr>
                        @if (!empty($log->new_values) || !empty($log->old_values))
                            <tr>
                                <td colspan="8" class="bg-light">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="small text-uppercase font-weight-bold text-muted">Antes</div>
                                            <pre class="mb-0 small">{{ json_encode($log->old_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="small text-uppercase font-weight-bold text-muted">Depois</div>
                                            <pre class="mb-0 small">{{ json_encode($log->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endif
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4">Nenhum log encontrado para os filtros informados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white border-0">
            <div class="d-flex flex-wrap justify-content-between align-items-center" style="gap: 10px;">
                <div class="small text-muted">
                    Mostrando <strong>{{ $pageInfo['from'] }}</strong> a <strong>{{ $pageInfo['to'] }}</strong>
                    de <strong>{{ number_format((int) $pageInfo['total'], 0, ',', '.') }}</strong> registros.
                    Pagina <strong>{{ $pageInfo['current'] }}</strong> de <strong>{{ $pageInfo['last'] }}</strong>.
                </div>
                <div>
                    {{ $logs->onEachSide(1)->links('pagination::bootstrap-4') }}
                </div>
                @if ($logs->hasPages())
                    <div class="btn-group btn-group-sm" role="group" aria-label="Navegacao rapida">
                        <a href="{{ $logs->url(1) }}"
                            class="btn btn-outline-secondary {{ $logs->onFirstPage() ? 'disabled' : '' }}">
                            Primeira
                        </a>
                        <a href="{{ $logs->previousPageUrl() ?: '#' }}"
                            class="btn btn-outline-secondary {{ $logs->onFirstPage() ? 'disabled' : '' }}">
                            Anterior
                        </a>
                        <a href="{{ $logs->nextPageUrl() ?: '#' }}"
                            class="btn btn-outline-secondary {{ !$logs->hasMorePages() ? 'disabled' : '' }}">
                            Proxima
                        </a>
                        <a href="{{ $logs->url($logs->lastPage()) }}"
                            class="btn btn-outline-secondary {{ !$logs->hasMorePages() ? 'disabled' : '' }}">
                            Ultima
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
