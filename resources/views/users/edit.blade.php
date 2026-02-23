@extends('adminlte::page')

@section('title', 'Painel | Editar Usuario')

@section('content_header')
    <div class="d-flex flex-wrap justify-content-between align-items-center" style="gap: 10px;">
        <div>
            <h1 class="m-0">Editar usuario</h1>
            <p class="text-muted m-0">Atualize perfil, status e credenciais de acesso.</p>
        </div>
        <a href="{{ route('users.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left mr-1"></i> Voltar
        </a>
    </div>
@endsection

@section('content')
    <div class="card report-card">
        <div class="card-body">
            <form method="POST" action="{{ route('users.update', $user) }}">
                @csrf
                @method('PUT')
                @include('users.form')

                <div class="d-flex mt-3" style="gap: 8px;">
                    <button type="submit" class="btn btn-theme">
                        <i class="fas fa-save mr-1"></i> Atualizar usuario
                    </button>
                    <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
@endsection
