@extends('adminlte::page')

@section('title', 'Painel | Usuarios')

@section('content_header')
    <div class="report-hero theme-fade-in">
        <div class="d-flex flex-wrap justify-content-between align-items-center" style="gap: 12px;">
            <div>
                <h1 class="m-0">Usuarios e Perfis</h1>
                <p>Cadastre operadores e financeiro com controle de funcao e status de acesso.</p>
            </div>
            <a href="{{ route('users.create') }}" class="btn btn-light btn-sm">
                <i class="fas fa-user-plus mr-1"></i> Novo usuario
            </a>
        </div>
    </div>
@endsection

@section('content')
    @if (session('success'))
        <div class="alert alert-success mt-3">{{ session('success') }}</div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger mt-3">{{ session('error') }}</div>
    @endif

    <div class="row mt-3">
        <div class="col-md-4 mb-2">
            <div class="theme-panel h-100">
                <p>Total de usuarios</p>
                <h4>{{ number_format((int) $users->total(), 0, ',', '.') }}</h4>
            </div>
        </div>
        <div class="col-md-4 mb-2">
            <div class="theme-panel h-100">
                <p>Administradores ativos</p>
                <h4>{{ number_format((int) $activeAdmins, 0, ',', '.') }}</h4>
            </div>
        </div>
        <div class="col-md-4 mb-2">
            <div class="theme-panel h-100">
                <p>Usuario logado</p>
                <h4>{{ auth()->user()->name ?? '-' }}</h4>
            </div>
        </div>
    </div>

    <div class="card report-card mt-2">
        <div class="card-body">
            <form method="GET" action="{{ route('users.index') }}" class="mb-3">
                <div class="row">
                    <div class="col-md-4 mb-2">
                        <label class="small text-uppercase font-weight-bold">Busca</label>
                        <input type="text" name="q" value="{{ $filters['q'] }}" class="form-control"
                            placeholder="Nome ou e-mail">
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="small text-uppercase font-weight-bold">Perfil</label>
                        <select name="role" class="form-control">
                            <option value="">Todos</option>
                            @foreach ($roles as $role => $label)
                                <option value="{{ $role }}" {{ $filters['role'] === $role ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 mb-2">
                        <label class="small text-uppercase font-weight-bold">Status</label>
                        <select name="status" class="form-control">
                            <option value="all" {{ $filters['status'] === 'all' ? 'selected' : '' }}>Todos</option>
                            <option value="active" {{ $filters['status'] === 'active' ? 'selected' : '' }}>Ativos</option>
                            <option value="inactive" {{ $filters['status'] === 'inactive' ? 'selected' : '' }}>Inativos</option>
                        </select>
                    </div>
                    <div class="col-md-1 mb-2">
                        <label class="small text-uppercase font-weight-bold">Pagina</label>
                        <select name="per_page" class="form-control">
                            @foreach ([10, 15, 20, 50] as $pp)
                                <option value="{{ $pp }}" {{ (int) $filters['per_page'] === $pp ? 'selected' : '' }}>
                                    {{ $pp }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 mb-2 d-flex align-items-end" style="gap: 8px;">
                        <button class="btn btn-theme btn-sm flex-grow-1" type="submit">Filtrar</button>
                        <a href="{{ route('users.index') }}" class="btn btn-outline-secondary btn-sm">Limpar</a>
                    </div>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-theme mb-0">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>E-mail</th>
                            <th>Perfil</th>
                            <th>Status</th>
                            <th>Criado em</th>
                            <th class="text-center">Acoes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $user)
                            <tr>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>
                                    <span class="badge badge-info">{{ $roles[$user->role] ?? strtoupper($user->role) }}</span>
                                </td>
                                <td>
                                    <span class="badge {{ $user->is_active ? 'badge-success' : 'badge-secondary' }}">
                                        {{ $user->is_active ? 'Ativo' : 'Inativo' }}
                                    </span>
                                </td>
                                <td>{{ optional($user->created_at)->format('d/m/Y H:i') }}</td>
                                <td class="text-center">
                                    <a href="{{ route('users.edit', $user) }}" class="btn btn-sm btn-outline-primary" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>

                                    <form method="POST" action="{{ route('users.destroy', $user) }}" class="d-inline"
                                        onsubmit="return confirm('Deseja excluir este usuario?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger"
                                            title="Excluir" {{ auth()->id() === $user->id ? 'disabled' : '' }}>
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">Nenhum usuario encontrado.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $users->onEachSide(1)->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>
@endsection
