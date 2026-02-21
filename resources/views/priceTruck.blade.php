@extends('adminlte::page')

@section('adminlte_css')
    <link rel="icon" type="image/png" href="{{ asset('img/LogoEstacionamento.png') }}">
    @parent
@endsection

@section('title', 'Painel | Precos Caminhonetes')

@section('css')
    <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css" />
@endsection

@section('js')
    <script src="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
    <script>
        @if ($errors->any())
            @foreach ($errors->all() as $error)
                toastr.error('{{ $error }}');
            @endforeach
        @endif

        @if (session('create'))
            toastr.success('{{ session('create') }}');
        @endif
    </script>
@endsection

@section('content_header')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3" style="gap: 12px;">
        <div>
            <h1 class="m-0">Precos de Caminhonetes</h1>
            <p class="text-muted m-0">Gerencie os valores para tickets e mensalidade de caminhonetes.</p>
        </div>
        <div class="d-flex flex-wrap" style="gap: 8px;">
            <a href="{{ route('priceCar') }}" class="btn btn-outline-primary">
                <i class="fas fa-car mr-1"></i> Carros
            </a>
            <a href="{{ route('priceMotorcycle') }}" class="btn btn-outline-primary">
                <i class="fas fa-motorcycle mr-1"></i> Motos
            </a>
            <a href="{{ route('priceTruck') }}" class="btn {{ request()->routeIs('priceTruck') ? 'btn-theme' : 'btn-outline-primary' }}" style="color: {{ request()->routeIs('priceTruck') ? '#fff' : 'inherit' }};">
                <i class="fas fa-truck-pickup mr-1"></i> Caminhonetes
            </a>
        </div>
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
            <div class="theme-panel mb-4">
                <p>Tabela ativa</p>
                <h4>Configuracao de precos para caminhonetes</h4>
            </div>
            <x-price.form :price="$priceTruck" :route="$route" />
        </div>
    </div>
@endsection
