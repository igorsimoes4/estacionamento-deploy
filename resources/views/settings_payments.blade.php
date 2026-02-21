@extends('adminlte::page')

@section('adminlte_css')
    <link rel="icon" type="image/png" href="{{ asset('img/LogoEstacionamento.png') }}">
    <link rel="stylesheet" href="{{ asset('adminlte/dist/css/adminlte.css') }}" />
    <link rel="stylesheet" href="{{ asset('fontawesome-free/css/all.min.css') }}" />
    @parent
@endsection

@section('title', 'Painel | Metodos de Pagamento')

@section('css')
    <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css"/>
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
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item" aria-current="page"><a href="/painel">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Metodos de Pagamento</li>
        </ol>
    </nav>
    <div style="display: flex; justify-content:space-between; align-items:center; padding: 0 20px 0 20px; margin-bottom:10px;">
        <h1 style="margin:0;">Metodos de Pagamento e Gateways</h1>
        <a href="{{ route('settings') }}" class="btn btn-outline-primary btn-sm">
            <i class="fas fa-info-circle mr-1"></i> Informacoes do Estacionamento
        </a>
    </div>
@endsection

@section('content')
    <div class="card">
        @if ($errors->any())
            <div class="card-header">
                <div class="alert alert-danger mb-0" role="alert">
                    Verifique os campos destacados e tente novamente.
                </div>
            </div>
        @endif
        <div class="card-body">
            <x-settings.payment-form :estacionamentos='$estacionamento' :route='$route' />
        </div>
    </div>
@endsection
