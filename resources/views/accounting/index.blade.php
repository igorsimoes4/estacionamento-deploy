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

    @if (session('error'))
        <div class="alert alert-danger mt-3">{{ session('error') }}</div>
    @endif

    <div class="row mt-3">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="theme-kpi theme-info">
                <div>
                    <p>Receita Operacional</p>
                    <h3>R$ {{ number_format((float) $stats['operational_revenue'], 2, ',', '.') }}</h3>
                </div>
                <i class="fas fa-parking"></i>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="theme-kpi theme-success">
                <div>
                    <p>Receita Manual</p>
                    <h3>R$ {{ number_format((float) $stats['manual_revenue'], 2, ',', '.') }}</h3>
                </div>
                <i class="fas fa-arrow-up"></i>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="theme-kpi theme-danger">
                <div>
                    <p>Despesas</p>
                    <h3>R$ {{ number_format((float) $stats['manual_expense'], 2, ',', '.') }}</h3>
                </div>
                <i class="fas fa-arrow-down"></i>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="theme-kpi {{ $stats['net_result'] >= 0 ? 'theme-success' : 'theme-danger' }}">
                <div>
                    <p>Resultado do Periodo</p>
                    <h3>R$ {{ number_format((float) $stats['net_result'], 2, ',', '.') }}</h3>
                </div>
                <i class="fas fa-balance-scale"></i>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4 mb-3">
            <div class="theme-panel h-100">
                <p>Saldo acumulado</p>
                <h4>R$ {{ number_format((float) $stats['all_time_balance'], 2, ',', '.') }}</h4>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="theme-panel h-100">
                <p>Saldo dos filtros</p>
                <h4>R$ {{ number_format((float) $stats['filtered_balance'], 2, ',', '.') }}</h4>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="theme-panel h-100">
                <p>Lancamentos filtrados</p>
                <h4>{{ number_format((int) $stats['filtered_count'], 0, ',', '.') }}</h4>
            </div>
        </div>
    </div>

    <div class="card theme-card mb-3">
        <div class="card-header"><strong>Filtros e Visao Analitica</strong></div>
        <div class="card-body">
            <form method="GET" action="{{ route('accounting.index') }}" class="mb-3">
                <div class="row">
                    <div class="col-md-2 mb-2">
                        <label class="small text-uppercase font-weight-bold">Periodo</label>
                        <input type="month" name="period" value="{{ $filters['period'] ?? $period }}" class="form-control">
                    </div>
                    <div class="col-md-2 mb-2">
                        <label class="small text-uppercase font-weight-bold">Tipo</label>
                        <select name="type" class="form-control">
                            <option value="all" {{ ($filters['type'] ?? $type) === 'all' ? 'selected' : '' }}>Todos</option>
                            <option value="receita" {{ ($filters['type'] ?? $type) === 'receita' ? 'selected' : '' }}>Receitas</option>
                            <option value="despesa" {{ ($filters['type'] ?? $type) === 'despesa' ? 'selected' : '' }}>Despesas</option>
                        </select>
                    </div>
                    <div class="col-md-2 mb-2">
                        <label class="small text-uppercase font-weight-bold">Pagamento</label>
                        <select name="payment_method" class="form-control">
                            @foreach ($paymentMethodOptions as $methodKey => $methodLabel)
                                <option value="{{ $methodKey }}" {{ ($filters['payment_method'] ?? '') === $methodKey ? 'selected' : '' }}>
                                    {{ $methodLabel }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="small text-uppercase font-weight-bold">Busca</label>
                        <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" class="form-control"
                            placeholder="Categoria, descricao ou observacao">
                    </div>
                    <div class="col-md-1 mb-2">
                        <label class="small text-uppercase font-weight-bold">Pagina</label>
                        <select name="per_page" class="form-control">
                            @foreach ([10, 20, 30, 50] as $pp)
                                <option value="{{ $pp }}" {{ (int) ($filters['per_page'] ?? 20) === $pp ? 'selected' : '' }}>
                                    {{ $pp }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 mb-2 d-flex align-items-end" style="gap: 8px;">
                        <button type="submit" class="btn btn-theme btn-sm flex-grow-1">
                            <i class="fas fa-filter mr-1"></i> Filtrar
                        </button>
                        <a href="{{ route('accounting.index') }}" class="btn btn-outline-secondary btn-sm">Limpar</a>
                    </div>
                </div>
            </form>

            <div class="row">
                <div class="col-lg-6 mb-3">
                    <div class="table-responsive border rounded">
                        <table class="table table-theme mb-0">
                            <thead>
                                <tr>
                                    <th colspan="3">Receita operacional por metodo</th>
                                </tr>
                                <tr>
                                    <th>Metodo</th>
                                    <th class="text-center">Transacoes</th>
                                    <th class="text-right">Valor</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($operationalByMethod as $method)
                                    <tr>
                                        <td>{{ $method->method }}</td>
                                        <td class="text-center">{{ $method->total }}</td>
                                        <td class="text-right text-success font-weight-bold">R$ {{ number_format((float) $method->amount, 2, ',', '.') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted">Sem recebimentos operacionais no periodo.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="col-lg-6 mb-3">
                    <div class="table-responsive border rounded">
                        <table class="table table-theme mb-0">
                            <thead>
                                <tr>
                                    <th colspan="3">Lancamentos contabeis por metodo</th>
                                </tr>
                                <tr>
                                    <th>Metodo</th>
                                    <th class="text-center">Lancamentos</th>
                                    <th class="text-right">Valor</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($manualByMethod as $method)
                                    <tr>
                                        <td>{{ $method->method }}</td>
                                        <td class="text-center">{{ $method->total }}</td>
                                        <td class="text-right font-weight-bold">R$ {{ number_format((float) $method->amount, 2, ',', '.') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted">Sem lancamentos contabeis para o filtro.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="table-responsive border rounded mb-1">
                <table class="table table-theme mb-0">
                    <thead>
                        <tr>
                            <th colspan="5">Top categorias (filtro atual)</th>
                        </tr>
                        <tr>
                            <th>Categoria</th>
                            <th class="text-center">Lancamentos</th>
                            <th class="text-right">Receitas</th>
                            <th class="text-right">Despesas</th>
                            <th class="text-right">Saldo</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($topCategories as $category)
                            <tr>
                                <td>{{ $category->category }}</td>
                                <td class="text-center">{{ $category->total }}</td>
                                <td class="text-right text-success font-weight-bold">R$ {{ number_format((float) $category->revenue_amount, 2, ',', '.') }}</td>
                                <td class="text-right text-danger font-weight-bold">R$ {{ number_format((float) $category->expense_amount, 2, ',', '.') }}</td>
                                <td class="text-right font-weight-bold {{ $category->balance_amount >= 0 ? 'text-success' : 'text-danger' }}">
                                    R$ {{ number_format((float) $category->balance_amount, 2, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted">Sem categorias para o filtro selecionado.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card report-card mt-1">
        <div class="card-header"><strong>Lancamentos do Periodo</strong></div>
        <div class="card-body">
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
                                <td>
                                    <div>{{ optional($entry->occurred_at)->format('d/m/Y') }}</div>
                                    <div class="small text-muted">#{{ $entry->id }}</div>
                                </td>
                                <td>
                                    <span class="badge {{ $entry->type === 'receita' ? 'badge-success' : 'badge-danger' }}">
                                        {{ ucfirst((string) $entry->type) }}
                                    </span>
                                </td>
                                <td>{{ $entry->category }}</td>
                                <td>
                                    <div>{{ $entry->description ?: '-' }}</div>
                                    @if (!empty($entry->notes))
                                        <div class="small text-muted text-truncate" style="max-width: 230px;" title="{{ $entry->notes }}">
                                            {{ $entry->notes }}
                                        </div>
                                    @endif
                                </td>
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

            <div class="d-flex flex-wrap justify-content-between align-items-center mt-2" style="gap: 10px;">
                <div class="small text-muted">
                    Mostrando <strong>{{ $entries->firstItem() ?? 0 }}</strong> a <strong>{{ $entries->lastItem() ?? 0 }}</strong>
                    de <strong>{{ number_format((int) $entries->total(), 0, ',', '.') }}</strong> lancamentos.
                </div>
                <div>
                    {{ $entries->onEachSide(1)->links('pagination::bootstrap-4') }}
                </div>
            </div>
        </div>
    </div>
@endsection
