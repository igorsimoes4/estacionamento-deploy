@extends('adminlte::page')

@section('title', 'Painel | Contabilidade')

@section('content_header')
    <div class="report-hero theme-fade-in">
        <div class="d-flex flex-wrap justify-content-between align-items-center" style="gap: 12px;">
            <div>
                <h1 class="m-0">Contabilidade</h1>
                <p>Controle de receitas, despesas e resultado financeiro do estacionamento.</p>
            </div>
            <a href="{{ route('accounting.create') }}" class="btn btn-light btn-sm">
                <i class="fas fa-plus mr-1"></i> Novo lancamento
            </a>
        </div>
    </div>
@endsection

@section('content')
    @if (session('success'))
        <div class="alert alert-success mt-3">{{ session('success') }}</div>
    @endif

    <div class="row mt-3">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="theme-kpi theme-info">
                <div>
                    <p>Receita Operacional</p>
                    <h3>R$ {{ number_format($stats['operational_revenue'], 2, ',', '.') }}</h3>
                </div>
                <i class="fas fa-parking"></i>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="theme-kpi theme-success">
                <div>
                    <p>Receita Manual</p>
                    <h3>R$ {{ number_format($stats['manual_revenue'], 2, ',', '.') }}</h3>
                </div>
                <i class="fas fa-arrow-up"></i>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="theme-kpi theme-danger">
                <div>
                    <p>Despesas</p>
                    <h3>R$ {{ number_format($stats['manual_expense'], 2, ',', '.') }}</h3>
                </div>
                <i class="fas fa-arrow-down"></i>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="theme-kpi {{ $stats['net_result'] >= 0 ? 'theme-success' : 'theme-danger' }}">
                <div>
                    <p>Resultado do Periodo</p>
                    <h3>R$ {{ number_format($stats['net_result'], 2, ',', '.') }}</h3>
                </div>
                <i class="fas fa-balance-scale"></i>
            </div>
        </div>
    </div>

    <div class="card report-card mt-1">
        <div class="card-body">
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-3" style="gap: 10px;">
                <form method="GET" action="{{ route('accounting.index') }}" class="d-flex flex-wrap" style="gap: 8px;">
                    <input type="month" name="period" value="{{ $period }}" class="form-control" style="max-width: 220px;">
                    <select name="type" class="form-control" style="max-width: 180px;">
                        <option value="all" {{ $type === 'all' ? 'selected' : '' }}>Todos</option>
                        <option value="receita" {{ $type === 'receita' ? 'selected' : '' }}>Receitas</option>
                        <option value="despesa" {{ $type === 'despesa' ? 'selected' : '' }}>Despesas</option>
                    </select>
                    <button type="submit" class="btn btn-theme btn-sm">
                        <i class="fas fa-filter mr-1"></i> Filtrar
                    </button>
                </form>

                <span class="report-meta">
                    <i class="fas fa-wallet"></i>
                    Saldo acumulado: <strong>R$ {{ number_format($stats['all_time_balance'], 2, ',', '.') }}</strong>
                </span>
            </div>

            <div class="table-responsive mb-4">
                <table class="table table-theme">
                    <thead>
                        <tr>
                            <th>Receita operacional por metodo</th>
                            <th class="text-center">Transacoes</th>
                            <th class="text-right">Valor</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($operationalByMethod as $method)
                            <tr>
                                <td>{{ $method->method }}</td>
                                <td class="text-center">{{ $method->total }}</td>
                                <td class="text-right text-success font-weight-bold">R$ {{ number_format($method->amount, 2, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted">Sem recebimentos operacionais no periodo.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="table-responsive">
                <table class="table table-theme">
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Tipo</th>
                            <th>Categoria</th>
                            <th>Descricao</th>
                            <th>Pagamento</th>
                            <th class="text-right">Valor</th>
                            <th class="text-center">Acoes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($entries as $entry)
                            <tr>
                                <td>{{ optional($entry->occurred_at)->format('d/m/Y') }}</td>
                                <td>
                                    <span class="badge {{ $entry->type === 'receita' ? 'badge-success' : 'badge-danger' }}">
                                        {{ ucfirst($entry->type) }}
                                    </span>
                                </td>
                                <td>{{ $entry->category }}</td>
                                <td>{{ $entry->description ?: '-' }}</td>
                                <td>{{ \App\Models\Cars::paymentMethodLabel($entry->payment_method) }}</td>
                                <td class="text-right font-weight-bold {{ $entry->type === 'receita' ? 'text-success' : 'text-danger' }}">
                                    R$ {{ number_format((float) $entry->amount, 2, ',', '.') }}
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('accounting.edit', $entry) }}" class="btn btn-sm btn-outline-primary" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('accounting.destroy', $entry) }}" method="POST" class="d-inline"
                                        onsubmit="return confirm('Excluir este lancamento?')">
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
                                <td colspan="7" class="text-center text-muted py-4">Nenhum lancamento encontrado para o filtro selecionado.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-2">
                {{ $entries->links() }}
            </div>
        </div>
    </div>
@endsection
