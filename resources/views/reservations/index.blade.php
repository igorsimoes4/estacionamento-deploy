@extends('adminlte::page')

@section('title', 'Operacao | Reservas')

@section('content_header')
    <div class="report-hero theme-fade-in">
        <div class="d-flex flex-wrap justify-content-between align-items-center" style="gap: 12px;">
            <div>
                <h1 class="m-0">Reservas de Vagas</h1>
                <p>Gestao de reservas antecipadas com pre-pagamento e check-in integrado.</p>
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
                    <p>Total</p>
                    <h3>{{ number_format((int) ($stats['total'] ?? 0), 0, ',', '.') }}</h3>
                </div>
                <i class="fas fa-calendar-alt"></i>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="theme-kpi theme-success">
                <div>
                    <p>Ativas</p>
                    <h3>{{ number_format((int) ($stats['active'] ?? 0), 0, ',', '.') }}</h3>
                </div>
                <i class="fas fa-check-circle"></i>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="theme-kpi theme-warn">
                <div>
                    <p>Proximas</p>
                    <h3>{{ number_format((int) ($stats['upcoming'] ?? 0), 0, ',', '.') }}</h3>
                </div>
                <i class="fas fa-hourglass-half"></i>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="theme-kpi theme-danger">
                <div>
                    <p>Check-in / Canceladas</p>
                    <h3>{{ number_format((int) ($stats['checked_in'] ?? 0), 0, ',', '.') }} / {{ number_format((int) ($stats['cancelled'] ?? 0), 0, ',', '.') }}</h3>
                </div>
                <i class="fas fa-route"></i>
            </div>
        </div>
    </div>

    <div class="card theme-card mb-3">
        <div class="card-body">
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-3" style="gap: 10px;">
                <span class="report-meta">
                    <i class="fas fa-wallet"></i>
                    Valor estimado em reservas ativas:
                    <strong>R$ {{ number_format(((int) ($stats['estimated_active_cents'] ?? 0)) / 100, 2, ',', '.') }}</strong>
                </span>
                <span class="report-meta">
                    <i class="fas fa-filter"></i>
                    Resultado atual: <strong>{{ number_format((int) ($stats['filtered_total'] ?? 0), 0, ',', '.') }}</strong>
                </span>
            </div>

            <form method="GET" action="{{ route('reservations.index') }}">
                <div class="row">
                    <div class="col-md-3 mb-2">
                        <label class="small text-uppercase font-weight-bold">Busca</label>
                        <input class="form-control" type="text" name="q" value="{{ $filters['q'] ?? '' }}"
                            placeholder="Ref, cliente, email, telefone, placa">
                    </div>
                    <div class="col-md-2 mb-2">
                        <label class="small text-uppercase font-weight-bold">Status</label>
                        <select class="form-control" name="status">
                            @foreach ($statusOptions as $value => $label)
                                <option value="{{ $value }}" {{ ($filters['status'] ?? 'active') === $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 mb-2">
                        <label class="small text-uppercase font-weight-bold">Tipo</label>
                        <select class="form-control" name="vehicle_type">
                            <option value="">Todos</option>
                            <option value="carro" {{ ($filters['vehicle_type'] ?? '') === 'carro' ? 'selected' : '' }}>Carro</option>
                            <option value="moto" {{ ($filters['vehicle_type'] ?? '') === 'moto' ? 'selected' : '' }}>Moto</option>
                            <option value="caminhonete" {{ ($filters['vehicle_type'] ?? '') === 'caminhonete' ? 'selected' : '' }}>Caminhonete</option>
                        </select>
                    </div>
                    <div class="col-md-2 mb-2">
                        <label class="small text-uppercase font-weight-bold">Pagamento</label>
                        <select class="form-control" name="payment_status">
                            <option value="">Todos</option>
                            @foreach (['pending' => 'Pendente', 'paid' => 'Pago', 'failed' => 'Falhou', 'cancelled' => 'Cancelado', 'refunded' => 'Estornado'] as $value => $label)
                                <option value="{{ $value }}" {{ ($filters['payment_status'] ?? '') === $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="small text-uppercase font-weight-bold">Setor</label>
                        <select class="form-control" name="sector_id">
                            <option value="0">Todos os setores</option>
                            @foreach ($sectors as $sectorFilter)
                                <option value="{{ $sectorFilter->id }}" {{ (int) ($filters['sector_id'] ?? 0) === (int) $sectorFilter->id ? 'selected' : '' }}>
                                    {{ $sectorFilter->name }} ({{ $sectorFilter->code }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 mb-2">
                        <label class="small text-uppercase font-weight-bold">Data inicial</label>
                        <input class="form-control" type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}">
                    </div>
                    <div class="col-md-2 mb-2">
                        <label class="small text-uppercase font-weight-bold">Data final</label>
                        <input class="form-control" type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}">
                    </div>
                    <div class="col-md-2 mb-2">
                        <label class="small text-uppercase font-weight-bold">Por pagina</label>
                        <select class="form-control" name="per_page">
                            @foreach ([10, 20, 30, 50] as $size)
                                <option value="{{ $size }}" {{ (int) ($filters['per_page'] ?? 20) === $size ? 'selected' : '' }}>
                                    {{ $size }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 mb-2 d-flex align-items-end" style="gap: 8px;">
                        <button type="submit" class="btn btn-theme btn-sm">Filtrar reservas</button>
                        <a href="{{ route('reservations.index') }}" class="btn btn-outline-secondary btn-sm">Limpar</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card theme-card mb-3">
        <div class="card-header"><strong>Nova Reserva</strong></div>
        <div class="card-body">
            <form method="POST" action="{{ route('reservations.store') }}">
                @csrf
                <div class="row">
                    <div class="col-md-3 mb-2">
                        <label class="small text-uppercase font-weight-bold">Nome</label>
                        <input class="form-control" type="text" name="customer_name" value="{{ old('customer_name') }}" required>
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="small text-uppercase font-weight-bold">E-mail</label>
                        <input class="form-control" type="email" name="customer_email" value="{{ old('customer_email') }}">
                    </div>
                    <div class="col-md-2 mb-2">
                        <label class="small text-uppercase font-weight-bold">Telefone</label>
                        <input class="form-control" type="text" name="customer_phone" value="{{ old('customer_phone') }}">
                    </div>
                    <div class="col-md-2 mb-2">
                        <label class="small text-uppercase font-weight-bold">Placa</label>
                        <input class="form-control" type="text" name="vehicle_plate" value="{{ old('vehicle_plate') }}" required>
                    </div>
                    <div class="col-md-2 mb-2">
                        <label class="small text-uppercase font-weight-bold">Tipo</label>
                        <select class="form-control" name="vehicle_type" required>
                            <option value="carro" {{ old('vehicle_type') === 'carro' ? 'selected' : '' }}>Carro</option>
                            <option value="moto" {{ old('vehicle_type') === 'moto' ? 'selected' : '' }}>Moto</option>
                            <option value="caminhonete" {{ old('vehicle_type') === 'caminhonete' ? 'selected' : '' }}>Caminhonete</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="small text-uppercase font-weight-bold">Setor preferencial</label>
                        <select class="form-control" name="parking_sector_id">
                            <option value="">Sem preferencia</option>
                            @foreach ($sectors as $sectorCreate)
                                <option value="{{ $sectorCreate->id }}" {{ (string) old('parking_sector_id') === (string) $sectorCreate->id ? 'selected' : '' }}>
                                    {{ $sectorCreate->name }} ({{ $sectorCreate->code }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="small text-uppercase font-weight-bold">Modelo do veiculo</label>
                        <input class="form-control" type="text" name="vehicle_model" value="{{ old('vehicle_model') }}">
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="small text-uppercase font-weight-bold">Inicio</label>
                        <input class="form-control" type="datetime-local" name="starts_at" value="{{ old('starts_at') }}" required>
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="small text-uppercase font-weight-bold">Fim</label>
                        <input class="form-control" type="datetime-local" name="ends_at" value="{{ old('ends_at') }}" required>
                    </div>
                    <div class="col-md-2 mb-2">
                        <label class="small text-uppercase font-weight-bold">Pre-pagamento</label>
                        <select class="form-control" name="prepaid">
                            <option value="0" {{ old('prepaid', '0') === '0' ? 'selected' : '' }}>Nao</option>
                            <option value="1" {{ old('prepaid') === '1' ? 'selected' : '' }}>Sim</option>
                        </select>
                    </div>
                    <div class="col-md-2 mb-2">
                        <label class="small text-uppercase font-weight-bold">Metodo</label>
                        <select class="form-control" name="payment_method">
                            @foreach (['pix' => 'Pix', 'boleto' => 'Boleto', 'cartao_credito' => 'Cartao credito', 'cartao_debito' => 'Cartao debito', 'dinheiro' => 'Dinheiro'] as $methodValue => $methodLabel)
                                <option value="{{ $methodValue }}" {{ old('payment_method', 'pix') === $methodValue ? 'selected' : '' }}>
                                    {{ $methodLabel }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-12 mb-2">
                        <label class="small text-uppercase font-weight-bold">Observacoes</label>
                        <input class="form-control" type="text" name="notes" value="{{ old('notes') }}">
                    </div>
                </div>

                <div class="text-right">
                    <button type="submit" class="btn btn-theme">
                        <i class="fas fa-plus mr-1"></i> Criar reserva
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="card theme-card overflow-hidden">
        <div class="table-responsive">
            <table class="table table-theme mb-0">
                <thead>
                    <tr>
                        <th>Referencia</th>
                        <th>Cliente</th>
                        <th>Veiculo</th>
                        <th>Periodo</th>
                        <th>Setor/Vaga</th>
                        <th>Financeiro</th>
                        <th>Status</th>
                        <th class="text-center">Acoes</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($reservations as $reservation)
                        @php
                            $statusBadge = [
                                'pending' => 'badge-warning',
                                'confirmed' => 'badge-info',
                                'checked_in' => 'badge-primary',
                                'completed' => 'badge-success',
                                'cancelled' => 'badge-danger',
                                'no_show' => 'badge-dark',
                            ][$reservation->status] ?? 'badge-secondary';

                            $paymentBadge = [
                                'paid' => 'badge-success',
                                'pending' => 'badge-warning',
                                'failed' => 'badge-danger',
                                'cancelled' => 'badge-secondary',
                                'refunded' => 'badge-info',
                            ][$reservation->payment_status] ?? 'badge-secondary';
                        @endphp
                        <tr>
                            <td>
                                <div class="font-weight-bold">{{ $reservation->reference }}</div>
                                <div class="small text-muted">{{ optional($reservation->created_at)->format('d/m/Y H:i') }}</div>
                            </td>
                            <td>
                                <div class="font-weight-bold">{{ $reservation->customer_name }}</div>
                                <div class="small text-muted">{{ $reservation->customer_phone ?: '-' }}</div>
                                <div class="small text-muted">{{ $reservation->customer_email ?: '-' }}</div>
                            </td>
                            <td>
                                <div class="font-weight-bold text-uppercase">{{ $reservation->vehicle_plate }}</div>
                                <div class="small text-muted">{{ strtoupper((string) $reservation->vehicle_type) }}</div>
                                <div class="small text-muted">{{ $reservation->vehicle_model ?: '-' }}</div>
                            </td>
                            <td>
                                <div>{{ optional($reservation->starts_at)->format('d/m/Y H:i') }}</div>
                                <div class="small text-muted">ate {{ optional($reservation->ends_at)->format('d/m/Y H:i') }}</div>
                                <div class="small text-muted">
                                    {{ $reservation->starts_at ? $reservation->starts_at->diffForHumans() : '-' }}
                                </div>
                            </td>
                            <td>
                                <div>{{ $reservation->sector->name ?? '-' }}</div>
                                <div class="small text-muted">{{ $reservation->spot->code ?? '-' }}</div>
                            </td>
                            <td>
                                <div class="font-weight-bold">R$ {{ number_format(((int) $reservation->estimated_amount_cents) / 100, 2, ',', '.') }}</div>
                                <div class="small text-muted">Pre-pago: R$ {{ number_format(((int) $reservation->prepaid_amount_cents) / 100, 2, ',', '.') }}</div>
                                <span class="badge {{ $paymentBadge }}">{{ strtoupper((string) $reservation->payment_status) }}</span>
                            </td>
                            <td>
                                <span class="badge {{ $statusBadge }}">{{ strtoupper((string) $reservation->status) }}</span>
                                @if ($reservation->checked_in_at)
                                    <div class="small text-muted mt-1">Check-in: {{ $reservation->checked_in_at->format('d/m H:i') }}</div>
                                @endif
                            </td>
                            <td class="text-center">
                                @if (in_array($reservation->status, ['pending', 'confirmed'], true))
                                    <form method="POST" action="{{ route('reservations.checkin', $reservation) }}" class="d-inline">
                                        @csrf
                                        <button class="btn btn-sm btn-outline-success" type="submit" title="Check-in">
                                            <i class="fas fa-sign-in-alt"></i>
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('reservations.cancel', $reservation) }}" class="d-inline"
                                        onsubmit="return confirm('Cancelar esta reserva?');">
                                        @csrf
                                        <button class="btn btn-sm btn-outline-danger" type="submit" title="Cancelar">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </form>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">Nenhuma reserva encontrada para os filtros informados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-footer bg-white border-0">
            <div class="d-flex flex-wrap justify-content-between align-items-center" style="gap: 10px;">
                <div class="small text-muted">
                    Mostrando <strong>{{ $reservations->firstItem() ?? 0 }}</strong> a <strong>{{ $reservations->lastItem() ?? 0 }}</strong>
                    de <strong>{{ number_format((int) $reservations->total(), 0, ',', '.') }}</strong> reservas.
                </div>
                <div>{{ $reservations->onEachSide(1)->links('pagination::bootstrap-4') }}</div>
            </div>
        </div>
    </div>
@endsection
