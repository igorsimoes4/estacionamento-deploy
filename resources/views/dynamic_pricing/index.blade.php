@extends('adminlte::page')

@section('title', 'Configuração | Preço Dinâmico')

@section('content_header')
    <h1 class="m-0">Regras de Preço Dinâmico</h1>
@endsection

@section('content')
    @if (session('create'))
        <div class="alert alert-success">{{ session('create') }}</div>
    @endif

    <div class="card theme-card mb-3">
        <div class="card-header"><strong>Nova regra</strong></div>
        <div class="card-body">
            <form method="POST" action="{{ route('dynamic-pricing.store') }}">
                @csrf
                <div class="row">
                    <div class="col-md-3 mb-2"><input class="form-control" name="name" placeholder="Nome da regra" required></div>
                    <div class="col-md-2 mb-2">
                        <select class="form-control" name="vehicle_type">
                            <option value="">Todos tipos</option>
                            <option value="carro">Carro</option>
                            <option value="moto">Moto</option>
                            <option value="caminhonete">Caminhonete</option>
                        </select>
                    </div>
                    <div class="col-md-2 mb-2">
                        <select class="form-control" name="day_of_week">
                            <option value="">Todos dias</option>
                            @foreach ([0 => 'Dom', 1 => 'Seg', 2 => 'Ter', 3 => 'Qua', 4 => 'Qui', 5 => 'Sex', 6 => 'Sab'] as $day => $label)
                                <option value="{{ $day }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-1 mb-2"><input class="form-control" type="time" name="starts_at"></div>
                    <div class="col-md-1 mb-2"><input class="form-control" type="time" name="ends_at"></div>
                    <div class="col-md-1 mb-2"><input class="form-control" type="number" name="occupancy_from" min="0" max="100" placeholder="De" required></div>
                    <div class="col-md-1 mb-2"><input class="form-control" type="number" name="occupancy_to" min="0" max="100" placeholder="Até" required></div>
                    <div class="col-md-1 mb-2"><input class="form-control" type="number" name="multiplier" step="0.01" min="0.1" placeholder="x" required></div>
                    <div class="col-md-2 mb-2"><input class="form-control" type="number" name="flat_addition" step="0.01" min="0" placeholder="Acréscimo (R$)"></div>
                    <div class="col-md-2 mb-2"><input class="form-control" type="number" name="priority" min="1" max="1000" placeholder="Prioridade"></div>
                    <div class="col-md-2 mb-2">
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" checked>
                            <label class="form-check-label" for="is_active">Ativa</label>
                        </div>
                    </div>
                    <div class="col-md-2 mb-2"><button class="btn btn-theme btn-block" type="submit">Adicionar</button></div>
                </div>
            </form>
        </div>
    </div>

    <div class="card theme-card overflow-hidden">
        <div class="table-responsive">
            <table class="table table-theme mb-0">
                <thead>
                    <tr>
                        <th>Regra</th>
                        <th>Tipo</th>
                        <th>Dia</th>
                        <th>Faixa horário</th>
                        <th>Lotação</th>
                        <th>Multiplicador</th>
                        <th>Acréscimo</th>
                        <th>Prioridade</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rules as $rule)
                        <tr>
                            <td>{{ $rule->name }}</td>
                            <td>{{ $rule->vehicle_type ?: 'Todos' }}</td>
                            <td>{{ $rule->day_of_week === null ? 'Todos' : ['Dom','Seg','Ter','Qua','Qui','Sex','Sab'][(int) $rule->day_of_week] }}</td>
                            <td>{{ $rule->starts_at ?: '--:--' }} - {{ $rule->ends_at ?: '--:--' }}</td>
                            <td>{{ $rule->occupancy_from }}% - {{ $rule->occupancy_to }}%</td>
                            <td>x{{ number_format((float) $rule->multiplier, 2, ',', '.') }}</td>
                            <td>R$ {{ number_format(((int) $rule->flat_addition_cents) / 100, 2, ',', '.') }}</td>
                            <td>{{ $rule->priority }}</td>
                            <td><span class="badge {{ $rule->is_active ? 'badge-success' : 'badge-secondary' }}">{{ $rule->is_active ? 'ATIVA' : 'INATIVA' }}</span></td>
                            <td>
                                <form method="POST" action="{{ route('dynamic-pricing.destroy', $rule) }}" onsubmit="return confirm('Remover regra?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" type="submit">Remover</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center py-4">Nenhuma regra cadastrada.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white border-0">{{ $rules->onEachSide(1)->links('pagination::bootstrap-4') }}</div>
    </div>
@endsection
