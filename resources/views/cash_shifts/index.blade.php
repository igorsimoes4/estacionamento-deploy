@extends('adminlte::page')

@section('title', 'Operacao | Caixa por Turno')

@section('content_header')
    <div class="report-hero theme-fade-in">
        <div class="d-flex flex-wrap justify-content-between align-items-center" style="gap: 12px;">
            <div>
                <h1 class="m-0">Caixa por Turno</h1>
                <p>Abertura, movimentacoes, fechamento e controle de divergencias por operador.</p>
            </div>
            <span class="badge badge-light p-2">Atualizado em {{ now()->format('d/m/Y H:i') }}</span>
        </div>
    </div>
@endsection

@section('content')
    @if (session('create'))
        <div class="alert alert-success mt-3">{{ session('create') }}</div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger mt-3">{{ session('error') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger mt-3 mb-0">{{ $errors->first() }}</div>
    @endif

    <div class="row mt-3">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="theme-kpi theme-info">
                <div>
                    <p>Turnos Abertos</p>
                    <h3>{{ number_format((int) ($stats['open_count'] ?? 0), 0, ',', '.') }}</h3>
                </div>
                <i class="fas fa-lock-open"></i>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="theme-kpi theme-success">
                <div>
                    <p>Fechados Hoje</p>
                    <h3>{{ number_format((int) ($stats['closed_today'] ?? 0), 0, ',', '.') }}</h3>
                </div>
                <i class="fas fa-check-double"></i>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="theme-kpi theme-warn">
                <div>
                    <p>Movimentacoes Hoje</p>
                    <h3>{{ number_format((int) ($stats['movements_today'] ?? 0), 0, ',', '.') }}</h3>
                </div>
                <i class="fas fa-exchange-alt"></i>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="theme-kpi theme-danger">
                <div>
                    <p>Divergencia Hoje</p>
                    <h3>R$ {{ number_format(((int) ($stats['divergence_today_cents'] ?? 0)) / 100, 2, ',', '.') }}</h3>
                </div>
                <i class="fas fa-balance-scale"></i>
            </div>
        </div>
    </div>

    <div class="row align-items-start">
        <div class="col-lg-5 mb-3">
            <div class="card theme-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <strong>Operacao do Turno</strong>
                    @if ($openShift)
                        <span class="badge badge-success">EM ABERTO</span>
                    @else
                        <span class="badge badge-secondary">SEM TURNO ABERTO</span>
                    @endif
                </div>
                <div class="card-body">
                    @if (!$openShift)
                        <form method="POST" action="{{ route('cash-shifts.open') }}">
                            @csrf
                            <div class="form-group mb-2">
                                <label class="small text-uppercase font-weight-bold">Valor inicial (R$)</label>
                                <input class="form-control" type="number" step="0.01" min="0" name="opening_amount"
                                    value="{{ old('opening_amount') }}" required>
                            </div>
                            <div class="form-group mb-3">
                                <label class="small text-uppercase font-weight-bold">Observacoes</label>
                                <textarea class="form-control" name="notes" rows="3">{{ old('notes') }}</textarea>
                            </div>
                            <button class="btn btn-theme" type="submit">
                                <i class="fas fa-lock-open mr-1"></i> Abrir turno
                            </button>
                        </form>
                    @else
                        <div class="alert alert-info mb-3">
                            Turno: <strong>{{ $openShift->code }}</strong><br>
                            Operador: <strong>{{ $openShift->user->name ?? 'Nao informado' }}</strong><br>
                            Abertura: {{ optional($openShift->opened_at)->format('d/m/Y H:i') }}
                        </div>

                        <div class="row mb-3">
                            <div class="col-6 mb-2">
                                <div class="theme-panel h-100">
                                    <p>Abertura</p>
                                    <h4>R$ {{ number_format(((int) $openShift->opening_amount_cents) / 100, 2, ',', '.') }}</h4>
                                </div>
                            </div>
                            <div class="col-6 mb-2">
                                <div class="theme-panel h-100">
                                    <p>Esperado</p>
                                    <h4>R$ {{ number_format(((int) $openShift->expected_amount_cents) / 100, 2, ',', '.') }}</h4>
                                </div>
                            </div>
                            <div class="col-6 mb-2">
                                <div class="theme-panel h-100">
                                    <p>Entradas</p>
                                    <h4>R$ {{ number_format(((int) ($movementTotals['entries_cents'] ?? 0)) / 100, 2, ',', '.') }}</h4>
                                </div>
                            </div>
                            <div class="col-6 mb-2">
                                <div class="theme-panel h-100">
                                    <p>Saidas</p>
                                    <h4>R$ {{ number_format(((int) ($movementTotals['withdrawals_cents'] ?? 0)) / 100, 2, ',', '.') }}</h4>
                                </div>
                            </div>
                        </div>

                        <form method="POST" action="{{ route('cash-shifts.movement', $openShift) }}" class="border rounded p-2 mb-3">
                            @csrf
                            <h6 class="mb-2">Nova Movimentacao</h6>
                            <div class="form-row">
                                <div class="form-group col-md-4 mb-2">
                                    <label class="small">Tipo</label>
                                    <select class="form-control" name="type" required>
                                        @foreach (['venda' => 'Venda', 'entrada' => 'Entrada', 'reforco' => 'Reforco', 'sangria' => 'Sangria', 'saida' => 'Saida', 'estorno' => 'Estorno'] as $typeValue => $typeLabel)
                                            <option value="{{ $typeValue }}">{{ $typeLabel }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group col-md-4 mb-2">
                                    <label class="small">Metodo</label>
                                    <select class="form-control" name="method">
                                        @foreach (['dinheiro' => 'Dinheiro', 'pix' => 'Pix', 'boleto' => 'Boleto', 'cartao_credito' => 'Cartao credito', 'cartao_debito' => 'Cartao debito', 'transferencia' => 'Transferencia', 'outro' => 'Outro'] as $methodValue => $methodLabel)
                                            <option value="{{ $methodValue }}">{{ $methodLabel }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group col-md-4 mb-2">
                                    <label class="small">Valor (R$)</label>
                                    <input class="form-control" type="number" step="0.01" min="0.01" name="amount" required>
                                </div>
                            </div>
                            <div class="form-group mb-2">
                                <label class="small">Descricao</label>
                                <input class="form-control" type="text" name="description">
                            </div>
                            <button class="btn btn-theme btn-sm" type="submit">
                                <i class="fas fa-save mr-1"></i> Registrar
                            </button>
                        </form>

                        <form method="POST" action="{{ route('cash-shifts.close', $openShift) }}" class="border rounded p-2 mb-3">
                            @csrf
                            <h6 class="mb-2 text-danger">Fechamento do Turno</h6>
                            <div class="form-group mb-2">
                                <label class="small">Valor contado no fechamento (R$)</label>
                                <input class="form-control" type="number" step="0.01" min="0" name="counted_amount" required>
                            </div>
                            <div class="form-group mb-2">
                                <label class="small">Observacoes do fechamento</label>
                                <textarea class="form-control" name="notes" rows="2"></textarea>
                            </div>
                            <button class="btn btn-outline-danger btn-sm" type="submit">
                                <i class="fas fa-lock mr-1"></i> Fechar turno
                            </button>
                        </form>

                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <div class="card border h-100">
                                    <div class="card-header py-2"><strong class="small">Resumo por Tipo</strong></div>
                                    <div class="card-body py-2 px-2">
                                        @forelse ($movementTypeStats as $type => $item)
                                            <div class="d-flex justify-content-between small mb-1">
                                                <span>{{ strtoupper(str_replace('_', ' ', (string) $type)) }}</span>
                                                <span>{{ $item['count'] }} | R$ {{ number_format(((int) $item['amount_cents']) / 100, 2, ',', '.') }}</span>
                                            </div>
                                        @empty
                                            <div class="small text-muted">Sem movimentacoes.</div>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-2">
                                <div class="card border h-100">
                                    <div class="card-header py-2"><strong class="small">Resumo por Metodo</strong></div>
                                    <div class="card-body py-2 px-2">
                                        @forelse ($movementMethodStats as $method => $item)
                                            <div class="d-flex justify-content-between small mb-1">
                                                <span>{{ strtoupper(str_replace('_', ' ', (string) $method)) }}</span>
                                                <span>{{ $item['count'] }} | R$ {{ number_format(((int) $item['amount_cents']) / 100, 2, ',', '.') }}</span>
                                            </div>
                                        @empty
                                            <div class="small text-muted">Sem movimentacoes.</div>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive mt-2">
                            <table class="table table-theme table-sm mb-0">
                                <thead>
                                    <tr>
                                        <th>Horario</th>
                                        <th>Tipo</th>
                                        <th>Metodo</th>
                                        <th class="text-right">Valor</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($openShift->movements->take(12) as $movement)
                                        <tr>
                                            <td>{{ optional($movement->occurred_at)->format('H:i:s') }}</td>
                                            <td>{{ strtoupper(str_replace('_', ' ', (string) $movement->type)) }}</td>
                                            <td>{{ strtoupper(str_replace('_', ' ', (string) ($movement->method ?: '-'))) }}</td>
                                            <td class="text-right">R$ {{ number_format(((int) $movement->amount_cents) / 100, 2, ',', '.') }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted">Sem movimentacoes neste turno.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-7 mb-3">
            <div class="card theme-card">
                <div class="card-header d-flex flex-wrap justify-content-between align-items-center" style="gap: 8px;">
                    <strong>Historico de Turnos</strong>
                    <div class="d-flex flex-wrap" style="gap: 6px;">
                        <span class="report-meta">
                            <i class="fas fa-filter"></i>
                            Filtrados: <strong>{{ number_format((int) ($stats['filtered_total'] ?? 0), 0, ',', '.') }}</strong>
                        </span>
                        <span class="report-meta">
                            <i class="fas fa-list"></i>
                            Pagina: <strong>{{ number_format((int) $history->currentPage(), 0, ',', '.') }}</strong>
                        </span>
                    </div>
                </div>
                <div class="card-body border-bottom pb-2 bg-light">
                    <form method="GET" action="{{ route('cash-shifts.index') }}">
                        <div class="row">
                            <div class="col-md-3 mb-2">
                                <label class="small text-uppercase font-weight-bold">Status</label>
                                <select class="form-control" name="status">
                                    <option value="all" {{ ($filters['status'] ?? 'all') === 'all' ? 'selected' : '' }}>Todos</option>
                                    <option value="open" {{ ($filters['status'] ?? '') === 'open' ? 'selected' : '' }}>Abertos</option>
                                    <option value="closed" {{ ($filters['status'] ?? '') === 'closed' ? 'selected' : '' }}>Fechados</option>
                                </select>
                            </div>
                            <div class="col-md-3 mb-2">
                                <label class="small text-uppercase font-weight-bold">Data inicial</label>
                                <input class="form-control" type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}">
                            </div>
                            <div class="col-md-3 mb-2">
                                <label class="small text-uppercase font-weight-bold">Data final</label>
                                <input class="form-control" type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}">
                            </div>
                            <div class="col-md-3 mb-2">
                                <label class="small text-uppercase font-weight-bold">Por pagina</label>
                                <select class="form-control" name="per_page">
                                    @foreach ([10, 20, 30, 50] as $size)
                                        <option value="{{ $size }}" {{ (int) ($filters['per_page'] ?? 20) === $size ? 'selected' : '' }}>
                                            {{ $size }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-8 mb-2">
                                <label class="small text-uppercase font-weight-bold">Busca</label>
                                <input class="form-control" type="text" name="q" value="{{ $filters['q'] ?? '' }}"
                                    placeholder="Codigo do turno, operador ou observacoes">
                            </div>
                            <div class="col-md-4 mb-2 d-flex align-items-end" style="gap: 8px;">
                                <button class="btn btn-theme btn-sm" type="submit">Filtrar</button>
                                <a href="{{ route('cash-shifts.index') }}" class="btn btn-outline-secondary btn-sm">Limpar</a>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="table-responsive">
                    <table class="table table-theme mb-0">
                        <thead>
                            <tr>
                                <th>Turno</th>
                                <th>Operador</th>
                                <th>Janela</th>
                                <th class="text-right">Mov.</th>
                                <th class="text-right">Esperado</th>
                                <th class="text-right">Contado</th>
                                <th class="text-right">Divergencia</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($history as $shift)
                                @php
                                    $difference = (int) $shift->difference_amount_cents;
                                    $differenceClass = $difference === 0 ? 'badge-success' : ($difference > 0 ? 'badge-info' : 'badge-danger');
                                    $differenceSigned = ($difference > 0 ? '+' : '') . number_format($difference / 100, 2, ',', '.');
                                    $duration = '-';

                                    if ($shift->opened_at && $shift->closed_at) {
                                        $minutes = (int) $shift->opened_at->diffInMinutes($shift->closed_at);
                                        $hours = intdiv($minutes, 60);
                                        $mins = $minutes % 60;
                                        $duration = $hours . 'h ' . $mins . 'm';
                                    } elseif ($shift->status === 'open' && $shift->opened_at) {
                                        $duration = 'Em andamento';
                                    }
                                @endphp
                                <tr class="{{ $shift->status === 'open' ? 'table-warning' : '' }}">
                                    <td>
                                        <div class="font-weight-bold">{{ $shift->code }}</div>
                                        @if (!empty($shift->notes))
                                            <div class="small text-muted text-truncate" style="max-width: 190px;" title="{{ $shift->notes }}">
                                                {{ $shift->notes }}
                                            </div>
                                        @endif
                                    </td>
                                    <td>{{ $shift->user->name ?? '-' }}</td>
                                    <td>
                                        <div>{{ optional($shift->opened_at)->format('d/m/Y H:i') ?: '-' }}</div>
                                        <div class="small text-muted">Fim: {{ optional($shift->closed_at)->format('d/m/Y H:i') ?: '-' }}</div>
                                        <div class="small text-muted">Duracao: {{ $duration }}</div>
                                    </td>
                                    <td class="text-right">
                                        <span class="badge badge-light">{{ number_format((int) ($shift->movements_count ?? 0), 0, ',', '.') }}</span>
                                    </td>
                                    <td class="text-right">R$ {{ number_format(((int) $shift->expected_amount_cents) / 100, 2, ',', '.') }}</td>
                                    <td class="text-right">
                                        @if ($shift->counted_amount_cents !== null)
                                            R$ {{ number_format(((int) $shift->counted_amount_cents) / 100, 2, ',', '.') }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="text-right">
                                        <span class="badge {{ $differenceClass }}">
                                            R$ {{ $differenceSigned }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge {{ $shift->status === 'open' ? 'badge-primary' : 'badge-secondary' }}">
                                            {{ strtoupper((string) $shift->status) }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-4 text-muted">Nenhum turno encontrado para os filtros informados.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="card-footer bg-white border-0">
                    <div class="d-flex flex-wrap justify-content-between align-items-center" style="gap: 10px;">
                        <div class="small text-muted">
                            Mostrando <strong>{{ $history->firstItem() ?? 0 }}</strong> a <strong>{{ $history->lastItem() ?? 0 }}</strong>
                            de <strong>{{ number_format((int) ($stats['filtered_total'] ?? 0), 0, ',', '.') }}</strong> turnos.
                        </div>
                        <div>{{ $history->onEachSide(1)->links('pagination::bootstrap-4') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
