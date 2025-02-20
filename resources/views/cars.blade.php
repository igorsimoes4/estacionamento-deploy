@extends('adminlte::page')

@section('adminlte_css')
    <link rel="icon" type="image/png" href="{{ asset('img/LogoEstacionamento.png') }}">
    <link rel="stylesheet" href="{{ asset('fontawesome-free/css/all.min.css') }}" />
    <link rel="stylesheet" href="//cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css" />
    @parent

    <style>
        /* Ajusta a altura mínima para evitar cortes na tela */
        .wrapper,
        body,
        html {
            min-height: 130vh !important;
        }

        /* Define o fundo geral */
        body,
        html {
            background-color: #F4F6F9;
        }

        /* Estilização do relógio digital */
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

        /* Melhorias na tabela */
        .table {
            border-collapse: collapse;
            width: 100%;
        }

        .table thead {
            background-color: #343a40;
            color: #ffffff;
            text-transform: uppercase;
        }

        .table tbody tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        .table tbody tr:hover {
            background-color: #ddd;
        }

        /* Melhorias na responsividade */
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

            .table th,
            .table td {
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

        /* Personalização do DataTables */
        /* Caixa de pesquisa */
        .dataTables_wrapper .dataTables_filter input {
            border-radius: 5px;
            padding: 5px;
            border: 1px solid #ccc;
        }

        /* Paginação */
        .dataTables_wrapper .dataTables_paginate .paginate_button {
            border-radius: 5px;
            margin: 2px;
            padding: 5px 10px;
            border: 1px solid #cdd7e0;
            background-color: #cdd7e0;
            color: black;
        }
        /* Ajuste para botões de ação */
        .btn-sm {
            padding: 5px 10px;
            font-size: 14px;
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
    <script src="//cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
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
        $('#car-table').DataTable({
            responsive: true,
            order: [
                [3, "desc"],
            ], // Ordena pela coluna de entrada (index 3)
            columnDefs: [{
                    type: 'datetime',
                    targets: 3,
                } // Define a coluna 3 como data/hora
            ],
            language: {
                url: "https://cdn.datatables.net/plug-ins/1.10.21/i18n/Portuguese.json",
            }
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
                <div class="col">
                    <div id="clock-container">
                        <input disabled class="form-control form-control-lg" type="text" id="rel">
                    </div>
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
                            <th>Data Entrada</th>
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
                                <td data-order="{{ $car->created_at->format('Y-m-d H:i:s') }}">
                                    {{ $car->created_at->format('d/m/Y H:i:s') }}
                                </td>
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
                                    <div class="row justify-content-center align-items-center flex-wrap flex-md-nowrap text-center text-md-left mb-2 mb-md-0 flex-grow-1"
                                        style="gap:10px;">
                                        <a class="btn btn-sm btn-warning modal-btn" data-id="{{ $car->id }}"
                                            data-toggle="modal" data-target="#myModal">
                                            <i class="fas fa-eye"></i> Visualizar
                                        </a>
                                        <a class="btn btn-sm btn-danger" href="#"
                                            onclick="event.preventDefault(); document.getElementById('delete-form-{{ $car->id }}').submit();">
                                            <i class="fas fa-edit"></i> Finalizar
                                        </a>
                                        <form id="delete-form-{{ $car->id }}"
                                            action="{{ route('cars.destroy', $car->id) }}" method="POST"
                                            style="display: none;">
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
    </div>

    <x-show.modal />
@endsection
