@extends('adminlte::page')

@section('title', 'Operação | Integrações e Saúde')

@section('content_header')
    <h1 class="m-0">Integrações (cancela/catraca/apps) e Saúde do Sistema</h1>
@endsection

@section('content')
    @if (session('create'))
        <div class="alert alert-success">{{ session('create') }}</div>
    @endif

    <div class="row">
        <div class="col-lg-6 mb-3">
            <div class="card theme-card h-100">
                <div class="card-header"><strong>Nova integração</strong></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('integrations.store') }}">
                        @csrf
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <input class="form-control" type="text" name="name" placeholder="Nome" required>
                            </div>
                            <div class="col-md-6 mb-2">
                                <select class="form-control" name="type" required>
                                    <option value="anpr">ANPR/OCR</option>
                                    <option value="cancela">Cancela</option>
                                    <option value="catraca">Catraca</option>
                                    <option value="fiscal">Fiscal</option>
                                    <option value="webhook">Webhook</option>
                                    <option value="payment">Pagamento</option>
                                    <option value="app">App/terceiro</option>
                                </select>
                            </div>
                            <div class="col-md-12 mb-2">
                                <input class="form-control" type="url" name="base_url" placeholder="URL base">
                            </div>
                            <div class="col-md-6 mb-2">
                                <input class="form-control" type="text" name="auth_token" placeholder="Token">
                            </div>
                            <div class="col-md-6 mb-2">
                                <input class="form-control" type="text" name="auth_secret" placeholder="Secret">
                            </div>
                            <div class="col-md-12 mb-2">
                                <textarea class="form-control" name="settings" rows="3" placeholder='JSON de configurações ex: {"timeout":15}'></textarea>
                            </div>
                            <div class="col-md-12 mb-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" checked>
                                    <label class="form-check-label" for="is_active">Ativa</label>
                                </div>
                            </div>
                        </div>
                        <button class="btn btn-theme" type="submit">Salvar integração</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-3">
            <div class="card theme-card h-100 overflow-hidden">
                <div class="card-header"><strong>Saúde do sistema</strong></div>
                <div class="table-responsive">
                    <table class="table table-theme mb-0">
                        <thead>
                            <tr>
                                <th>Check</th>
                                <th>Status</th>
                                <th>Mensagem</th>
                                <th>Data</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($healthChecks as $check)
                                <tr>
                                    <td>{{ $check->check_key }}</td>
                                    <td>
                                        <span class="badge {{ $check->status === 'ok' ? 'badge-success' : 'badge-danger' }}">
                                            {{ strtoupper($check->status) }}
                                        </span>
                                    </td>
                                    <td>{{ $check->message }}</td>
                                    <td>{{ optional($check->checked_at)->format('d/m/Y H:i') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4">Nenhum health check disponível.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card theme-card overflow-hidden">
                <div class="card-header"><strong>Integrações cadastradas</strong></div>
                <div class="table-responsive">
                    <table class="table table-theme mb-0">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Tipo</th>
                                <th>URL</th>
                                <th>Status</th>
                                <th>Última saúde</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($integrations as $integration)
                                <tr>
                                    <td>{{ $integration->name }}</td>
                                    <td>{{ strtoupper($integration->type) }}</td>
                                    <td>{{ $integration->base_url ?: '-' }}</td>
                                    <td>
                                        <span class="badge {{ $integration->is_active ? 'badge-success' : 'badge-secondary' }}">
                                            {{ $integration->is_active ? 'ATIVA' : 'INATIVA' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div>{{ $integration->last_health_status ?: '-' }}</div>
                                        <div class="small text-muted">{{ optional($integration->last_checked_at)->format('d/m/Y H:i') ?: '-' }}</div>
                                    </td>
                                    <td>
                                        <form method="POST" action="{{ route('integrations.update', $integration) }}" class="d-flex" style="gap: 6px;">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="name" value="{{ $integration->name }}">
                                            <input type="hidden" name="base_url" value="{{ $integration->base_url }}">
                                            <input type="hidden" name="auth_token" value="{{ $integration->auth_token }}">
                                            <input type="hidden" name="auth_secret" value="{{ $integration->auth_secret }}">
                                            <input type="hidden" name="settings" value='{{ json_encode($integration->settings, JSON_UNESCAPED_UNICODE) }}'>
                                            <input type="hidden" name="is_active" value="{{ $integration->is_active ? 0 : 1 }}">
                                            <button class="btn btn-sm btn-outline-primary" type="submit">
                                                {{ $integration->is_active ? 'Desativar' : 'Ativar' }}
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4">Nenhuma integração cadastrada.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
