@extends('adminlte::page')

@section('title', 'Operação | Mapa de Vagas')

@section('content_header')
    <div class="d-flex flex-wrap justify-content-between align-items-center" style="gap: 10px;">
        <div>
            <h1 class="m-0">Mapa Visual do Pátio</h1>
            <p class="text-muted m-0">Controle em tempo real por setor e vagas.</p>
        </div>
        <span class="badge badge-light p-2">Atualizado em {{ now()->format('d/m/Y H:i') }}</span>
    </div>
@endsection

@section('content')
    @if (session('create'))
        <div class="alert alert-success">{{ session('create') }}</div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="row mb-3">
        <div class="col-md-3 mb-2">
            <div class="theme-stat-card h-100">
                <p class="theme-stat-label">Veículos Ativos</p>
                <h3>{{ number_format((int) $summary['active_cars'], 0, ',', '.') }}</h3>
            </div>
        </div>
        <div class="col-md-3 mb-2">
            <div class="theme-stat-card h-100">
                <p class="theme-stat-label">Finalizados Hoje</p>
                <h3>{{ number_format((int) $summary['finished_today'], 0, ',', '.') }}</h3>
            </div>
        </div>
        <div class="col-md-3 mb-2">
            <div class="theme-stat-card h-100">
                <p class="theme-stat-label">Ocupação</p>
                <h3>{{ (int) $summary['occupancy_percent'] }}%</h3>
            </div>
        </div>
        <div class="col-md-3 mb-2">
            <div class="theme-stat-card h-100">
                <p class="theme-stat-label">Vagas Livres</p>
                <h3>{{ number_format((int) $summary['available_spots'], 0, ',', '.') }}</h3>
            </div>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-lg-6 mb-3">
            <div class="card theme-card h-100">
                <div class="card-header"><strong>Novo Setor</strong></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('operations.sectors.store') }}">
                        @csrf
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <input class="form-control" type="text" name="name" placeholder="Nome do setor" required>
                            </div>
                            <div class="col-md-3 mb-2">
                                <input class="form-control" type="text" name="code" placeholder="Código" required>
                            </div>
                            <div class="col-md-3 mb-2">
                                <input class="form-control" type="number" name="capacity" min="1" placeholder="Capacidade" required>
                            </div>
                            <div class="col-md-3 mb-2">
                                <input class="form-control" type="text" name="color" placeholder="#0f6c74">
                            </div>
                            <div class="col-md-3 mb-2">
                                <input class="form-control" type="number" name="map_rows" min="1" max="30" placeholder="Linhas">
                            </div>
                            <div class="col-md-3 mb-2">
                                <input class="form-control" type="number" name="map_columns" min="1" max="60" placeholder="Colunas">
                            </div>
                            <div class="col-md-12 mb-2">
                                <input class="form-control" type="text" name="notes" placeholder="Observações">
                            </div>
                        </div>
                        <button class="btn btn-theme btn-sm" type="submit">Criar setor</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-3">
            <div class="card theme-card h-100">
                <div class="card-header"><strong>Nova Vaga</strong></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('operations.spots.store') }}">
                        @csrf
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <select class="form-control" name="parking_sector_id" required>
                                    <option value="">Selecione o setor</option>
                                    @foreach ($sectors as $sector)
                                        <option value="{{ $sector->id }}">{{ $sector->name }} ({{ $sector->code }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3 mb-2">
                                <input class="form-control" type="text" name="code" placeholder="Ex: A-01" required>
                            </div>
                            <div class="col-md-3 mb-2">
                                <select class="form-control" name="vehicle_type" required>
                                    <option value="carro">Carro</option>
                                    <option value="moto">Moto</option>
                                    <option value="caminhonete">Caminhonete</option>
                                    <option value="geral">Geral</option>
                                </select>
                            </div>
                        </div>
                        <button class="btn btn-theme btn-sm" type="submit">Criar vaga</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        @forelse ($sectors as $sector)
            @php
                $spots = $sector->spots;
                $total = max(1, $spots->count());
                $occupied = $spots->where('status', 'occupied')->count();
                $occupancy = (int) round(($occupied / $total) * 100);
            @endphp

            <div class="col-xl-6 mb-3">
                <div class="card theme-card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <strong>{{ $sector->name }}</strong>
                            <div class="small text-muted">Código: {{ $sector->code }} | Ocupação: {{ $occupancy }}%</div>
                        </div>
                        <span class="badge badge-secondary">{{ $spots->count() }} vagas</span>
                    </div>
                    <div class="card-body parking-card-body">
                        <div class="parking-grid" style="--columns: {{ max(2, min(8, (int) $sector->map_columns)) }};">
                            @forelse ($spots as $spot)
                                <div class="parking-spot spot-{{ $spot->status }}" title="{{ strtoupper($spot->status) }}">
                                    <div class="font-weight-bold">{{ $spot->code }}</div>
                                    <div class="small text-muted">{{ strtoupper($spot->vehicle_type) }}</div>
                                    <form method="POST" action="{{ route('operations.spots.status', $spot) }}" class="mt-2">
                                        @csrf
                                        @method('PATCH')
                                        <select class="form-control form-control-sm" name="status" onchange="this.form.submit()">
                                            @foreach (['available' => 'Livre', 'reserved' => 'Reservada', 'occupied' => 'Ocupada', 'blocked' => 'Bloqueada', 'maintenance' => 'Manutenção'] as $statusKey => $statusLabel)
                                                <option value="{{ $statusKey }}" {{ $spot->status === $statusKey ? 'selected' : '' }}>
                                                    {{ $statusLabel }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </form>
                                </div>
                            @empty
                                <div class="text-muted">Nenhuma vaga cadastrada neste setor.</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-info">Nenhum setor cadastrado ainda.</div>
            </div>
        @endforelse
    </div>
@endsection

@push('css')
    <style>
        .parking-grid {
            display: grid;
            grid-template-columns: repeat(var(--columns), minmax(0, 1fr));
            gap: 10px;
            width: 100%;
        }

        .parking-card-body {
            overflow: hidden;
        }

        .parking-spot {
            border: 1px solid #d7e5f3;
            border-radius: 12px;
            padding: 8px;
            background: #fff;
            min-width: 0;
            box-sizing: border-box;
        }

        .parking-spot .form-control {
            width: 100%;
            min-width: 0;
        }

        .spot-available { background: #edf8f0; }
        .spot-reserved { background: #fff7e7; }
        .spot-occupied { background: #ffe9e9; }
        .spot-blocked { background: #ececec; }
        .spot-maintenance { background: #e8f0ff; }

        @media (max-width: 768px) {
            .parking-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }
    </style>
@endpush
