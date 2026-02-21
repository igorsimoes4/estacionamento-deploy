@extends('adminlte::page')

@section('adminlte_css')
    <link rel="icon" type="image/png" href="{{ asset('img/LogoEstacionamento.png') }}">
    <link rel="stylesheet" href="{{ asset('adminlte/dist/css/adminlte.css') }}" />
    <link rel="stylesheet" href="{{ asset('fontawesome-free/css/all.min.css') }}" />
    @parent
@endsection

@section('title', 'Novo Veiculo')

@section('css')
    <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css" />
@endsection

@section('js')
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.11/jquery.mask.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
    <script>
        $('#placa').mask('AAA-9999');

        @if ($errors->any())
            @foreach ($errors->all() as $error)
                toastr.error('{{ $error }}');
            @endforeach
        @endif
    </script>
@endsection

@section('content_header')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3" style="gap: 12px;">
        <div>
            <h1 class="m-0">Adicionar Veiculo</h1>
            <p class="text-muted m-0">Cadastre uma nova entrada no patio com os dados principais do veiculo.</p>
        </div>
        <a href="{{ route('cars.index') }}" class="btn btn-outline-primary">
            <i class="fas fa-list mr-1"></i> Ver Veiculos
        </a>
    </div>
@endsection

@section('content')
    <div class="card theme-card">
        @if ($errors->any())
            <div class="card-header border-0 pb-0">
                <div class="alert alert-danger mb-0" role="alert">
                    Verifique os campos obrigatorios antes de salvar.
                </div>
            </div>
        @endif
        <div class="card-body">
            <x-cars.create.form />
        </div>
    </div>
@endsection
