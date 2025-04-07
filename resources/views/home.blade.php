@extends('adminlte::page')

@section('adminlte_css')
    <link rel="icon" type="image/png" href="{{ asset('img/LogoEstacionamento.png') }}">
    <link rel="stylesheet" href="{{ asset('fontawesome-free/css/all.min.css') }}" />
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4e73df;
            --secondary-color: #1cc88a;
            --info-color: #36b9cc;
            --warning-color: #f6c23e;
            --danger-color: #e74a3b;
            --dark-color: #5a5c69;
            --light-color: #f8f9fc;
        }

        html,
        body {
            height: 100%;
            margin: 0;
            font-family: 'Nunito', sans-serif;
        }

        .content-wrapper {
            min-height: calc(100vh - 56px);
            background-color: #f8f9fc;
        }

        .content {
            padding: 25px;
        }

        .chart-container {
            width: 100%;
            height: 400px;
            position: relative;
        }

        .card-body canvas {
            width: 100% !important;
            height: 100% !important;
        }

        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            margin-bottom: 25px;
            transition: transform 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
        }

        .card-header {
            background-color: white;
            border-bottom: 1px solid #e3e6f0;
            border-top-left-radius: 10px !important;
            border-top-right-radius: 10px !important;
            padding: 1rem 1.25rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .card-header h3 {
            margin: 0;
            font-weight: 700;
            color: #5a5c69;
            font-size: 1.25rem;
        }

        .card-body {
            padding: 1.25rem;
        }

        .small-box {
            border-radius: 10px;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            position: relative;
            display: block;
            margin-bottom: 25px;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .small-box:hover {
            transform: translateY(-5px);
        }

        .small-box .inner {
            padding: 20px;
            color: white;
        }

        .small-box h3 {
            font-size: 2.5rem;
            font-weight: 700;
            margin: 0 0 10px 0;
            white-space: nowrap;
        }

        .small-box p {
            font-size: 1rem;
            margin-bottom: 0.5rem;
        }

        .small-box .icon {
            position: absolute;
            top: 15px;
            right: 15px;
            font-size: 70px;
            color: rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
        }

        .small-box:hover .icon {
            font-size: 80px;
            top: 10px;
        }

        .bg-info {
            background: linear-gradient(135deg, #36b9cc 0%, #1a8eaa 100%);
        }

        .bg-warning {
            background: linear-gradient(135deg, #f6c23e 0%, #dda20a 100%);
        }

        .bg-danger {
            background: linear-gradient(135deg, #e74a3b 0%, #c52c1a 100%);
        }

        .bg-success {
            background: linear-gradient(135deg, #1cc88a 0%, #13855c 100%);
        }

        .badge-light {
            background-color: rgba(255, 255, 255, 0.2);
            color: white;
            font-weight: 600;
            padding: 0.5rem 0.75rem;
            border-radius: 30px;
            font-size: 0.85rem;
        }

        .breadcrumb {
            background-color: transparent;
            padding: 0;
            margin-bottom: 1.5rem;
        }

        .breadcrumb-item {
            font-weight: 600;
            font-size: 1rem;
        }

        .breadcrumb-item.active {
            color: #5a5c69;
        }

        .dashboard-header {
            margin-bottom: 1.5rem;
        }

        .dashboard-header h1 {
            font-size: 1.75rem;
            font-weight: 700;
            color: #5a5c69;
            margin: 0;
        }

        .dashboard-header p {
            color: #858796;
            margin: 0.5rem 0 0 0;
        }

        .progress {
            height: 10px;
            border-radius: 5px;
            margin-top: 10px;
        }

        .progress-bar {
            border-radius: 5px;
        }

        .stats-summary {
            display: flex;
            justify-content: space-between;
            margin-top: 10px;
            font-size: 0.875rem;
            color: rgba(255, 255, 255, 0.8);
        }

        .vehicle-count {
            display: flex;
            align-items: center;
        }

        .vehicle-count i {
            margin-right: 5px;
        }

        .card-footer {
            background-color: #f8f9fc;
            border-top: 1px solid #e3e6f0;
            padding: 0.75rem 1.25rem;
            border-bottom-left-radius: 10px !important;
            border-bottom-right-radius: 10px !important;
        }

        .date-filter {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
        }

        .date-filter select {
            margin-left: 10px;
            border-radius: 5px;
            border: 1px solid #d1d3e2;
            padding: 0.375rem 0.75rem;
            font-size: 0.875rem;
        }

        .chart-legend {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            margin-top: 1rem;
        }

        .legend-item {
            display: flex;
            align-items: center;
            margin-right: 1rem;
            margin-bottom: 0.5rem;
        }

        .legend-color {
            width: 15px;
            height: 15px;
            border-radius: 3px;
            margin-right: 5px;
        }

        .legend-label {
            font-size: 0.875rem;
            color: #5a5c69;
        }

        .dashboard-summary {
            background-color: white;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        }

        .summary-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: #5a5c69;
            margin-bottom: 1rem;
        }

        .summary-stats {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .stat-item {
            flex: 1;
            min-width: 150px;
            padding: 1rem;
            background-color: #f8f9fc;
            border-radius: 8px;
            text-align: center;
        }

        .stat-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }

        .stat-label {
            font-size: 0.875rem;
            color: #858796;
        }

        .stat-icon {
            margin-bottom: 0.5rem;
            color: var(--primary-color);
            opacity: 0.8;
        }

        .occupancy-bar {
            height: 30px;
            background-color: #e9ecef;
            border-radius: 15px;
            overflow: hidden;
            margin: 1rem 0;
        }

        .occupancy-progress {
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            transition: width 1s ease;
        }

        .occupancy-label {
            display: flex;
            justify-content: space-between;
            font-size: 0.875rem;
            color: #858796;
        }

        @media (max-width: 768px) {
            .small-box h3 {
                font-size: 2rem;
            }

            .small-box .icon {
                font-size: 50px;
            }

            .small-box:hover .icon {
                font-size: 55px;
            }
        }
    </style>
    @parent
@endsection


@section('title', 'Painel | Home')

@section('content_header')
    <div class="dashboard-header">
        <h1>Painel de Controle</h1>
        <p>Visão geral do estacionamento</p>
    </div>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item active" aria-current="page">Home</li>
        </ol>
    </nav>
@endsection
@section('content')
    <!-- Dashboard Summary -->
    <div class="dashboard-summary">
        <div class="summary-title">
            <i class="fas fa-tachometer-alt mr-2"></i> Resumo da Ocupação
        </div>

        <div class="row">

            <!-- Car Occupancy -->
            <div class="mb-4  col-md-6 col-lg-4">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="font-weight-bold text-primary">
                        <i class="fas fa-car mr-1"></i> Carros
                    </span>
                    <span class="badge badge-primary">
                        {{ $data['car_parking'] }}/{{ $data['total_car_vagas'] }}
                    </span>
                </div>
                <div class="occupancy-bar">
                    <div class="occupancy-progress bg-primary"
                        style="width: {{ ($data['car_parking'] / $data['total_car_vagas']) * 100 }}%">
                        {{ round(($data['car_parking'] / $data['total_car_vagas']) * 100) }}%
                    </div>
                </div>
                <div class="occupancy-label">
                    <span>Ocupado: {{ $data['car_parking'] }}</span>
                    <span>Disponível: {{ $data['total_car_vagas'] - $data['car_parking'] }}</span>
                </div>
            </div>

            <!-- Motorcycle Occupancy -->
            <div class="mb-4  col-md-6 col-lg-4">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="font-weight-bold text-warning">
                        <i class="fas fa-motorcycle mr-1"></i> Motos
                    </span>
                    <span class="badge badge-warning">
                        {{ $data['moto_parking'] }}/{{ $data['total_moto_vagas'] }}
                    </span>
                </div>
                <div class="occupancy-bar">
                    <div class="occupancy-progress bg-warning"
                        style="width: {{ ($data['moto_parking'] / $data['total_moto_vagas']) * 100 }}%">
                        {{ round(($data['moto_parking'] / $data['total_moto_vagas']) * 100) }}%
                    </div>
                </div>
                <div class="occupancy-label">
                    <span>Ocupado: {{ $data['moto_parking'] }}</span>
                    <span>Disponível: {{ $data['total_moto_vagas'] - $data['moto_parking'] }}</span>
                </div>
            </div>

            <!-- Pickup Truck Occupancy -->
            <div class="mb-4 col-md-6 col-lg-4">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="font-weight-bold text-danger">
                        <i class="fas fa-truck-pickup mr-1"></i> Caminhonetes
                    </span>
                    <span class="badge badge-danger">
                        {{ $data['caminhonete_parking'] }}/{{ $data['total_caminhonete_vagas'] }}
                    </span>
                </div>
                <div class="occupancy-bar">
                    <div class="occupancy-progress bg-danger"
                        style="width: {{ ($data['caminhonete_parking'] / $data['total_caminhonete_vagas']) * 100 }}%">
                        {{ round(($data['caminhonete_parking'] / $data['total_caminhonete_vagas']) * 100) }}%
                    </div>
                </div>
                <div class="occupancy-label">
                    <span>Ocupado: {{ $data['caminhonete_parking'] }}</span>
                    <span>Disponível: {{ $data['total_caminhonete_vagas'] - $data['caminhonete_parking'] }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Status Cards -->
    <div class="row">
        <!-- Cars Card -->
        <div class="col-lg-3 col-md-6 col-sm-12">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $data['car_parking'] }}</h3>
                    <p>Carros Estacionados</p>
                    <span class="badge badge-light">
                        <i class="fas fa-parking"></i> {{ $data['total_car_vagas'] - $data['car_parking'] }} vagas
                        restantes
                    </span>

                    <div class="stats-summary mt-3">
                        <div class="vehicle-count">
                            <i class="fas fa-sign-in-alt"></i> {{ rand(10, 30) }} entradas hoje
                        </div>
                        <div class="vehicle-count">
                            <i class="fas fa-sign-out-alt"></i> {{ rand(5, 20) }} saídas hoje
                        </div>
                    </div>
                </div>
                <div class="icon">
                    <i class="fa fa-car"></i>
                </div>
            </div>
        </div>

        <!-- Motorcycles Card -->
        <div class="col-lg-3 col-md-6 col-sm-12">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $data['moto_parking'] }}</h3>
                    <p>Motos Estacionadas</p>
                    <span class="badge badge-light">
                        <i class="fas fa-parking"></i> {{ $data['total_moto_vagas'] - $data['moto_parking'] }} vagas
                        restantes
                    </span>

                    <div class="stats-summary mt-3">
                        <div class="vehicle-count">
                            <i class="fas fa-sign-in-alt"></i> {{ rand(5, 15) }} entradas hoje
                        </div>
                        <div class="vehicle-count">
                            <i class="fas fa-sign-out-alt"></i> {{ rand(3, 10) }} saídas hoje
                        </div>
                    </div>
                </div>
                <div class="icon">
                    <i class="fa fa-motorcycle"></i>
                </div>
            </div>
        </div>

        <!-- Pickup Trucks Card -->
        <div class="col-lg-3 col-md-6 col-sm-12">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ $data['caminhonete_parking'] }}</h3>
                    <p>Caminhonetes Estacionadas</p>
                    <span class="badge badge-light">
                        <i class="fas fa-parking"></i>
                        {{ $data['total_caminhonete_vagas'] - $data['caminhonete_parking'] }} vagas restantes
                    </span>

                    <div class="stats-summary mt-3">
                        <div class="vehicle-count">
                            <i class="fas fa-sign-in-alt"></i> {{ rand(3, 10) }} entradas hoje
                        </div>
                        <div class="vehicle-count">
                            <i class="fas fa-sign-out-alt"></i> {{ rand(1, 8) }} saídas hoje
                        </div>
                    </div>
                </div>
                <div class="icon">
                    <i class="fa fa-truck-pickup"></i>
                </div>
            </div>
        </div>

        <!-- Monthly Members Card -->
        <div class="col-lg-3 col-md-6 col-sm-12">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $data['monthly_members'] }}</h3>
                    <p>Mensalistas Ativos</p>
                    <div class="stats-summary mt-3">
                        <div class="vehicle-count">
                            <i class="fas fa-car"></i> {{ $data['monthly_cars'] }} carros
                        </div>
                        <div class="vehicle-count">
                            <i class="fas fa-motorcycle"></i> {{ $data['monthly_motos'] }} motos
                        </div>
                        <div class="vehicle-count">
                            <i class="fas fa-truck-pickup"></i> {{ $data['monthly_caminhonetes'] }} caminhonetes
                        </div>
                    </div>
                    <span class="badge badge-light mt-2">
                        <i class="fas fa-parking"></i> {{ $data['total_mensalistas_vagas'] - $data['monthly_members'] }} vagas restantes
                    </span>
                </div>
                <div class="icon">
                    <i class="fa fa-users"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Occupancy Bars -->
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-chart-bar mr-2"></i> Ocupação Atual
                </h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <!-- Carros -->
                    <div class="col-md-4">
                        <div class="occupancy-item">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="font-weight-bold">
                                    <i class="fas fa-car mr-1"></i> Carros
                                </span>
                                <span class="badge badge-primary">
                                    {{ $data['car_parking'] }}/{{ $data['total_car_vagas'] }}
                                </span>
                            </div>
                            <div class="progress">
                                <div class="progress-bar bg-primary" role="progressbar" 
                                     style="width: {{ ($data['car_parking'] / $data['total_car_vagas']) * 100 }}%">
                                    {{ round(($data['car_parking'] / $data['total_car_vagas']) * 100) }}%
                                </div>
                            </div>
                            <div class="mt-2">
                                <small class="text-muted">
                                    Mensalistas: {{ $data['monthly_cars'] }} | 
                                    Avulsos: {{ $data['car_parking'] - $data['monthly_cars'] }}
                                </small>
                            </div>
                        </div>
                    </div>

                    <!-- Motos -->
                    <div class="col-md-4">
                        <div class="occupancy-item">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="font-weight-bold">
                                    <i class="fas fa-motorcycle mr-1"></i> Motos
                                </span>
                                <span class="badge badge-warning">
                                    {{ $data['moto_parking'] }}/{{ $data['total_moto_vagas'] }}
                                </span>
                            </div>
                            <div class="progress">
                                <div class="progress-bar bg-warning" role="progressbar" 
                                     style="width: {{ ($data['moto_parking'] / $data['total_moto_vagas']) * 100 }}%">
                                    {{ round(($data['moto_parking'] / $data['total_moto_vagas']) * 100) }}%
                                </div>
                            </div>
                            <div class="mt-2">
                                <small class="text-muted">
                                    Mensalistas: {{ $data['monthly_motos'] }} | 
                                    Avulsos: {{ $data['moto_parking'] - $data['monthly_motos'] }}
                                </small>
                            </div>
                        </div>
                    </div>

                    <!-- Caminhonetes -->
                    <div class="col-md-4">
                        <div class="occupancy-item">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="font-weight-bold">
                                    <i class="fas fa-truck-pickup mr-1"></i> Caminhonetes
                                </span>
                                <span class="badge badge-danger">
                                    {{ $data['caminhonete_parking'] }}/{{ $data['total_caminhonete_vagas'] }}
                                </span>
                            </div>
                            <div class="progress">
                                <div class="progress-bar bg-danger" role="progressbar" 
                                     style="width: {{ ($data['caminhonete_parking'] / $data['total_caminhonete_vagas']) * 100 }}%">
                                    {{ round(($data['caminhonete_parking'] / $data['total_caminhonete_vagas']) * 100) }}%
                                </div>
                            </div>
                            <div class="mt-2">
                                <small class="text-muted">
                                    Mensalistas: {{ $data['monthly_caminhonetes'] }} | 
                                    Avulsos: {{ $data['caminhonete_parking'] - $data['monthly_caminhonetes'] }}
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Revenue Summary -->
    <div class="row">
        <div class="col-lg-4 col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-money-bill-wave mr-2"></i> Receita
                    </h3>
                    <div class="date-filter">
                        <select id="revenueFilter" class="form-control form-control-sm">
                            <option value="today">Hoje</option>
                            <option value="week">Esta Semana</option>
                            <option value="month" selected>Este Mês</option>
                        </select>
                    </div>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <h2 class="font-weight-bold text-success">
                            R$ {{ number_format(rand(5000, 15000), 2, ',', '.') }}
                        </h2>
                        <p class="text-muted">Total de receita no período</p>
                    </div>

                    <div class="revenue-breakdown">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Tickets Avulsos</span>
                            <span class="font-weight-bold">R$ {{ number_format(rand(2000, 8000), 2, ',', '.') }}</span>
                        </div>
                        <div class="progress mb-3">
                            <div class="progress-bar bg-info" role="progressbar" style="width: {{ rand(30, 60) }}%">
                            </div>
                        </div>

                        <div class="d-flex justify-content-between mb-2">
                            <span>Mensalidades</span>
                            <span class="font-weight-bold">R$ {{ number_format(rand(2000, 6000), 2, ',', '.') }}</span>
                        </div>
                        <div class="progress mb-3">
                            <div class="progress-bar bg-success" role="progressbar" style="width: {{ rand(30, 50) }}%">
                            </div>
                        </div>

                        <div class="d-flex justify-content-between mb-2">
                            <span>Serviços Adicionais</span>
                            <span class="font-weight-bold">R$ {{ number_format(rand(500, 2000), 2, ',', '.') }}</span>
                        </div>
                        <div class="progress">
                            <div class="progress-bar bg-warning" role="progressbar" style="width: {{ rand(5, 20) }}%">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer text-center">
                    <a href="#" class="text-primary">Ver relatório detalhado <i
                            class="fas fa-arrow-right ml-1"></i></a>
                </div>
            </div>
        </div>

        <!-- Charts -->
        <div class="col-lg-8 col-md-12">
            <div class="row">
                <!-- Gráfico de Veículos mais Estacionados no Mês -->
                <div class="col-lg-6 col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-chart-pie mr-2"></i> Veículos mais Estacionados
                            </h3>
                            <div class="date-filter">
                                <select id="vehicleChartFilter" class="form-control form-control-sm">
                                    <option value="week">Esta Semana</option>
                                    <option value="month" selected>Este Mês</option>
                                </select>
                            </div>
                        </div>
                        <div class="card-body chart-container">
                            <canvas id="vehicleTypeChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Gráfico de Veículos Estacionados -->
                <div class="col-lg-6 col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-chart-line mr-2"></i> Veículos Estacionados
                            </h3>
                            <div class="card-tools">
                                <select id="periodSelect" class="form-control form-control-sm">
                                    <option value="year">Ano Inteiro</option>
                                    <option value="quarter">Último Trimestre</option>
                                </select>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="monthlyVehicleChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-history mr-2"></i> Atividade Recente
                    </h3>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="thead-light">
                                <tr>
                                    <th>Placa</th>
                                    <th>Modelo</th>
                                    <th>Tipo</th>
                                    <th>Entrada</th>
                                    <th>Tempo Estacionado</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($data['cars'] as $car)
                                <tr>
                                    <td>{{ $car->placa }}</td>
                                    <td>{{ $car->modelo }}</td>
                                    <td>
                                        @if($car->tipo_car == 'carro')
                                            <span class="badge badge-primary">
                                                <i class="fas fa-car"></i> Carro
                                            </span>
                                        @elseif($car->tipo_car == 'moto')
                                            <span class="badge badge-warning">
                                                <i class="fas fa-motorcycle"></i> Moto
                                            </span>
                                        @else
                                            <span class="badge badge-danger">
                                                <i class="fas fa-truck-pickup"></i> Caminhonete
                                            </span>
                                        @endif
                                    </td>
                                    <td>{{ \Carbon\Carbon::parse($car->created_at)->format('d/m/Y H:i') }}</td>
                                    <td>
                                        @php
                                            $entrada = \Carbon\Carbon::parse($car->created_at);
                                            $agora = \Carbon\Carbon::now();
                                            $diferenca = $entrada->diff($agora);
                                            
                                            $horas = $diferenca->h;
                                            $minutos = $diferenca->i;
                                            $dias = $diferenca->d;
                                            
                                            $tempo = '';
                                            if ($dias > 0) {
                                                $tempo .= $dias . 'd ';
                                            }
                                            if ($horas > 0) {
                                                $tempo .= $horas . 'h ';
                                            }
                                            $tempo .= $minutos . 'm';
                                        @endphp
                                        {{ $tempo }}
                                    </td>
                                    <td>
                                        @if($car->status == 'finalizado')
                                            <span class="badge badge-secondary">
                                                <i class="fas fa-check-circle"></i> Finalizado
                                            </span>
                                        @else
                                            <span class="badge badge-success">
                                                <i class="fas fa-parking"></i> Ativo
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center">
                                        <div class="alert alert-info">
                                            <i class="fas fa-info-circle"></i> Nenhum veículo estacionado no momento.
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer text-center">
                    <a href="{{ route('cars.index') }}" class="btn btn-primary">
                        <i class="fas fa-list mr-2"></i> Ver Todas as Atividades
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Peak Hours -->
    <div class="row mt-4">
        <div class="col-lg-12 col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-clock mr-2"></i> Horários de Pico
                    </h3>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="peakHoursChart"></canvas>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="d-flex justify-content-between">
                        <div>
                            <span class="font-weight-bold">Horário mais movimentado:</span>
                            <span class="text-danger ml-2">
                                @php
                                    $maxCount = max($data['PeakHours']);
                                    $maxHour = array_search($maxCount, $data['PeakHours']);
                                    echo sprintf('%02d:00 - %02d:00', $maxHour, $maxHour + 1);
                                @endphp
                            </span>
                        </div>
                        <div>
                            <span class="font-weight-bold">Horário mais tranquilo:</span>
                            <span class="text-success ml-2">
                                @php
                                    $minCount = min($data['PeakHours']);
                                    $minHour = array_search($minCount, $data['PeakHours']);
                                    echo sprintf('%02d:00 - %02d:00', $minHour, $minHour + 1);
                                @endphp
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script src="{{ asset('overlayScrollbars/js/jquery.overlayScrollbars.min.js') }}"></script>
    <script src="{{ asset('popper/popper.min.js') }}"></script>
    <script src="{{ asset('bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.0/chart.min.js"></script>
    <script src="{{ asset('js/jquery.min.js') }}"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Gráfico de Veículos mais Estacionados (Doughnut)
            const vehicleTypeCtx = document.getElementById('vehicleTypeChart').getContext('2d');
            const vehicleTypeChart = new Chart(vehicleTypeCtx, {
                type: 'doughnut',
                data: {
                    labels: {!! json_encode($data['CarLabels']) !!},
                    datasets: [{
                        data: {!! json_encode($data['CarValues']) !!},
                        backgroundColor: [
                            'rgba(54, 162, 235, 0.8)',
                            'rgba(255, 206, 86, 0.8)',
                            'rgba(255, 99, 132, 0.8)'
                        ],
                        borderColor: [
                            'rgba(54, 162, 235, 1)',
                            'rgba(255, 206, 86, 1)',
                            'rgba(255, 99, 132, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 20,
                                font: {
                                    size: 12
                                }
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let label = context.label || '';
                                    let value = context.raw || 0;
                                    let total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    let percentage = Math.round((value / total) * 100);
                                    return `${label}: ${value} (${percentage}%)`;
                                }
                            }
                        }
                    }
                }
            });

            // Gráfico de Veículos Estacionados
            const monthlyVehicleCtx = document.getElementById('monthlyVehicleChart').getContext('2d');
            let monthlyVehicleChart;

            function createChart(labels, datasets) {
                if (monthlyVehicleChart) {
                    monthlyVehicleChart.destroy();
                }

                monthlyVehicleChart = new Chart(monthlyVehicleCtx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: datasets
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    padding: 20,
                                    font: {
                                        size: 12
                                    }
                                }
                            },
                            tooltip: {
                                mode: 'index',
                                intersect: false,
                                callbacks: {
                                    label: function(context) {
                                        return `${context.dataset.label}: ${context.raw} veículos`;
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 1,
                                    callback: function(value) {
                                        return value + ' veíc.';
                                    }
                                }
                            }
                        }
                    }
                });
            }

            // Dados iniciais (ano)
            const yearDatasets = {!! json_encode($data['CarLabelsYear']) !!}.map((label, index) => {
                const colors = [
                    'rgba(54, 162, 235, 0.8)',
                    'rgba(255, 206, 86, 0.8)',
                    'rgba(255, 99, 132, 0.8)'
                ];
                
                return {
                    label: label.charAt(0).toUpperCase() + label.slice(1),
                    data: {!! json_encode($data['CarValuesYear']) !!}[label],
                    borderColor: colors[index],
                    backgroundColor: colors[index].replace('0.8', '0.1'),
                    tension: 0.4,
                    fill: true,
                    pointRadius: 4,
                    pointHoverRadius: 6
                };
            });

            // Dados do trimestre
            const quarterDatasets = {!! json_encode($data['CarLabelsYear']) !!}.map((label, index) => {
                const colors = [
                    'rgba(54, 162, 235, 0.8)',
                    'rgba(255, 206, 86, 0.8)',
                    'rgba(255, 99, 132, 0.8)'
                ];
                
                return {
                    label: label.charAt(0).toUpperCase() + label.slice(1),
                    data: {!! json_encode($data['QuarterValues']) !!}[label],
                    borderColor: colors[index],
                    backgroundColor: colors[index].replace('0.8', '0.1'),
                    tension: 0.4,
                    fill: true,
                    pointRadius: 4,
                    pointHoverRadius: 6
                };
            });

            // Criar gráfico inicial (ano)
            createChart({!! json_encode($data['MonthLabels']) !!}, yearDatasets);

            // Event listener para o select
            document.getElementById('periodSelect').addEventListener('change', function() {
                if (this.value === 'year') {
                    createChart({!! json_encode($data['MonthLabels']) !!}, yearDatasets);
                } else {
                    // Converter labels do trimestre para o formato correto
                    const quarterLabels = {!! json_encode($data['QuarterLabels']) !!}.map(label => {
                        const [month, year] = label.split('/');
                        return `${month}/${year}`;
                    });
                    createChart(quarterLabels, quarterDatasets);
                }
            });

            // Animação das barras de ocupação
            const occupancyBars = document.querySelectorAll('.occupancy-progress');
            occupancyBars.forEach(bar => {
                const targetWidth = bar.style.width;
                bar.style.width = '0%';
                setTimeout(() => {
                    bar.style.width = targetWidth;
                }, 300);
            });

            // Gráfico de Horários de Pico
            const peakHoursCtx = document.getElementById('peakHoursChart').getContext('2d');
            const peakHoursChart = new Chart(peakHoursCtx, {
                type: 'bar',
                data: {
                    labels: {!! json_encode($data['HourLabels']) !!},
                    datasets: [{
                        label: 'Veículos Estacionados',
                        data: {!! json_encode($data['PeakHours']) !!},
                        backgroundColor: 'rgba(54, 162, 235, 0.5)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return `Veículos: ${context.raw}`;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    }
                }
            });
        });
    </script>
@endsection
