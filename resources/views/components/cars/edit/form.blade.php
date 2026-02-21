<form action="{{ route('cars.update', $car->id) }}" method="POST" class="theme-fade-in">
    @csrf
    @method('PUT')

    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="small text-uppercase font-weight-bold">Modelo</label>
            <input type="text" name="modelo" value="{{ old('modelo', $car->modelo) }}"
                class="form-control @error('modelo') is-invalid @enderror" placeholder="Ex: Corolla XEI">
            @error('modelo')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-6 mb-3">
            <label class="small text-uppercase font-weight-bold">Placa</label>
            <input type="text" name="placa" value="{{ old('placa', $car->placa) }}"
                class="form-control @error('placa') is-invalid @enderror" placeholder="AAA-1234">
            @error('placa')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-6 mb-3">
            <label class="small text-uppercase font-weight-bold">Data/Hora de Entrada</label>
            <input type="datetime-local" name="entrada"
                value="{{ old('entrada', optional($car->created_at)->format('Y-m-d\\TH:i')) }}"
                class="form-control @error('entrada') is-invalid @enderror">
            @error('entrada')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-6 mb-3">
            <label class="small text-uppercase font-weight-bold">Tipo de Veiculo</label>
            <select class="form-control @error('tipo_car') is-invalid @enderror" name="tipo_car">
                <option value="carro" {{ old('tipo_car', $car->tipo_car) === 'carro' ? 'selected' : '' }}>Carro
                </option>
                <option value="moto" {{ old('tipo_car', $car->tipo_car) === 'moto' ? 'selected' : '' }}>Moto</option>
                <option value="caminhonete" {{ old('tipo_car', $car->tipo_car) === 'caminhonete' ? 'selected' : '' }}>
                    Caminhonete</option>
            </select>
            @error('tipo_car')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-12 mb-3">
            <label class="small text-uppercase font-weight-bold">Preco Atual</label>
            <input type="text" disabled value="R$ {{ number_format((float) ($car->price ?? 0), 2, ',', '.') }}"
                class="form-control">
        </div>

        @if ($car->status === 'finalizado')
            <div class="col-md-6 mb-3">
                <label class="small text-uppercase font-weight-bold">Metodo de Pagamento</label>
                <input type="text" disabled value="{{ \App\Models\Cars::paymentMethodLabel($car->payment_method) }}"
                    class="form-control">
            </div>

            <div class="col-md-6 mb-3">
                <label class="small text-uppercase font-weight-bold">Gateway de Pagamento</label>
                <input type="text" disabled value="{{ \App\Models\Cars::paymentProviderLabel($car->payment_provider) }}"
                    class="form-control">
            </div>

            <div class="col-md-6 mb-3">
                <label class="small text-uppercase font-weight-bold">Pagamento Registrado em</label>
                <input type="text" disabled
                    value="{{ optional($car->paid_at ?? $car->saida)->format('d/m/Y H:i:s') }}"
                    class="form-control">
            </div>

            <div class="col-md-6 mb-3">
                <label class="small text-uppercase font-weight-bold">Referencia de Pagamento</label>
                <input type="text" disabled value="{{ $car->payment_reference ?: '-' }}"
                    class="form-control">
            </div>

            @if (!empty($car->payment_url))
                <div class="col-md-12 mb-3">
                    <label class="small text-uppercase font-weight-bold">URL de Pagamento/Boleto</label>
                    <input type="text" disabled value="{{ $car->payment_url }}" class="form-control">
                </div>
            @endif
        @endif
    </div>

    <div class="d-flex justify-content-between align-items-center mt-2">
        <a href="{{ route('cars.index') }}" class="btn btn-outline-primary">
            <i class="fas fa-arrow-left mr-1"></i> Voltar
        </a>
        <button class="btn btn-theme" style="color: white;">
            <i class="fa fa-save mr-1" aria-hidden="true"></i> Salvar Alterações
        </button>
    </div>
</form>
