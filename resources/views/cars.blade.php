@extends('adminlte::page')
@push('styles')
    <link rel="stylesheet" href="{{ asset('adminlte/dist/css/adminlte.css') }}" />
    <link rel="stylesheet" href="{{ asset('fontawesome-free/css/all.min.css') }}" />
@endpush

@push('scripts')
    <script src="{{ asset('js/jquery.min.js') }}"></script>
    <script src="{{ asset('adminlte/dist/js/adminlte.min.js') }}"></script>
    <script src="{{ asset('overlayScrollbars/js/jquery.overlayScrollbars.min.js') }}"></script>
    <script src="{{ asset('popper/popper.min.js') }}"></script>
    <script src="{{ asset('bootstrap/js/bootstrap.bundle.min.js') }}"></script>
@endpush
<script src="https://estacionamento-deploy.vercel.app/public/js/jquery.min.js"></script>
    <script src="https://estacionamento-deploy.vercel.app/public/adminlte/dist/js/adminlte.min.js"></script>
    <script src="https://estacionamento-deploy.vercel.app/public/overlayScrollbars/js/jquery.overlayScrollbars.min.js"></script>
    <script src="https://estacionamento-deploy.vercel.app/public/popper/popper.min.js"></script>
    <script src="https://estacionamento-deploy.vercel.app/public/bootstrap/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://estacionamento-deploy.vercel.app/public/adminlte/dist/css/adminlte.css" />
    <link rel="stylesheet" href="https://estacionamento-deploy.vercel.app/public/fontawesome-free/css/all.min.css" />

@section('plugins.Chartjs', true)

@section('title', 'Painel | Carros Estacionados')

@section('css')
    <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css"/>
    <meta http-equiv="refresh" content="300">
    <style>
        .form-control:disabled {
            background-color: transparent;
            border-color: #949494;
        }
    </style>
@endsection

@section('js')
    <script src="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>

            @if ($errors->any())
                @foreach ($errors->all() as $error)
                toastr.options = {
                    "closeButton": true,
                    "debug": false,
                    "newestOnTop": false,
                    "progressBar": true,
                    "positionClass": "toast-top-right",
                    "preventDuplicates": false,
                    "onclick": null,
                    "showDuration": "1200",
                    "hideDuration": "1200",
                    "timeOut": "5000",
                    "extendedTimeOut": "1000",
                    "showEasing": "swing",
                    "hideEasing": "linear",
                    "showMethod": "fadeIn",
                    "hideMethod": "fadeOut"
                }
                    toastr.error('{{$error}}');
                @endforeach
            @endif
            @if (session('create'))
                toastr.options = {
                    "closeButton": true,
                    "debug": false,
                    "newestOnTop": false,
                    "progressBar": true,
                    "positionClass": "toast-top-right",
                    "preventDuplicates": false,
                    "onclick": null,
                    "showDuration": "1200",
                    "hideDuration": "1200",
                    "timeOut": "5000",
                    "extendedTimeOut": "1000",
                    "showEasing": "swing",
                    "hideEasing": "linear",
                    "showMethod": "fadeIn",
                    "hideMethod": "fadeOut"
                }
                toastr.success('{{session('create')}}');
            @endif
            @if (session('delete_car'))
                toastr.options = {
                    "closeButton": true,
                    "debug": false,
                    "newestOnTop": false,
                    "progressBar": true,
                    "positionClass": "toast-top-right",
                    "preventDuplicates": false,
                    "onclick": null,
                    "showDuration": "1200",
                    "hideDuration": "1200",
                    "timeOut": "5000",
                    "extendedTimeOut": "1000",
                    "showEasing": "swing",
                    "hideEasing": "linear",
                    "showMethod": "fadeIn",
                    "hideMethod": "fadeOut"
                }
                toastr.error('{{session('delete_car')}}');
            @endif

            function relogio() {
                var data = new Date();
                var hora = data.getHours();
                var minuto = data.getMinutes();
                var segundo = data.getSeconds();

                if(hora < 10) {
                    hora = "0"+hora;
                }
                if(minuto < 10) {
                    minuto = "0"+minuto;
                }
                if(segundo < 10) {
                    segundo = "0"+segundo;
                }

                var horas = hora+":"+minuto+":"+segundo;
                document.getElementById("rel").value=horas;
            }

            var tempo = setInterval(relogio,1000);
    </script>

@endsection

@section('content_header')
    <h1 style="display: flex; justify-content:space-between; padding: 0 20px 0 20px; margin-bottom:10px;">
        Veículos no Estacionamento
        <a class="btn btn-md btn-success" href="{{route('cars.create')}}"><i style="margin-right: 5px; font-size:15px;" class="fa fa-plus-circle" aria-hidden="true"></i>  Adicionar Veículo</a>
    </h1>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <div class="row">
                <div class="col-md-3">
                    <input disabled style="text-align: center; font-size: 50px; border:none; background-color:#fff;" class="form-control form-control-lg" type="text" id="rel">
                </div>
                <div class="col-md-6">
                </div>
                <div class="col-md-3">
                    <form action="{{route('search')}}" method="POST">
                        @csrf
                        <div class="input-group">
                            <input type="search" name="search" id="searchInput" class="form-control form-control-lg @error('search') is-invalid @enderror" placeholder="Digite a Placa">
                            <div class="input-group-append">
                                <button class="btn btn-lg btn-default"><i class="fa fa-search"></i></button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <table class="table table-striped">
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

                            $hora = $tempo->h;
                            $minuto = $tempo->i;
                            $dia = $tempo->d;
                            $mes = $tempo->m;
                        @endphp
                            <tr>
                                <th>{{$car->tipo_car}}</th>
                                <th>{{$car->modelo}}</th>
                                <th>{{$car->placa}}</th>
                                <th>{{$car->entrada}}</th>
                                <th>
                                    @php
                                        $result = ($mes >= 1 ? "$mes meses " : "") .
                                                ($dia >= 1 ? ($dia == 1 ? "$dia dia " : "$dia dias ") : "") .
                                                ($hora >= 1 ? ($hora == 1 ? "$hora hora " : "$hora horas ") : "") .
                                                ($minuto >= 1 ? ($minuto == 1 ? "$minuto minuto" : "$minuto minutos") : "1 minuto");
                                        // if ($mes >= 1) {
                                        //     if ($dia <= 1) {
                                        //         if($dia >= 1) {
                                        //             if($hora >= 1) {
                                        //                 if($minuto >= 1) {
                                        //                     $result = "$mes meses $dia dia $hora horas $minuto minutos";
                                        //                 } else {
                                        //                     $result = "$mes meses $dia dia $hora horas";
                                        //                 }
                                        //             } else {
                                        //                 if($minuto >= 1) {
                                        //                     $result = "$mes meses $dia dia $minuto minutos";
                                        //                 } else {
                                        //                     $result = "$mes meses $dia dia";
                                        //                 }
                                        //             }
                                        //         } else {
                                        //             if($hora >= 1) {
                                        //                 if($minuto >= 1) {
                                        //                     $result = "$mes meses $hora horas $minuto minutos";
                                        //                 } else {
                                        //                     $result = "$mes meses $hora horas";
                                        //                 }
                                        //             } else {
                                        //                 if($minuto >= 1) {
                                        //                     $result = "$mes meses $minuto minutos";
                                        //                 } else {
                                        //                     $result = "$mes meses";
                                        //                 }
                                        //             }
                                        //         }
                                        //     } elseif ($dia <= 30) {
                                        //         if($dia >= 1) {
                                        //             if($hora >= 1) {
                                        //                 if($minuto >= 1) {
                                        //                     $result = "$mes meses $dia dias $hora horas $minuto minutos";
                                        //                 } else {
                                        //                     $result = "$mes meses $dia dias $hora horas";
                                        //                 }
                                        //             } else {
                                        //                 if($minuto >= 1) {
                                        //                     $result = "$mes meses $dia dias $minuto minutos";
                                        //                 } else {
                                        //                     $result = "$mes meses $dia dias";
                                        //                 }
                                        //             }
                                        //         } else {
                                        //             if($hora >= 1) {
                                        //                 if($minuto >= 1) {
                                        //                     $result = "$mes meses $hora horas $minuto minutos";
                                        //                 } else {
                                        //                     $result = "$mes meses $hora horas";
                                        //                 }
                                        //             } else {
                                        //                 if($minuto >= 1) {
                                        //                     $result = "$mes meses $minuto minutos";
                                        //                 } else {
                                        //                     $result = "$mes meses";
                                        //                 }
                                        //             }
                                        //         }
                                        //     }
                                        // }else {
                                        //     if ($dia <= 1) {
                                        //         if($dia >= 1) {
                                        //             if($hora >= 1) {
                                        //                 if($minuto >= 1) {
                                        //                     $result = "$dia dia $hora horas $minuto minutos";
                                        //                 } else {
                                        //                     $result = "$dia dia $hora horas";
                                        //                 }
                                        //             } else {
                                        //                 if($minuto >= 1) {
                                        //                     $result = "$dia dia $minuto minutos";
                                        //                 } else {
                                        //                     $result = "$dia dia";
                                        //                 }
                                        //             }
                                        //         } else {
                                        //             if($hora >= 1) {
                                        //                 if($minuto >= 1) {
                                        //                     $result = "$hora horas $minuto minutos";
                                        //                 } else {
                                        //                     $result = "$hora horas";
                                        //                 }
                                        //             } else {
                                        //                 if($minuto >= 1) {
                                        //                     $result = "$minuto minutos";
                                        //                 } else {
                                        //                     $result = "1 minuto";
                                        //                 }
                                        //             }
                                        //         }
                                        //     } elseif ($dia <= 30) {
                                        //         if($dia >= 1) {
                                        //             if($hora >= 1) {
                                        //                 if($minuto >= 1) {
                                        //                     $result = "$dia dias $hora horas $minuto minutos";
                                        //                 } else {
                                        //                     $result = "$dia dias $hora horas";
                                        //                 }
                                        //             } else {
                                        //                 if($minuto >= 1) {
                                        //                     $result = "$dia dias $minuto minutos";
                                        //                 } else {
                                        //                     $result = "$dia dias";
                                        //                 }
                                        //             }
                                        //         } else {
                                        //             if($hora >= 1) {
                                        //                 if($minuto >= 1) {
                                        //                     $result = "$hora horas $minuto minutos";
                                        //                 } else {
                                        //                     $result = "$hora horas";
                                        //                 }
                                        //             } else {
                                        //                 if($minuto >= 1) {
                                        //                     $result = "$minuto minutos";
                                        //                 } else {
                                        //                     $result = "1 minuto";
                                        //                 }
                                        //             }
                                        //         }
                                        //     }
                                        // }
                                        echo $result;
                                    @endphp
                                </th>
                                <th>R$ @php echo number_format($car->price, 2, ',', '')  @endphp </th>
                                <th width="300">
                                    <div class="row">
                                        <a style="margin-right: 5px;" class="btn btn-sm btn-warning modal-btn" data-id="{{ $car->id }}" data-toggle="modal" data-target="#myModal">
                                            <i style="margin-right: 5px; font-size:13px;" class="fas fa-solid fa-eye"></i> Visualizar
                                        </a>
                                        <a style="margin-right: 5px;" id="teste" class="btn btn-sm btn-danger" href="{{route('cars.edit', ['car' => $car->id])}}"><i style="margin-right: 5px; font-size:13px;" class="fas fa-solid fa-edit"></i> Finalizar</a>
                                        <x-print.form :car="$car" :entrada="$entrada" />
                                    </div>
                                </th>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer">
            <div style="display: flex; justify-content:flex-end;padding: 0 20px 0 20px;">
                {{$cars->links('pagination::bootstrap-4')}}
            </div>
        </div>
    </div>

    <x-show.modal />
@endsection
