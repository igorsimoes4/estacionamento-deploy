@extends('adminlte::page')

@section('title', 'Operação | Central de Notificações')

@section('content_header')
    <h1 class="m-0">Central de Notificações</h1>
@endsection

@section('content')
    @if (session('create'))
        <div class="alert alert-success">{{ session('create') }}</div>
    @endif

    <div class="card theme-card mb-3">
        <div class="card-body d-flex flex-wrap justify-content-between align-items-center" style="gap: 8px;">
            <form method="GET" action="{{ route('notifications.index') }}" class="d-flex" style="gap: 8px;">
                <select class="form-control" name="status" onchange="this.form.submit()">
                    <option value="all" {{ $status === 'all' ? 'selected' : '' }}>Todos</option>
                    <option value="queued" {{ $status === 'queued' ? 'selected' : '' }}>Na fila</option>
                    <option value="sent" {{ $status === 'sent' ? 'selected' : '' }}>Enviados</option>
                    <option value="retry" {{ $status === 'retry' ? 'selected' : '' }}>Retry</option>
                </select>
            </form>

            <form method="POST" action="{{ route('notifications.dispatch') }}">
                @csrf
                <button class="btn btn-theme" type="submit">Processar fila agora</button>
            </form>
        </div>
    </div>

    <div class="card theme-card overflow-hidden">
        <div class="table-responsive">
            <table class="table table-theme mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Canal</th>
                        <th>Destinatário</th>
                        <th>Título</th>
                        <th>Mensagem</th>
                        <th>Status</th>
                        <th>Agendada</th>
                        <th>Enviada</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($logs as $log)
                        <tr>
                            <td>{{ $log->id }}</td>
                            <td>{{ strtoupper($log->channel) }}</td>
                            <td>{{ $log->recipient }}</td>
                            <td>{{ $log->title ?: '-' }}</td>
                            <td>{{ \Illuminate\Support\Str::limit($log->message, 90) }}</td>
                            <td>
                                <span class="badge {{ $log->status === 'sent' ? 'badge-success' : ($log->status === 'retry' ? 'badge-warning' : 'badge-secondary') }}">
                                    {{ strtoupper($log->status) }}
                                </span>
                            </td>
                            <td>{{ optional($log->scheduled_at)->format('d/m/Y H:i') ?: '-' }}</td>
                            <td>{{ optional($log->sent_at)->format('d/m/Y H:i') ?: '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4">Nenhuma notificação encontrada.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white border-0">{{ $logs->onEachSide(1)->links('pagination::bootstrap-4') }}</div>
    </div>
@endsection
