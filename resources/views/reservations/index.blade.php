@extends('adminlte::page')

@section('title', 'Operação | Reservas')

@section('content_header')
    <div class="d-flex flex-wrap justify-content-between align-items-center" style="gap: 10px;">
        <div>
            <h1 class="m-0">Reserva Antecipada de Vagas</h1>
            <p class="text-muted m-0">Site/app com pré-pagamento e check-in integrado.</p>
        </div>
    </div>
@endsection

@section('content')
    @if (session('create'))
        <div class="alert alert-success">{{ session('create') }}</div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card theme-card mb-3">
        <div class="card-body">
            <form method="POST" action="{{ route('reservations.store') }}">
                @csrf
                <div class="row">
                    <div class="col-md-3 mb-2">
                        <label class="small text-uppercase font-weight-bold">Nome</label>
                        <input class="form-control" type="text" name="customer_name" required>
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="small text-uppercase font-weight-bold">E-mail</label>
                        <input class="form-control" type="email" name="customer_email">
                    </div>
                    <div class="col-md-2 mb-2">
                        <label class="small text-uppercase font-weight-bold">Telefone</label>
                        <input class="form-control" type="text" name="customer_phone">
                    </div>
                    <div class="col-md-2 mb-2">
                        <label class="small text-uppercase font-weight-bold">Placa</label>
                        <input class="form-control" type="text" name="vehicle_plate" required>
                    </div>
                    <div class="col-md-2 mb-2">
                        <label class="small text-uppercase font-weight-bold">Tipo</label>
                        <select class="form-control" name="vehicle_type" required>
                            <option value="carro">Carro</option>
                            <option value="moto">Moto</option>
                            <option value="caminhonete">Caminhonete</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="small text-uppercase font-weight-bold">Modelo do veículo</label>
                        <input class="form-control" type="text" name="vehicle_model">
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="small text-uppercase font-weight-bold">Início</label>
                        <input class="form-control" type="datetime-local" name="starts_at" required>
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="small text-uppercase font-weight-bold">Fim</label>
                        <input class="form-control" type="datetime-local" name="ends_at" required>
                    </div>
                    <div class="col-md-2 mb-2">
                        <label class="small text-uppercase font-weight-bold">Pré-pagamento</label>
                        <select class="form-control" name="prepaid">
                            <option value="0">Não</option>
                            <option value="1">Sim</option>
                        </select>
                    </div>
                    <div class="col-md-2 mb-2">
                        <label class="small text-uppercase font-weight-bold">Método</label>
                        <select class="form-control" name="payment_method">
                            <option value="pix">Pix</option>
                            <option value="boleto">Boleto</option>
                            <option value="cartao_credito">Cartão crédito</option>
                            <option value="cartao_debito">Cartão débito</option>
                            <option value="dinheiro">Dinheiro</option>
                        </select>
                    </div>
                    <div class="col-md-12 mb-2">
                        <label class="small text-uppercase font-weight-bold">Observações</label>
                        <input class="form-control" type="text" name="notes">
                    </div>
                </div>

                <div class="text-right">
                    <button type="submit" class="btn btn-theme">Criar reserva</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card theme-card overflow-hidden">
        <div class="table-responsive">
            <table class="table table-theme mb-0">
                <thead>
                    <tr>
                        <th>Referência</th>
                        <th>Cliente</th>
                        <th>Placa</th>
                        <th>Período</th>
                        <th>Setor/Vaga</th>
                        <th>Status</th>
                        <th>Pagamento</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($reservations as $reservation)
                        <tr>
                            <td>{{ $reservation->reference }}</td>
                            <td>
                                <div class="font-weight-bold">{{ $reservation->customer_name }}</div>
                                <div class="small text-muted">{{ $reservation->customer_phone ?: '-' }}</div>
                            </td>
                            <td>{{ $reservation->vehicle_plate }}</td>
                            <td>
                                <div>{{ optional($reservation->starts_at)->format('d/m/Y H:i') }}</div>
                                <div class="small text-muted">até {{ optional($reservation->ends_at)->format('d/m/Y H:i') }}</div>
                            </td>
                            <td>
                                <div>{{ $reservation->sector->name ?? '-' }}</div>
                                <div class="small text-muted">{{ $reservation->spot->code ?? '-' }}</div>
                            </td>
                            <td>
                                <span class="badge badge-info">{{ strtoupper($reservation->status) }}</span>
                            </td>
                            <td>
                                <span class="badge {{ $reservation->payment_status === 'paid' ? 'badge-success' : 'badge-warning' }}">
                                    {{ strtoupper($reservation->payment_status) }}
                                </span>
                            </td>
                            <td>
                                <div class="d-flex" style="gap: 6px;">
                                    @if (in_array($reservation->status, ['pending', 'confirmed'], true))
                                        <form method="POST" action="{{ route('reservations.checkin', $reservation) }}">
                                            @csrf
                                            <button class="btn btn-sm btn-outline-success" type="submit">Check-in</button>
                                        </form>
                                        <form method="POST" action="{{ route('reservations.cancel', $reservation) }}">
                                            @csrf
                                            <button class="btn btn-sm btn-outline-danger" type="submit">Cancelar</button>
                                        </form>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4">Nenhuma reserva encontrada.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white border-0">{{ $reservations->onEachSide(1)->links('pagination::bootstrap-4') }}</div>
    </div>
@endsection
