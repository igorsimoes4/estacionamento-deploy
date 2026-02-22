@extends('adminlte::page')

@section('title', 'Operação | Caixa por Turno')

@section('content_header')
    <h1 class="m-0">Controle de Caixa por Turno</h1>
@endsection

@section('content')
    @if (session('create'))
        <div class="alert alert-success">{{ session('create') }}</div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="row">
        <div class="col-lg-5 mb-3">
            <div class="card theme-card h-100">
                <div class="card-header"><strong>Abertura de Caixa</strong></div>
                <div class="card-body">
                    @if (!$openShift)
                        <form method="POST" action="{{ route('cash-shifts.open') }}">
                            @csrf
                            <div class="form-group">
                                <label>Valor inicial (R$)</label>
                                <input class="form-control" type="number" step="0.01" min="0" name="opening_amount" required>
                            </div>
                            <div class="form-group">
                                <label>Observações</label>
                                <textarea class="form-control" name="notes" rows="3"></textarea>
                            </div>
                            <button class="btn btn-theme" type="submit">Abrir turno</button>
                        </form>
                    @else
                        <div class="alert alert-info mb-2">
                            Turno aberto: <strong>{{ $openShift->code }}</strong><br>
                            Início: {{ optional($openShift->opened_at)->format('d/m/Y H:i') }}
                        </div>
                        <p class="mb-1">Esperado: <strong>R$ {{ number_format(((int) $openShift->expected_amount_cents) / 100, 2, ',', '.') }}</strong></p>
                        <p class="mb-3">Abertura: <strong>R$ {{ number_format(((int) $openShift->opening_amount_cents) / 100, 2, ',', '.') }}</strong></p>

                        <form method="POST" action="{{ route('cash-shifts.close', $openShift) }}" class="border rounded p-2 mb-3">
                            @csrf
                            <div class="form-group mb-2">
                                <label>Valor contado no fechamento (R$)</label>
                                <input class="form-control" type="number" step="0.01" min="0" name="counted_amount" required>
                            </div>
                            <div class="form-group mb-2">
                                <label>Observações do fechamento</label>
                                <textarea class="form-control" name="notes" rows="2"></textarea>
                            </div>
                            <button class="btn btn-outline-danger btn-sm" type="submit">Fechar turno</button>
                        </form>

                        <form method="POST" action="{{ route('cash-shifts.movement', $openShift) }}" class="border rounded p-2">
                            @csrf
                            <h6>Movimentação</h6>
                            <div class="form-row">
                                <div class="form-group col-md-4">
                                    <label>Tipo</label>
                                    <select class="form-control" name="type" required>
                                        <option value="venda">Venda</option>
                                        <option value="entrada">Entrada</option>
                                        <option value="reforco">Reforço</option>
                                        <option value="sangria">Sangria</option>
                                        <option value="saida">Saída</option>
                                        <option value="estorno">Estorno</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-4">
                                    <label>Método</label>
                                    <select class="form-control" name="method">
                                        <option value="dinheiro">Dinheiro</option>
                                        <option value="pix">Pix</option>
                                        <option value="boleto">Boleto</option>
                                        <option value="cartao_credito">Cartão crédito</option>
                                        <option value="cartao_debito">Cartão débito</option>
                                        <option value="transferencia">Transferência</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-4">
                                    <label>Valor (R$)</label>
                                    <input class="form-control" type="number" step="0.01" min="0.01" name="amount" required>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Descrição</label>
                                <input class="form-control" type="text" name="description">
                            </div>
                            <button class="btn btn-theme btn-sm" type="submit">Registrar</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-7 mb-3">
            <div class="card theme-card h-100">
                <div class="card-header"><strong>Histórico de Turnos</strong></div>
                <div class="table-responsive">
                    <table class="table table-theme mb-0">
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Abertura</th>
                                <th>Fechamento</th>
                                <th>Esperado</th>
                                <th>Contado</th>
                                <th>Divergência</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($history as $shift)
                                <tr>
                                    <td>{{ $shift->code }}</td>
                                    <td>{{ optional($shift->opened_at)->format('d/m/Y H:i') }}</td>
                                    <td>{{ optional($shift->closed_at)->format('d/m/Y H:i') ?: '-' }}</td>
                                    <td>R$ {{ number_format(((int) $shift->expected_amount_cents) / 100, 2, ',', '.') }}</td>
                                    <td>R$ {{ number_format(((int) ($shift->counted_amount_cents ?? 0)) / 100, 2, ',', '.') }}</td>
                                    <td>
                                        <span class="badge {{ ((int) $shift->difference_amount_cents) === 0 ? 'badge-success' : 'badge-warning' }}">
                                            R$ {{ number_format(((int) $shift->difference_amount_cents) / 100, 2, ',', '.') }}
                                        </span>
                                    </td>
                                    <td><span class="badge badge-secondary">{{ strtoupper($shift->status) }}</span></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4">Nenhum turno registrado.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer bg-white border-0">{{ $history->onEachSide(1)->links('pagination::bootstrap-4') }}</div>
            </div>
        </div>
    </div>
@endsection
