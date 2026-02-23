@extends('adminlte::page')

@section('title', 'Painel | Novo Usuario')

@section('content_header')
    <div class="d-flex flex-wrap justify-content-between align-items-center" style="gap: 10px;">
        <div>
            <h1 class="m-0">Novo usuario</h1>
            <p class="text-muted m-0">Crie um acesso administrativo e defina o perfil do sistema.</p>
        </div>
        <a href="{{ route('users.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left mr-1"></i> Voltar
        </a>
    </div>
@endsection

@section('content')
    <div class="card report-card">
        <div class="card-body">
            <form method="POST" action="{{ route('users.store') }}">
                @csrf
                @include('users.form')

                <div class="d-flex mt-3" style="gap: 8px;">
                    <button type="submit" class="btn btn-theme">
                        <i class="fas fa-save mr-1"></i> Salvar usuario
                    </button>
                    <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
@endsection
