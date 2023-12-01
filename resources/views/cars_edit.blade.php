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
<script src="{{ asset('js/jquery.min.js') }}"></script>
    <script src="{{ asset('adminlte/dist/js/adminlte.min.js') }}"></script>
    <script src="{{ asset('overlayScrollbars/js/jquery.overlayScrollbars.min.js') }}"></script>
    <script src="{{ asset('popper/popper.min.js') }}"></script>
    <script src="{{ asset('bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<link rel="stylesheet" href="{{ asset('adminlte/dist/css/adminlte.css') }}" />
    <link rel="stylesheet" href="{{ asset('fontawesome-free/css/all.min.css') }}" />

@section('title', 'Nova Página')

@section('content_header')
    <h1 style="display: flex; justify-content:space-between; padding: 0 20px 0 20px; margin-bottom:10px;">
        Editar Veículo
    </h1>
@endsection

@section('css')
    <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css"/>
@endsection

@section('js')
    <script src="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
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
    </script>
@endsection

@section('js')
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>

            function exibemensagem() {
                const Toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 1000,
                    timerProgressBar: true,
                    didOpen: (toast) => {
                        toast.addEventListener('mouseenter', Swal.stopTimer)
                        toast.addEventListener('mouseleave', Swal.resumeTimer)
                    }
                });

                    Toast.fire({
                    icon: 'success',
                    title: 'Página Adicionada com sucesso'
                });
            };
    </script>
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
            <x-edit.form :car="$car"/>
        </div>

    </div>


@endsection
