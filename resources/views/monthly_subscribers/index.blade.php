@extends('adminlte::page')

@section('adminlte_css')
    <link rel="icon" type="image/png" href="{{ asset('img/LogoEstacionamento.png') }}">
    <link rel="stylesheet" href="{{ asset('fontawesome-free/css/all.min.css') }}" />
    @parent
@endsection

@section('content')
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Mensalistas</h5>
                    <div class="d-flex" style="gap: 8px;">
                        <a href="{{ route('monthly-access.login') }}" class="btn btn-outline-primary" target="_blank" rel="noopener">
                            <i class="fas fa-user-lock"></i> Portal Mensalista
                        </a>
                        <a href="{{ route('monthly-subscribers.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Novo Mensalista
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    @if (session('warning'))
                        <div class="alert alert-warning">{{ session('warning') }}</div>
                    @endif

                    <form method="GET" action="{{ route('monthly-subscribers.index') }}" class="mb-3">
                        <div class="row">
                            <div class="col-md-4">
                                <select name="status" class="form-control" onchange="this.form.submit()">
                                    <option value="todos" {{ ($status ?? 'todos') === 'todos' ? 'selected' : '' }}>Todos</option>
                                    <option value="ativos" {{ ($status ?? '') === 'ativos' ? 'selected' : '' }}>Apenas ativos</option>
                                    <option value="vencendo" {{ ($status ?? '') === 'vencendo' ? 'selected' : '' }}>Vencendo em 7 dias</option>
                                </select>
                            </div>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Nome</th>
                                    <th>CPF</th>
                                    <th>Telefone</th>
                                    <th>Placa</th>
                                    <th>Tipo</th>
                                    <th>Status</th>
                                    <th>Acesso</th>
                                    <th>Recorrência</th>
                                    <th>Inadimplência</th>
                                    <th>Vencimento</th>
                                    <th>Acoes</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($subscribers as $subscriber)
                                    <tr>
                                        <td>{{ $subscriber->name }}</td>
                                        <td>{{ $subscriber->cpf }}</td>
                                        <td>{{ $subscriber->phone }}</td>
                                        <td>{{ $subscriber->vehicle_plate }}</td>
                                        <td>{{ ucfirst($subscriber->vehicle_type) }}</td>
                                        <td>
                                            <span class="badge {{ $subscriber->is_active ? 'bg-success' : 'bg-danger' }}">
                                                {{ $subscriber->is_active ? 'Ativo' : 'Inativo' }}
                                            </span>
                                        </td>
                                        <td>
                                            @if (!$subscriber->access_enabled)
                                                <span class="badge bg-secondary">Bloqueado</span>
                                            @elseif (!empty($subscriber->access_password))
                                                <span class="badge bg-success">Liberado</span>
                                            @else
                                                <span class="badge bg-warning text-dark">Pendente</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge {{ $subscriber->auto_renew_enabled ? 'bg-success' : 'bg-secondary' }}">
                                                {{ $subscriber->auto_renew_enabled ? 'Auto' : 'Manual' }}
                                            </span>
                                            <div class="small text-muted">
                                                {{ $subscriber->recurring_payment_method ? strtoupper(str_replace('_', ' ', $subscriber->recurring_payment_method)) : 'BOLETO' }}
                                            </div>
                                        </td>
                                        <td>
                                            @if ($subscriber->delinquent_since)
                                                <span class="badge bg-danger">Em atraso</span>
                                                <div class="small text-muted">desde {{ optional($subscriber->delinquent_since)->format('d/m/Y') }}</div>
                                            @else
                                                <span class="badge bg-success">Em dia</span>
                                            @endif
                                        </td>
                                        <td>{{ optional($subscriber->end_date)->format('d/m/Y') }}</td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="{{ route('monthly-subscribers.show', $subscriber) }}" class="btn btn-sm btn-info">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('monthly-subscribers.edit', $subscriber) }}" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="{{ route('monthly-subscribers.destroy', $subscriber) }}" method="POST" class="d-inline"
                                                    onsubmit="return confirm('Tem certeza que deseja excluir este mensalista?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="11" class="text-center">Nenhum mensalista cadastrado.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
