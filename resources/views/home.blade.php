@extends('adminlte::page')

@section('title', 'Painel | Home')

@section('content_header')
    <div class="d-flex flex-wrap justify-content-between align-items-center" style="gap: 12px;">
        <div>
            <h1 class="m-0">Painel de Controle</h1>
            <p class="text-muted m-0">Acompanhamento operacional em tempo real do estacionamento.</p>
        </div>
        <span class="badge badge-light p-2">Atualizacao automatica ativa</span>
    </div>
@endsection

@section('content')
    @livewire('dashboard-stats')

    <div class="row mt-2">
        <div class="col-lg-3 mb-3">
            <a href="{{ route('cars.index') }}" class="theme-quick-link">
                <p>Operacao</p>
                <h5>Monitorar Patio</h5>
            </a>
        </div>
        <div class="col-lg-3 mb-3">
            <a href="{{ route('monthly-subscribers.index') }}" class="theme-quick-link">
                <p>Assinaturas</p>
                <h5>Gerenciar Mensalistas</h5>
            </a>
        </div>
        <div class="col-lg-3 mb-3">
            <a href="{{ route('reports.index') }}" class="theme-quick-link">
                <p>Inteligencia</p>
                <h5>Emitir Relatorios</h5>
            </a>
        </div>
        <div class="col-lg-3 mb-3">
            <a href="{{ route('accounting.index') }}" class="theme-quick-link">
                <p>Financeiro</p>
                <h5>Contabilidade</h5>
            </a>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-12">
            <div class="theme-panel">
                <p>Dica de fluxo</p>
                <h4>Use a tela de veiculos para finalizar, imprimir ticket e filtrar status em tempo real sem recarregar a pagina.</h4>
            </div>
        </div>
    </div>
@endsection
