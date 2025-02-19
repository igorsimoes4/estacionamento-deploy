@extends('adminlte::page')

@section('adminlte_css')
    <link rel="icon" type="image/png" href="{{ asset('img/LogoEstacionamento.png') }}">
    <link rel="stylesheet" href="{{ asset('fontawesome-free/css/all.min.css') }}" />
    @parent

    <style>
        .wrapper, body, html {
            min-height: 130vh !important;
        }
        body, html {
            background-color: #F4F6F9;
        }
        /* Estilizaçbão do relógio digital */
        #clock-container {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 60px;
            background: #343a40;
            border-radius: 8px;
            padding: 10px 20px;
            box-shadow: inset 0 0 8px rgba(255, 255, 255, 0.1), 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        #rel {
            font-size: 36px;
            font-weight: bold;
            font-family: 'Courier New', monospace;
            color: #ffffff;
            text-shadow: 0 0 10px rgba(255, 255, 255, 0.3);
            background: none;
            border: none;
            outline: none;
            width: 100%;
            text-align: center;
        }

        /* Responsividade para os botões */
        @media (max-width: 768px) {
            .btn-group {
                display: flex;
                flex-direction: column;
                gap: 10px;
            }

            .btn-custom {
                width: 100%;
                font-size: 16px;
            }

            .table-responsive {
                -ms-overflow-style: none;
                scrollbar-width: none;
            }

            .table-responsive::-webkit-scrollbar {
                display: none;
            }

            .table {
                width: 100%;
                overflow-x: auto;
                display: block;
            }

            /* Ajustes para o layout da tabela em telas pequenas */
            .table th, .table td {
                padding: 8px;
                font-size: 14px;
            }

            #clock-container {
                padding: 10px;
                font-size: 28px;
            }

            .col-sm-12 {
                margin-bottom: 15px;
            }

            .btn-block {
                width: 100%;
            }
        }
    </style>
@endsection

@section('title', 'Painel | Carros Estacionados')

@section('css')
    <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css" />
    <meta http-equiv="refresh" content="300">
@endsection

@section('js')
    <script src="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        @if ($errors->any())
            @foreach ($errors->all() as $error)
                toastr.error('{{ $error }}');
            @endforeach
        @endif

        @if (session('create'))
            toastr.success('{{ session('create') }}');
        @endif

        @if (session('delete_car'))
            toastr.error('{{ session('delete_car') }}');
        @endif

        // Relógio digital
        function relogio() {
            let data = new Date();
            let hora = String(data.getHours()).padStart(2, '0');
            let minuto = String(data.getMinutes()).padStart(2, '0');
            let segundo = String(data.getSeconds()).padStart(2, '0');
            document.getElementById("rel").value = `${hora}:${minuto}:${segundo}`;
        }

        setInterval(relogio, 1000);
        relogio();

        // Inicializa o DataTable com responsividade
        $(document).ready(function() {
            $('#car-table').DataTable({
                responsive: true
            });
        });
    </script>
@endsection

@section('content_header')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/painel"> Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Carros Estacionados</li>
        </ol>
    </nav>
    <div class="d-flex justify-content-between px-4 mb-3">
        <h1>Veículos no Estacionamento</h1>
        <a class="btn btn-md btn-success" href="{{ route('cars.create') }}">
            <i class="fa fa-plus-circle mr-2"></i> Adicionar Veículo
        </a>
    </div>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <div class="row">
                <div class="col-sm-12 col-md-3">
                    <div id="clock-container">
                        <input disabled class="form-control form-control-lg" type="text" id="rel">
                    </div>
                </div>
                <div class="col-sm-12 col-md-6"></div>
                <div class="col-sm-12 col-md-3">
                    <form action="{{ route('search') }}" method="POST">
                        @csrf
                        <div class="input-group">
                            <input type="search" name="search" id="searchInput"
                                class="form-control form-control-lg @error('search') is-invalid @enderror"
                                placeholder="Digite a Placa">
                            <div class="input-group-append">
                                <button class="btn btn-lg btn-default btn-block"><i class="fa fa-search"></i></button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-bordered" id="car-table">
                    <thead>
                        <tr>
                            <th>Tipo</th>
                            <th>Modelo</th>
                            <th>Placa</th>
                            <th>Hora Entrada</th>
                            <th>Total Estacionado</th>
                            <th>Preço</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($cars as $car)
                            @php
                                date_default_timezone_set('America/Sao_Paulo');
                                $saida = new DateTime();
                                $entrada = new DateTime($car->created_at);
                                $tempo = date_diff($entrada, $saida);
                            @endphp
                            <tr>
                                <td>{{ $car->tipo_car }}</td>
                                <td>{{ $car->modelo }}</td>
                                <td>{{ $car->placa }}</td>
                                <td>{{ $car->entrada }}</td>
                                <td>
                                    @php
                                        echo ($tempo->m ? "$tempo->m meses " : '') . 
                                             ($tempo->d ? "$tempo->d dias " : '') . 
                                             ($tempo->h ? "$tempo->h horas " : '') . 
                                             ($tempo->i ? "$tempo->i minutos" : '1 minuto');
                                    @endphp
                                </td>
                                <td>R$ {{ number_format($car->price, 2, ',', '') }}</td>
                                <td>
                                    <div class="row justify-content-center align-items-center flex-wrap flex-md-nowrap text-center text-md-left mb-2 mb-md-0 flex-grow-1" style="gap:10px;">
                                        <a class="btn btn-sm btn-warning modal-btn" data-id="{{ $car->id }}" data-toggle="modal" data-target="#myModal">
                                            <i class="fas fa-eye"></i> Visualizar
                                        </a>
                                        <a class="btn btn-sm btn-danger" href="#" onclick="event.preventDefault(); document.getElementById('delete-form-{{ $car->id }}').submit();">
                                            <i class="fas fa-edit"></i> Finalizar
                                        </a>
                                        <form id="delete-form-{{ $car->id }}" action="{{ route('cars.destroy', $car->id) }}" method="POST" style="display: none;">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                        <x-print.form :car="$car" :entrada="$entrada" />
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>        
        <div class="card-footer">
            <div style="display: flex; justify-content:flex-end;padding: 0 20px 0 20px;">
                {{ $cars->appends(['search' => request('search')])->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>

    <x-show.modal />
@endsection
