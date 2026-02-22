@extends('adminlte::page')

@section('title', 'Operação | Financeiro')

@section('content_header')
    <h1 class="m-0">Cobrança Recorrente, Conciliação e Fiscal</h1>
@endsection

@section('content')
    @if (session('create'))
        <div class="alert alert-success">{{ session('create') }}</div>
    @endif

    <div class="row mb-3">
        <div class="col-md-3 mb-2">
            <div class="theme-stat-card h-100">
                <p class="theme-stat-label">Transações pendentes</p>
                <h3>{{ number_format((int) $summary['pending_transactions'], 0, ',', '.') }}</h3>
            </div>
        </div>
        <div class="col-md-3 mb-2">
            <div class="theme-stat-card h-100">
                <p class="theme-stat-label">Ciclos em atraso</p>
                <h3>{{ number_format((int) $summary['overdue_cycles'], 0, ',', '.') }}</h3>
            </div>
        </div>
        <div class="col-md-3 mb-2">
            <div class="theme-stat-card h-100">
                <p class="theme-stat-label">Recebido hoje</p>
                <h3>R$ {{ number_format(((int) $summary['paid_today']) / 100, 2, ',', '.') }}</h3>
            </div>
        </div>
        <div class="col-md-3 mb-2">
            <div class="theme-stat-card h-100">
                <p class="theme-stat-label">Fiscal pendente</p>
                <h3>{{ number_format((int) $summary['fiscal_pending'], 0, ',', '.') }}</h3>
            </div>
        </div>
    </div>

    <div class="card theme-card mb-3">
        <div class="card-body d-flex flex-wrap" style="gap: 10px;">
            <form method="POST" action="{{ route('operations.finance.recurring') }}">
                @csrf
                <button class="btn btn-theme" type="submit">Rodar cobrança recorrente</button>
            </form>

            <form method="POST" action="{{ route('operations.finance.delinquency') }}">
                @csrf
                <button class="btn btn-outline-warning" type="submit">Processar inadimplência</button>
            </form>

            <form method="POST" action="{{ route('operations.finance.fiscal') }}" class="d-flex" style="gap: 8px;">
                @csrf
                <input type="number" name="transaction_id" class="form-control" placeholder="ID transação" required>
                <select class="form-control" name="document_type">
                    <option value="nfce">NFC-e</option>
                    <option value="nfse">NFS-e</option>
                </select>
                <button class="btn btn-outline-primary" type="submit">Emitir fiscal</button>
            </form>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6 mb-3">
            <div class="card theme-card h-100 overflow-hidden">
                <div class="card-header"><strong>Transações</strong></div>
                <div class="table-responsive">
                    <table class="table table-theme mb-0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Ref</th>
                                <th>Método</th>
                                <th>Status</th>
                                <th>Valor</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($transactions as $transaction)
                                <tr>
                                    <td>{{ $transaction->id }}</td>
                                    <td>{{ $transaction->reference }}</td>
                                    <td>{{ strtoupper($transaction->method) }}</td>
                                    <td><span class="badge badge-info">{{ strtoupper($transaction->status) }}</span></td>
                                    <td>R$ {{ number_format(((int) $transaction->amount_cents) / 100, 2, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="card-footer bg-white border-0">{{ $transactions->onEachSide(1)->links('pagination::bootstrap-4') }}</div>
            </div>
        </div>

        <div class="col-lg-6 mb-3">
            <div class="card theme-card h-100 overflow-hidden">
                <div class="card-header"><strong>Ciclos de Mensalidade</strong></div>
                <div class="table-responsive">
                    <table class="table table-theme mb-0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Ref</th>
                                <th>Competência</th>
                                <th>Vencimento</th>
                                <th>Status</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($billingCycles as $cycle)
                                <tr>
                                    <td>{{ $cycle->id }}</td>
                                    <td>{{ $cycle->reference }}</td>
                                    <td>{{ $cycle->competency }}</td>
                                    <td>{{ optional($cycle->due_date)->format('d/m/Y') }}</td>
                                    <td><span class="badge badge-secondary">{{ strtoupper($cycle->status) }}</span></td>
                                    <td>R$ {{ number_format(((int) $cycle->total_amount_cents) / 100, 2, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="card-footer bg-white border-0">{{ $billingCycles->onEachSide(1)->links('pagination::bootstrap-4') }}</div>
            </div>
        </div>

        <div class="col-12">
            <div class="card theme-card overflow-hidden">
                <div class="card-header"><strong>Documentos Fiscais</strong></div>
                <div class="table-responsive">
                    <table class="table table-theme mb-0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Tipo</th>
                                <th>Número</th>
                                <th>Status</th>
                                <th>Valor</th>
                                <th>Emitido em</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($fiscalDocuments as $doc)
                                <tr>
                                    <td>{{ $doc->id }}</td>
                                    <td>{{ strtoupper($doc->type) }}</td>
                                    <td>{{ $doc->number ?: '-' }}</td>
                                    <td><span class="badge badge-info">{{ strtoupper($doc->status) }}</span></td>
                                    <td>R$ {{ number_format(((int) $doc->total_cents) / 100, 2, ',', '.') }}</td>
                                    <td>{{ optional($doc->issued_at)->format('d/m/Y H:i') ?: '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="card-footer bg-white border-0">{{ $fiscalDocuments->onEachSide(1)->links('pagination::bootstrap-4') }}</div>
            </div>
        </div>
    </div>
@endsection
