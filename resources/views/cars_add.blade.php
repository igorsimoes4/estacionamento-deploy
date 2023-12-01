@extends('adminlte::page')
<script src="https://estacionamento-deploy.vercel.app/public/js/jquery.min.js"></script>
    <script src="https://estacionamento-deploy.vercel.app/public/adminlte/dist/js/adminlte.min.js"></script>
    <script src="https://estacionamento-deploy.vercel.app/public/overlayScrollbars/js/jquery.overlayScrollbars.min.js"></script>
    <script src="https://estacionamento-deploy.vercel.app/public/popper/popper.min.js"></script>
    <script src="https://estacionamento-deploy.vercel.app/public/bootstrap/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://estacionamento-deploy.vercel.app/public/adminlte/dist/css/adminlte.css" />
    <link rel="stylesheet" href="https://estacionamento-deploy.vercel.app/public/fontawesome-free/css/all.min.css" />

@section('title', 'Nova Página')

@section('content_header')
    <h1 style="display: flex; justify-content:space-between; padding: 0 20px 0 20px; margin-bottom:10px;">
        Adicionar Veículo

    </h1>
@endsection

@section('css')
    <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css"/>
@endsection

@section('js')
<script src="https://code.jquery.com/jquery-3.3.1.min.js" integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8=" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.inputmask/3.3.4/jquery.inputmask.bundle.min.js">
    $(document).ready(function(){
            $('.placa').inputmask('(999)-999-9999');
    });
</script>
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
            <x-create.form />
        </div>

    </div>


    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.inputmask/3.3.4/jquery.inputmask.bundle.min.js">
        $(document).ready(function(){
                $('.placa').inputmask('(999)-999-9999');
        });
    </script>
    <script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js"></script>


@endsection
