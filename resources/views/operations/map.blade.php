@extends('adminlte::page')

@section('title', 'Operacao | Mapa de Vagas')

@section('content_header')
    <div class="report-hero theme-fade-in">
        <div class="d-flex flex-wrap justify-content-between align-items-center" style="gap: 12px;">
            <div>
                <h1 class="m-0">Mapa Visual de Vagas</h1>
                <p>Controle por setor com status operacional em tempo real.</p>
            </div>
            <div class="d-flex" style="gap: 8px;">
                <a href="{{ route('operations.map', request()->query()) }}" class="btn btn-light btn-sm">
                    <i class="fas fa-sync-alt mr-1"></i> Atualizar
                </a>
                <span class="badge badge-light p-2">{{ now()->format('d/m/Y H:i') }}</span>
            </div>
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
        <div class="alert alert-danger mt-3 mb-0">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="row mt-3">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="theme-kpi theme-info">
                <div>
                    <p>Veiculos Ativos</p>
                    <h3>{{ number_format((int) $summary['active_cars'], 0, ',', '.') }}</h3>
                </div>
                <i class="fas fa-car-side"></i>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="theme-kpi theme-success">
                <div>
                    <p>Finalizados Hoje</p>
                    <h3>{{ number_format((int) $summary['finished_today'], 0, ',', '.') }}</h3>
                </div>
                <i class="fas fa-flag-checkered"></i>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="theme-kpi theme-warn">
                <div>
                    <p>Ocupacao Geral</p>
                    <h3>{{ (int) $summary['occupancy_percent'] }}%</h3>
                </div>
                <i class="fas fa-chart-pie"></i>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="theme-kpi theme-danger">
                <div>
                    <p>Ocupacao Filtro</p>
                    <h3>{{ (int) $summary['visible_occupancy_percent'] }}%</h3>
                </div>
                <i class="fas fa-filter"></i>
            </div>
        </div>
    </div>

    <div class="card theme-card mb-3">
        <div class="card-body py-3">
            <div class="d-flex flex-wrap align-items-center" style="gap: 8px;">
                <span class="badge parking-legend legend-available">Livre: {{ (int) $summary['available_spots'] }}</span>
                <span class="badge parking-legend legend-reserved">Reservada: {{ (int) $summary['reserved_spots'] }}</span>
                <span class="badge parking-legend legend-occupied">Ocupada: {{ (int) $summary['occupied_spots'] }}</span>
                <span class="badge parking-legend legend-blocked">Bloqueada: {{ (int) $summary['blocked_spots'] }}</span>
                <span class="badge parking-legend legend-maintenance">Manutencao: {{ (int) $summary['maintenance_spots'] }}</span>
                <span class="badge badge-light ml-md-auto">Total de vagas: {{ (int) $summary['total_spots'] }}</span>
                <span class="badge badge-light">Vagas visiveis: {{ (int) $summary['visible_spots'] }}</span>
            </div>
        </div>
    </div>

    <div class="card theme-card mb-3">
        <div class="card-header"><strong>Filtros do Mapa</strong></div>
        <div class="card-body">
            <form method="GET" action="{{ route('operations.map') }}">
                <div class="row">
                    <div class="col-md-3 mb-2">
                        <label class="small text-uppercase font-weight-bold">Setor</label>
                        <select class="form-control" name="sector_id">
                            <option value="0">Todos os setores</option>
                            @foreach ($allSectors as $sectorOption)
                                <option value="{{ $sectorOption->id }}" {{ (int) ($filters['sector_id'] ?? 0) === (int) $sectorOption->id ? 'selected' : '' }}>
                                    {{ $sectorOption->name }} ({{ $sectorOption->code }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 mb-2">
                        <label class="small text-uppercase font-weight-bold">Status</label>
                        <select class="form-control" name="status">
                            @foreach ($statusOptions as $statusValue => $statusLabel)
                                <option value="{{ $statusValue }}" {{ ($filters['status'] ?? '') === $statusValue ? 'selected' : '' }}>
                                    {{ $statusLabel }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 mb-2">
                        <label class="small text-uppercase font-weight-bold">Tipo</label>
                        <select class="form-control" name="vehicle_type">
                            @foreach ($vehicleTypeOptions as $typeValue => $typeLabel)
                                <option value="{{ $typeValue }}" {{ ($filters['vehicle_type'] ?? '') === $typeValue ? 'selected' : '' }}>
                                    {{ $typeLabel }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="small text-uppercase font-weight-bold">Buscar vaga</label>
                        <input type="text" class="form-control" name="q" value="{{ $filters['q'] ?? '' }}"
                            placeholder="Ex: A-01, B12">
                    </div>
                    <div class="col-md-2 mb-2 d-flex align-items-end" style="gap: 8px;">
                        <button type="submit" class="btn btn-theme btn-sm flex-grow-1">Filtrar</button>
                        <a href="{{ route('operations.map') }}" class="btn btn-outline-secondary btn-sm">Limpar</a>
                    </div>
                </div>

                <div class="custom-control custom-switch mt-2">
                    <input type="checkbox" class="custom-control-input" id="with_spots" name="with_spots" value="1"
                        {{ !empty($filters['with_spots']) ? 'checked' : '' }}>
                    <label class="custom-control-label" for="with_spots">Mostrar apenas setores com vagas no filtro atual</label>
                </div>
            </form>
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
                                <label class="small text-uppercase font-weight-bold">Nome</label>
                                <input class="form-control" type="text" name="name" placeholder="Nome do setor" required>
                            </div>
                            <div class="col-md-3 mb-2">
                                <label class="small text-uppercase font-weight-bold">Codigo</label>
                                <input class="form-control" type="text" name="code" placeholder="Ex: A" required>
                            </div>
                            <div class="col-md-3 mb-2">
                                <label class="small text-uppercase font-weight-bold">Capacidade</label>
                                <input class="form-control" type="number" name="capacity" min="1" placeholder="0" required>
                            </div>
                            <div class="col-md-3 mb-2">
                                <label class="small text-uppercase font-weight-bold">Cor</label>
                                <input class="form-control" type="text" name="color" placeholder="#0f6c74">
                            </div>
                            <div class="col-md-3 mb-2">
                                <label class="small text-uppercase font-weight-bold">Linhas</label>
                                <input class="form-control" type="number" name="map_rows" min="1" max="30" placeholder="5">
                            </div>
                            <div class="col-md-3 mb-2">
                                <label class="small text-uppercase font-weight-bold">Colunas</label>
                                <input class="form-control" type="number" name="map_columns" min="1" max="60" placeholder="10">
                            </div>
                            <div class="col-md-12 mb-2">
                                <label class="small text-uppercase font-weight-bold">Observacoes</label>
                                <input class="form-control" type="text" name="notes" placeholder="Opcional">
                            </div>
                        </div>
                        <button class="btn btn-theme btn-sm" type="submit">
                            <i class="fas fa-plus mr-1"></i> Criar setor
                        </button>
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
                                <label class="small text-uppercase font-weight-bold">Setor</label>
                                <select class="form-control" name="parking_sector_id" required>
                                    <option value="">Selecione o setor</option>
                                    @foreach ($allSectors as $sector)
                                        <option value="{{ $sector->id }}">{{ $sector->name }} ({{ $sector->code }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3 mb-2">
                                <label class="small text-uppercase font-weight-bold">Codigo</label>
                                <input class="form-control" type="text" name="code" placeholder="Ex: A-01" required>
                            </div>
                            <div class="col-md-3 mb-2">
                                <label class="small text-uppercase font-weight-bold">Tipo</label>
                                <select class="form-control" name="vehicle_type" required>
                                    <option value="carro">Carro</option>
                                    <option value="moto">Moto</option>
                                    <option value="caminhonete">Caminhonete</option>
                                    <option value="geral">Geral</option>
                                </select>
                            </div>
                        </div>
                        <button class="btn btn-theme btn-sm" type="submit">
                            <i class="fas fa-plus mr-1"></i> Criar vaga
                        </button>
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
                $available = $spots->where('status', 'available')->count();
                $reserved = $spots->where('status', 'reserved')->count();
                $occupied = $spots->where('status', 'occupied')->count();
                $blocked = $spots->where('status', 'blocked')->count();
                $maintenance = $spots->where('status', 'maintenance')->count();
                $occupancy = (int) round(($occupied / $total) * 100);
            @endphp

            <div class="col-xl-6 mb-3">
                <div class="card theme-card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center" style="gap: 8px;">
                        <div>
                            <div class="d-flex align-items-center" style="gap: 8px;">
                                <span class="sector-dot" style="background: {{ $sector->color ?: '#0f6c74' }};"></span>
                                <strong>{{ $sector->name }}</strong>
                            </div>
                            <div class="small text-muted mt-1">
                                Codigo: {{ $sector->code }} | Capacidade: {{ (int) $sector->capacity }} | Ocupacao: {{ $occupancy }}%
                            </div>
                        </div>
                        <span class="badge badge-secondary">{{ $spots->count() }} vagas</span>
                    </div>
                    <div class="card-body parking-card-body">
                        <div class="progress parking-progress mb-3">
                            <div class="progress-bar bg-danger" role="progressbar" style="width: {{ $occupancy }}%"></div>
                        </div>

                        <div class="d-flex flex-wrap mb-3" style="gap: 6px;">
                            <span class="badge parking-legend legend-available">L {{ $available }}</span>
                            <span class="badge parking-legend legend-reserved">R {{ $reserved }}</span>
                            <span class="badge parking-legend legend-occupied">O {{ $occupied }}</span>
                            <span class="badge parking-legend legend-blocked">B {{ $blocked }}</span>
                            <span class="badge parking-legend legend-maintenance">M {{ $maintenance }}</span>
                        </div>

                        <div class="parking-grid-wrap">
                            <div class="parking-grid" style="--columns: {{ max(2, min(4, (int) $sector->map_columns)) }};">
                                @forelse ($spots as $spot)
                                    <div class="parking-spot spot-{{ $spot->status }}" title="{{ strtoupper($spot->status) }}">
                                        <div class="d-flex justify-content-between align-items-center" style="gap: 6px;">
                                            <div class="font-weight-bold text-truncate">{{ $spot->code }}</div>
                                            <span class="badge badge-light text-uppercase spot-status">{{ $spot->status }}</span>
                                        </div>
                                        <div class="small text-muted text-uppercase">{{ $spot->vehicle_type }}</div>

                                        @if ($spot->status === 'occupied' && $spot->car)
                                            <div class="small mt-1 text-danger">Placa: {{ $spot->car->placa }}</div>
                                        @elseif ($spot->status === 'reserved' && $spot->reservation)
                                            <div class="small mt-1 text-warning">Reserva: {{ $spot->reservation->reference }}</div>
                                        @endif

                                        @if ($spot->occupied_since)
                                            <div class="small text-muted">Desde {{ $spot->occupied_since->format('d/m H:i') }}</div>
                                        @endif

                                        <form method="POST" action="{{ route('operations.spots.status', $spot) }}" class="mt-2">
                                            @csrf
                                            @method('PATCH')
                                            <select class="form-control form-control-sm" name="status" onchange="this.form.submit()">
                                                @foreach (['available' => 'Livre', 'reserved' => 'Reservada', 'occupied' => 'Ocupada', 'blocked' => 'Bloqueada', 'maintenance' => 'Manutencao'] as $statusKey => $statusLabel)
                                                    <option value="{{ $statusKey }}" {{ $spot->status === $statusKey ? 'selected' : '' }}>
                                                        {{ $statusLabel }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </form>
                                    </div>
                                @empty
                                    <div class="text-muted">Nenhuma vaga para os filtros informados neste setor.</div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-info">
                    Nenhum setor ou vaga encontrado para os filtros atuais.
                    <a href="{{ route('operations.map') }}" class="alert-link">Limpar filtros</a>
                </div>
            </div>
        @endforelse
    </div>
@endsection

@push('css')
    <style>
        .parking-progress {
            height: 8px;
            border-radius: 999px;
            background: #e6eef7;
        }

        .sector-dot {
            width: 10px;
            height: 10px;
            border-radius: 999px;
            display: inline-block;
            box-shadow: 0 0 0 2px rgba(0, 0, 0, .08);
        }

        .parking-legend {
            border-radius: 999px;
            padding: .35rem .55rem;
            border: 1px solid rgba(0, 0, 0, .06);
            font-size: .72rem;
            font-weight: 700;
        }

        .legend-available { background: #eaf8ef; color: #1f6f3d; }
        .legend-reserved { background: #fff6e8; color: #946108; }
        .legend-occupied { background: #ffebeb; color: #9d2732; }
        .legend-blocked { background: #ececec; color: #55606b; }
        .legend-maintenance { background: #e9f1ff; color: #2a4f96; }

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
            box-sizing: border-box;
            min-height: 118px;
            min-width: 0;
        }

        .parking-spot .spot-status {
            font-size: .62rem;
            letter-spacing: .3px;
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

        @media (max-width: 992px) {
            .parking-grid {
                width: 100%;
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }
    </style>
@endpush
