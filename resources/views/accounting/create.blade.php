@extends('adminlte::page')

@section('title', 'Painel | Contabilidade')

@section('content_header')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-2" style="gap: 12px;">
        <div>
            <h1 class="m-0">Novo Lancamento Contabil</h1>
            <p class="text-muted m-0">Registre uma receita ou despesa para composicao financeira.</p>
        </div>
    </div>
@endsection

@section('content')
    <div class="card report-card">
        <div class="card-body">
            @include('accounting.form')
        </div>
    </div>
@endsection

