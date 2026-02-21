<form action="{{ route($route) }}" method="POST" class="theme-fade-in">
    @csrf

    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="small text-uppercase font-weight-bold" for="valorHora">Valor Hora</label>
            <input type="number" step="0.01" min="0" id="valorHora" name="valorHora"
                value="{{ old('valorHora', $price->valorHora) }}"
                class="form-control @error('valorHora') is-invalid @enderror" placeholder="0.00">
            @error('valorHora')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-6 mb-3">
            <label class="small text-uppercase font-weight-bold" for="valorMinimo">Valor Minimo</label>
            <input type="number" step="0.01" min="0" id="valorMinimo" name="valorMinimo"
                value="{{ old('valorMinimo', $price->valorMinimo) }}"
                class="form-control @error('valorMinimo') is-invalid @enderror" placeholder="0.00">
            @error('valorMinimo')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-6 mb-3">
            <label class="small text-uppercase font-weight-bold" for="valorDiaria">Valor Diaria</label>
            <input type="number" step="0.01" min="0" id="valorDiaria" name="valorDiaria"
                value="{{ old('valorDiaria', $price->valorDiaria) }}"
                class="form-control @error('valorDiaria') is-invalid @enderror" placeholder="0.00">
            @error('valorDiaria')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-6 mb-3">
            <label class="small text-uppercase font-weight-bold" for="taxaAdicional">Hora Adicional</label>
            <input type="number" step="0.01" min="0" id="taxaAdicional" name="taxaAdicional"
                value="{{ old('taxaAdicional', $price->taxaAdicional) }}"
                class="form-control @error('taxaAdicional') is-invalid @enderror" placeholder="0.00">
            @error('taxaAdicional')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-6 mb-3">
            <label class="small text-uppercase font-weight-bold" for="taxaMensal">Valor Mensal</label>
            <input type="number" step="0.01" min="0" id="taxaMensal" name="taxaMensal"
                value="{{ old('taxaMensal', $price->taxaMensal) }}"
                class="form-control @error('taxaMensal') is-invalid @enderror" placeholder="0.00">
            @error('taxaMensal')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mt-2">
        <a href="{{ route('settings') }}" class="btn btn-outline-primary">
            <i class="fas fa-arrow-left mr-1"></i> Voltar
        </a>
        <button class="btn btn-theme" style="color: white;">
            <i class="fa fa-save mr-1" aria-hidden="true"></i> Salvar Alteracoes
        </button>
    </div>
</form>
