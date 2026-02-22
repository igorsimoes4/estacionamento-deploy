@extends('adminlte::page')

@section('title', 'Painel | Home')

@section('content_header')
    <div class="d-flex flex-wrap justify-content-between align-items-center" style="gap: 12px;">
        <div>
            <h1 class="m-0">Painel de Controle</h1>
            <p class="text-muted m-0">Acompanhamento operacional em tempo real do estacionamento.</p>
        </div>
        <div class="d-flex align-items-center" style="gap: 8px;">
            <form method="GET" action="{{ route('home') }}" class="d-flex align-items-center" style="gap: 8px;">
                <label class="small text-uppercase font-weight-bold m-0 text-muted">Periodo</label>
                <select name="period" class="form-control form-control-sm" onchange="this.form.submit()">
                    @foreach ($data['period_options'] as $periodKey => $periodLabel)
                        <option value="{{ $periodKey }}" {{ $data['selected_period'] === $periodKey ? 'selected' : '' }}>
                            {{ $periodLabel }}
                        </option>
                    @endforeach
                </select>
            </form>
            <span class="badge badge-light p-2">{{ $data['period_label'] }}</span>
        </div>
    </div>
@endsection

@section('content')
    @livewire('dashboard-stats')

    <div class="row mt-2">
        <div class="col-md-3 mb-3">
            <div class="theme-stat-card">
                <p class="theme-stat-label">Entradas ({{ $data['period_label'] }})</p>
                <h3>{{ number_format((int) $data['period_entries'], 0, ',', '.') }}</h3>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="theme-stat-card">
                <p class="theme-stat-label">Saidas ({{ $data['period_label'] }})</p>
                <h3>{{ number_format((int) $data['period_exits'], 0, ',', '.') }}</h3>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="theme-stat-card">
                <p class="theme-stat-label">Receita ({{ $data['period_label'] }})</p>
                <h3>R$ {{ number_format((float) $data['period_revenue'], 2, ',', '.') }}</h3>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="theme-stat-card">
                <p class="theme-stat-label">Ticket medio</p>
                <h3>R$ {{ number_format((float) $data['period_ticket_avg'], 2, ',', '.') }}</h3>
            </div>
        </div>
    </div>

    <div class="row mt-2">
        <div class="col-lg-3 col-md-6 mb-3">
            <a href="{{ route('cars.index') }}" class="theme-quick-link">
                <p>Operacao</p>
                <h5>Monitorar Patio</h5>
            </a>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <a href="{{ route('monthly-subscribers.index') }}" class="theme-quick-link">
                <p>Assinaturas</p>
                <h5>Gerenciar Mensalistas</h5>
            </a>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <a href="{{ route('reports.index') }}" class="theme-quick-link">
                <p>Inteligencia</p>
                <h5>Emitir Relatorios</h5>
            </a>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <a href="{{ route('accounting.index') }}" class="theme-quick-link">
                <p>Financeiro</p>
                <h5>Contabilidade</h5>
            </a>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <a href="{{ route('audit.index') }}" class="theme-quick-link">
                <p>Governanca</p>
                <h5>Auditoria de Eventos</h5>
            </a>
        </div>
    </div>

    <div class="row mt-1">
        <div class="col-lg-4 mb-3">
            <div class="theme-stat-card h-100">
                <p class="theme-stat-label">Ocupacao Atual</p>
                <h3>{{ $data['occupancy_total_percent'] }}%</h3>
                <div class="progress mt-2" style="height: 10px;">
                    <div class="progress-bar bg-info" role="progressbar" style="width: {{ $data['occupancy_total_percent'] }}%"></div>
                </div>
                <div class="small text-muted mt-2">
                    {{ $data['occupancy_total_used'] }} de {{ $data['occupancy_total_capacity'] }} vagas operacionais ocupadas.
                </div>
            </div>
        </div>
        <div class="col-lg-4 mb-3">
            <div class="theme-stat-card h-100">
                <p class="theme-stat-label">Mensalistas Vencendo (7 dias)</p>
                <h3>{{ $data['expiring_subscribers_count'] }}</h3>
                <div class="small text-muted mt-2">
                    Inadimplentes/ja vencidos: <strong>{{ $data['overdue_subscribers_count'] }}</strong>
                </div>
            </div>
        </div>
        <div class="col-lg-4 mb-3">
            <div class="theme-stat-card h-100">
                <p class="theme-stat-label">Ocupacao por Tipo</p>
                @foreach ($data['occupancy_by_type'] as $type => $occupancy)
                    <div class="small mt-2">
                        <strong>{{ ucfirst($type) }}:</strong> {{ $occupancy['used'] }}/{{ $occupancy['capacity'] }} ({{ $occupancy['percent'] }}%)
                    </div>
                    <div class="progress mt-1" style="height: 7px;">
                        <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $occupancy['percent'] }}%"></div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="row mt-2">
        <div class="col-lg-6 mb-3">
            <div class="card theme-card h-100">
                <div class="card-header border-0">
                    <h5 class="mb-0">Mensalistas com vencimento proximo</h5>
                </div>
                <div class="card-body pt-2">
                    @forelse ($data['expiring_subscribers'] as $subscriber)
                        <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                            <div>
                                <div class="font-weight-bold">{{ $subscriber->name }}</div>
                                <div class="small text-muted">Placa {{ $subscriber->vehicle_plate }}</div>
                            </div>
                            <div class="text-right">
                                <div class="small text-muted">Vence em</div>
                                <div class="font-weight-bold">{{ optional($subscriber->end_date)->format('d/m/Y') }}</div>
                            </div>
                        </div>
                    @empty
                        <p class="text-muted mb-0">Nenhum mensalista com vencimento nos proximos 10 dias.</p>
                    @endforelse
                </div>
            </div>
        </div>
        <div class="col-lg-6 mb-3">
            <div class="card theme-card h-100">
                <div class="card-header border-0 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Ultimos eventos do sistema ({{ $data['period_label'] }})</h5>
                    <a href="{{ route('audit.index') }}" class="btn btn-outline-primary btn-sm">Abrir auditoria</a>
                </div>
                <div class="card-body pt-2">
                    @forelse ($data['recent_activity'] as $activity)
                        <div class="border-bottom py-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="badge badge-info">{{ $activity->event }}</span>
                                <span class="small text-muted">{{ optional($activity->created_at)->format('d/m H:i') }}</span>
                            </div>
                            <div class="small mt-1">{{ $activity->description ?: ($activity->request_path ?: '-') }}</div>
                        </div>
                    @empty
                        <p class="text-muted mb-0">Sem eventos recentes.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-1">
        <div class="col-12">
            <div class="theme-panel">
                <p>Dica de fluxo</p>
                <h4>Use a tela de veiculos para finalizar, imprimir ticket e filtrar status em tempo real sem recarregar a pagina.</h4>
            </div>
        </div>
    </div>
@endsection
