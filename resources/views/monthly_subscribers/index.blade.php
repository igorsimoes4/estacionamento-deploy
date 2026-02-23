@extends('adminlte::page')

@section('title', 'Painel | Mensalistas')

@section('content_header')
    <div class="report-hero theme-fade-in">
        <div class="d-flex flex-wrap justify-content-between align-items-center" style="gap: 12px;">
            <div>
                <h1 class="m-0">Mensalistas</h1>
                <p>Gestao de contratos, acesso ao portal, recorrencia e inadimplencia.</p>
            </div>
            <div class="d-flex flex-wrap" style="gap: 8px;">
                <a href="{{ route('monthly-access.login') }}" class="btn btn-light btn-sm" target="_blank" rel="noopener">
                    <i class="fas fa-user-lock mr-1"></i> Portal Mensalista
                </a>
                <a href="{{ route('monthly-subscribers.create') }}" class="btn btn-theme btn-sm">
                    <i class="fas fa-plus mr-1"></i> Novo Mensalista
                </a>
            </div>
        </div>
    </div>
@endsection

@section('content')
    @if (session('success'))
        <div class="alert alert-success mt-3">{{ session('success') }}</div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger mt-3">{{ session('error') }}</div>
    @endif

    @if (session('warning'))
        <div class="alert alert-warning mt-3">{{ session('warning') }}</div>
    @endif

    <div class="row mt-3">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="theme-kpi theme-info">
                <div>
                    <p>Total</p>
                    <h3>{{ number_format((int) ($stats['total'] ?? 0), 0, ',', '.') }}</h3>
                </div>
                <i class="fas fa-users"></i>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="theme-kpi theme-success">
                <div>
                    <p>Ativos</p>
                    <h3>{{ number_format((int) ($stats['active'] ?? 0), 0, ',', '.') }}</h3>
                </div>
                <i class="fas fa-user-check"></i>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="theme-kpi theme-warn">
                <div>
                    <p>Vencendo (7 dias)</p>
                    <h3>{{ number_format((int) ($stats['expiring'] ?? 0), 0, ',', '.') }}</h3>
                </div>
                <i class="fas fa-clock"></i>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="theme-kpi theme-danger">
                <div>
                    <p>Inadimplentes</p>
                    <h3>{{ number_format((int) ($stats['overdue'] ?? 0), 0, ',', '.') }}</h3>
                </div>
                <i class="fas fa-exclamation-triangle"></i>
            </div>
        </div>
    </div>

    <div class="card report-card">
        <div class="card-body">
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-3" style="gap: 10px;">
                <span class="report-meta">
                    <i class="fas fa-wallet"></i>
                    Receita recorrente ativa: <strong>R$ {{ number_format((float) ($stats['mrr'] ?? 0), 2, ',', '.') }}</strong>
                </span>
            </div>

            <form method="GET" action="{{ route('monthly-subscribers.index') }}" class="mb-3">
                <div class="row">
                    <div class="col-md-4 mb-2">
                        <label class="small text-uppercase font-weight-bold">Busca</label>
                        <input type="text" name="q" class="form-control"
                            value="{{ $filters['q'] ?? '' }}"
                            placeholder="Nome, email, CPF ou placa">
                    </div>
                    <div class="col-md-2 mb-2">
                        <label class="small text-uppercase font-weight-bold">Status</label>
                        <select name="status" class="form-control">
                            <option value="todos" {{ ($filters['status'] ?? 'todos') === 'todos' ? 'selected' : '' }}>Todos</option>
                            <option value="ativos" {{ ($filters['status'] ?? '') === 'ativos' ? 'selected' : '' }}>Ativos</option>
                            <option value="vencendo" {{ ($filters['status'] ?? '') === 'vencendo' ? 'selected' : '' }}>Vencendo</option>
                            <option value="inadimplentes" {{ ($filters['status'] ?? '') === 'inadimplentes' ? 'selected' : '' }}>Inadimplentes</option>
                            <option value="inativos" {{ ($filters['status'] ?? '') === 'inativos' ? 'selected' : '' }}>Inativos</option>
                        </select>
                    </div>
                    <div class="col-md-2 mb-2">
                        <label class="small text-uppercase font-weight-bold">Tipo</label>
                        <select name="vehicle_type" class="form-control">
                            <option value="">Todos</option>
                            <option value="carro" {{ ($filters['vehicle_type'] ?? '') === 'carro' ? 'selected' : '' }}>Carro</option>
                            <option value="moto" {{ ($filters['vehicle_type'] ?? '') === 'moto' ? 'selected' : '' }}>Moto</option>
                            <option value="caminhonete" {{ ($filters['vehicle_type'] ?? '') === 'caminhonete' ? 'selected' : '' }}>Caminhonete</option>
                        </select>
                    </div>
                    <div class="col-md-2 mb-2">
                        <label class="small text-uppercase font-weight-bold">Por pagina</label>
                        <select name="per_page" class="form-control">
                            @foreach ([10, 15, 20, 50] as $size)
                                <option value="{{ $size }}" {{ (int) ($filters['per_page'] ?? 15) === $size ? 'selected' : '' }}>
                                    {{ $size }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 mb-2 d-flex align-items-end" style="gap: 8px;">
                        <button type="submit" class="btn btn-theme btn-sm flex-grow-1">Filtrar</button>
                        <a href="{{ route('monthly-subscribers.index') }}" class="btn btn-outline-secondary btn-sm">Limpar</a>
                    </div>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-theme mb-0">
                    <thead>
                        <tr>
                            <th>Mensalista</th>
                            <th>Veiculo</th>
                            <th>Recorrencia</th>
                            <th>Status</th>
                            <th>Acesso</th>
                            <th>Vencimento</th>
                            <th class="text-center">Acoes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($subscribers as $subscriber)
                            @php
                                $daysToExpire = $subscriber->end_date
                                    ? now()->startOfDay()->diffInDays($subscriber->end_date->copy()->startOfDay(), false)
                                    : null;
                            @endphp
                            <tr>
                                <td>
                                    <div class="font-weight-bold">{{ $subscriber->name }}</div>
                                    <div class="small text-muted">
                                        CPF: {{ $subscriber->cpf }} | Tel: {{ $subscriber->phone }}
                                    </div>
                                    <div class="small text-muted">{{ $subscriber->email ?: 'Sem e-mail' }}</div>
                                </td>
                                <td>
                                    <div class="font-weight-bold">{{ strtoupper((string) $subscriber->vehicle_plate) }}</div>
                                    <div class="small text-muted">
                                        {{ ucfirst((string) $subscriber->vehicle_type) }}
                                        @if (!empty($subscriber->vehicle_model))
                                            | {{ $subscriber->vehicle_model }}
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <span class="badge {{ $subscriber->auto_renew_enabled ? 'badge-success' : 'badge-secondary' }}">
                                        {{ $subscriber->auto_renew_enabled ? 'Automatica' : 'Manual' }}
                                    </span>
                                    <div class="small text-muted mt-1">
                                        {{ $subscriber->recurring_payment_method ? strtoupper(str_replace('_', ' ', $subscriber->recurring_payment_method)) : 'BOLETO' }}
                                    </div>
                                    <div class="small font-weight-bold mt-1">
                                        R$ {{ number_format((float) $subscriber->monthly_fee, 2, ',', '.') }}
                                    </div>
                                </td>
                                <td>
                                    <span class="badge {{ $subscriber->is_active ? 'badge-success' : 'badge-danger' }}">
                                        {{ $subscriber->is_active ? 'Ativo' : 'Inativo' }}
                                    </span>
                                    <div class="mt-1">
                                        @if ($subscriber->delinquent_since)
                                            <span class="badge badge-danger">Em atraso</span>
                                        @else
                                            <span class="badge badge-success">Em dia</span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    @if (!$subscriber->access_enabled)
                                        <span class="badge badge-secondary">Bloqueado</span>
                                    @elseif ($subscriber->hasPortalAccessConfigured())
                                        <span class="badge badge-success">Liberado</span>
                                    @else
                                        <span class="badge badge-warning">Pendente</span>
                                    @endif
                                    @if ($subscriber->access_last_login_at)
                                        <div class="small text-muted mt-1">
                                            Ultimo login: {{ $subscriber->access_last_login_at->format('d/m/Y H:i') }}
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <div class="font-weight-bold">{{ optional($subscriber->end_date)->format('d/m/Y') ?: '-' }}</div>
                                    @if ($daysToExpire !== null)
                                        <div class="small {{ $daysToExpire < 0 ? 'text-danger' : 'text-muted' }}">
                                            @if ($daysToExpire < 0)
                                                Venceu ha {{ abs($daysToExpire) }} dia(s)
                                            @elseif ($daysToExpire === 0)
                                                Vence hoje
                                            @else
                                                Faltam {{ $daysToExpire }} dia(s)
                                            @endif
                                        </div>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('monthly-subscribers.show', $subscriber) }}" class="btn btn-sm btn-outline-info" title="Detalhes">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('monthly-subscribers.edit', $subscriber) }}" class="btn btn-sm btn-outline-primary" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('monthly-subscribers.destroy', $subscriber) }}" method="POST" class="d-inline"
                                        onsubmit="return confirm('Tem certeza que deseja excluir este mensalista?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Excluir">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">Nenhum mensalista encontrado para os filtros informados.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3 d-flex flex-wrap justify-content-between align-items-center" style="gap: 10px;">
                <div class="small text-muted">
                    Mostrando <strong>{{ $subscribers->firstItem() ?? 0 }}</strong> a <strong>{{ $subscribers->lastItem() ?? 0 }}</strong>
                    de <strong>{{ number_format((int) $subscribers->total(), 0, ',', '.') }}</strong> mensalistas.
                </div>
                <div>
                    {{ $subscribers->onEachSide(1)->links('pagination::bootstrap-4') }}
                </div>
            </div>
        </div>
    </div>
@endsection
