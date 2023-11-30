@extends('adminlte::page')
<script src="/public/js/jquery.min.js"></script>
<script src="/public/vendor/jquery/jquery.js"></script>
<script src="/public/vendor/jquery/jquery.min.js"></script>
<script src="/public/vendor/adminlte/dist/js/adminlte.min.js"></script>
<script src="/public/vendor/overlayScrollbars/js/jquery.overlayScrollbars.min.js"></script>
<script src="/public/vendor/popper/popper.min.js"></script>
<script src="/public/vendor/popper/popper-utils.min.js"></script>
<script src="/public/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

<link rel="stylesheet" href="/public/vendor/adminlte/dist/css/adminlte.css" />
<link rel="stylesheet" href="/public/vendor/fontawesome-free/css/all.min.css" />

@section('plugins.Chartjs', true)

@section('title', 'Painel | Preço Motos')

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
                    "showDuration": "600",
                    "hideDuration": "1000",
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
                    "showDuration": "600",
                    "hideDuration": "1000",
                    "timeOut": "5000",
                    "extendedTimeOut": "1000",
                    "showEasing": "swing",
                    "hideEasing": "linear",
                    "showMethod": "fadeIn",
                    "hideMethod": "fadeOut"
                }
                toastr.success('{{session('create')}}');
            @endif
    </script>

@endsection

@section('content_header')
    <h1 style="display: flex; justify-content:space-between; padding: 0 20px 0 20px; margin-bottom:10px;">
        Preço para Motos
    </h1>
@endsection

@section('content')
    <div class="card">
        @if ($errors->any())
            <div class="card-header">
                <div class="alert alert-danger" role="alert">
                    Preencha os Campos Solicitados
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{$error}}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif
        <div class="card-body">
            <x-price.form :price="$priceMotorcycle" :route='$route' />
        </div>
    </div>
@endsection

