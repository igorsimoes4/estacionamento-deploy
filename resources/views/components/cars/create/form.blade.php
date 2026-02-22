<form action="{{ route('cars.store') }}" method="POST" class="theme-fade-in">
    @csrf
    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="small text-uppercase font-weight-bold">Modelo</label>
            <input type="text" name="modelo" value="{{ old('modelo') }}"
                class="form-control @error('modelo') is-invalid @enderror" placeholder="Ex: Corolla XEI">
            @error('modelo')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-6 mb-3">
            <label class="small text-uppercase font-weight-bold">Placa</label>
            <input type="text" id="placa" name="placa" data-mask="AAA-0000" data-mask-selectonfocus="true"
                value="{{ old('placa') }}" class="form-control @error('placa') is-invalid @enderror"
                placeholder="AAA-1234">
            @error('placa')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-6 mb-3">
            <label class="small text-uppercase font-weight-bold">Data/Hora de Entrada</label>
            <input type="datetime-local" autocomplete="" name="entrada"
                value="{{ old('entrada', now()->format('Y-m-d\TH:i')) }}"
                class="form-control @error('entrada') is-invalid @enderror">
            @error('entrada')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-6 mb-3">
            <label class="small text-uppercase font-weight-bold">Tipo de Veiculo</label>
            <select class="form-control @error('tipo_car') is-invalid @enderror" name="tipo_car">
                <option value="carro" {{ old('tipo_car', 'carro') === 'carro' ? 'selected' : '' }}>Carro</option>
                <option value="moto" {{ old('tipo_car') === 'moto' ? 'selected' : '' }}>Moto</option>
                <option value="caminhonete" {{ old('tipo_car') === 'caminhonete' ? 'selected' : '' }}>Caminhonete
                </option>
            </select>
            @error('tipo_car')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-6 mb-3">
            <label class="small text-uppercase font-weight-bold">Setor</label>
            <select class="form-control @error('parking_sector_id') is-invalid @enderror" name="parking_sector_id">
                <option value="">Alocar automaticamente</option>
                @foreach (($sectors ?? collect()) as $sector)
                    <option value="{{ $sector->id }}" {{ (string) old('parking_sector_id') === (string) $sector->id ? 'selected' : '' }}>
                        {{ $sector->name }} ({{ $sector->code }})
                    </option>
                @endforeach
            </select>
            @error('parking_sector_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mt-2">
        <a href="{{ route('cars.index') }}" class="btn btn-outline-primary">
            <i class="fas fa-arrow-left mr-1"></i> Voltar
        </a>
        <button class="btn btn-theme" style="color: white;">
            <i class="fa fa-plus-circle mr-1" aria-hidden="true"></i> Adicionar Veiculo
        </button>
    </div>
</form>
