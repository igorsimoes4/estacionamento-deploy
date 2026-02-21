@extends('adminlte::page')

@section('title', 'Painel | Veiculos')

@section('content_header')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3" style="gap: 12px;">
        <div>
            <h1 class="m-0">Gestao de Veiculos</h1>
        </div>
    </div>
@endsection

@section('content')
    @livewire('vehicles-table')
@endsection
