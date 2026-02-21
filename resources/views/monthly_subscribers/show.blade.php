@extends('adminlte::page')

@section('adminlte_css')
    <link rel="icon" type="image/png" href="{{ asset('img/LogoEstacionamento.png') }}">
    <link rel="stylesheet" href="{{ asset('fontawesome-free/css/all.min.css') }}" />
    @parent
@endsection

@section('content')
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Detalhes do Mensalista</h5>
                    <div class="d-flex" style="gap: 8px;">
                        <a href="{{ route('monthly-access.login') }}" class="btn btn-outline-primary btn-sm" target="_blank" rel="noopener">
                            <i class="fas fa-user-lock"></i> Portal
                        </a>
                        <a href="{{ route('monthly-subscribers.edit', $monthlySubscriber) }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-edit"></i> Editar
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-2"><strong>Nome:</strong> {{ $monthlySubscriber->name }}</div>
                        <div class="col-md-6 mb-2"><strong>CPF:</strong> {{ $monthlySubscriber->cpf }}</div>
                        <div class="col-md-6 mb-2"><strong>Telefone:</strong> {{ $monthlySubscriber->phone }}</div>
                        <div class="col-md-6 mb-2"><strong>E-mail:</strong> {{ $monthlySubscriber->email ?: 'Nao informado' }}</div>
                        <div class="col-md-6 mb-2"><strong>Placa:</strong> {{ $monthlySubscriber->vehicle_plate }}</div>
                        <div class="col-md-6 mb-2"><strong>Tipo:</strong> {{ ucfirst($monthlySubscriber->vehicle_type) }}</div>
                        <div class="col-md-6 mb-2"><strong>Modelo:</strong> {{ $monthlySubscriber->vehicle_model ?: 'Nao informado' }}</div>
                        <div class="col-md-6 mb-2"><strong>Cor:</strong> {{ $monthlySubscriber->vehicle_color ?: 'Nao informado' }}</div>
                        <div class="col-md-6 mb-2"><strong>Inicio:</strong> {{ optional($monthlySubscriber->start_date)->format('d/m/Y') }}</div>
                        <div class="col-md-6 mb-2"><strong>Termino:</strong> {{ optional($monthlySubscriber->end_date)->format('d/m/Y') }}</div>
                        <div class="col-md-6 mb-2"><strong>Valor Mensal:</strong> R$ {{ number_format((float) $monthlySubscriber->monthly_fee, 2, ',', '.') }}</div>
                        <div class="col-md-6 mb-2">
                            <strong>Status:</strong>
                            <span class="badge {{ $monthlySubscriber->is_active ? 'bg-success' : 'bg-danger' }}">
                                {{ $monthlySubscriber->is_active ? 'Ativo' : 'Inativo' }}
                            </span>
                        </div>
                        <div class="col-md-6 mb-2">
                            <strong>Acesso ao Portal:</strong>
                            @if (!$monthlySubscriber->access_enabled)
                                <span class="badge bg-secondary">Bloqueado</span>
                            @elseif (!empty($monthlySubscriber->access_password))
                                <span class="badge bg-success">Liberado</span>
                            @else
                                <span class="badge bg-warning text-dark">Pendente</span>
                            @endif
                        </div>
                        <div class="col-md-6 mb-2">
                            <strong>Ultimo Acesso:</strong>
                            {{ optional($monthlySubscriber->access_last_login_at)->format('d/m/Y H:i') ?: 'Sem acesso registrado' }}
                        </div>
                        <div class="col-12 mt-2"><strong>Observacoes:</strong> {{ $monthlySubscriber->observations ?: 'Sem observacoes' }}</div>
                    </div>
                </div>
                <div class="card-footer text-end">
                    <a href="{{ route('monthly-subscribers.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Voltar
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
