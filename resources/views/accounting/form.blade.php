@php
    $isEdit = isset($entry);
    $route = $isEdit ? route('accounting.update', $entry) : route('accounting.store');
    $method = $isEdit ? 'PUT' : 'POST';
@endphp

<form action="{{ $route }}" method="POST" class="theme-fade-in">
    @csrf
    @method($method)

    <div class="row">
        <div class="col-md-3 mb-3">
            <label for="type" class="form-label">Tipo *</label>
            <select id="type" name="type" class="form-control @error('type') is-invalid @enderror" required>
                <option value="receita" {{ old('type', $entry->type ?? 'receita') === 'receita' ? 'selected' : '' }}>Receita</option>
                <option value="despesa" {{ old('type', $entry->type ?? '') === 'despesa' ? 'selected' : '' }}>Despesa</option>
            </select>
            @error('type')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-5 mb-3">
            <label for="category" class="form-label">Categoria *</label>
            <input id="category" name="category" type="text" class="form-control @error('category') is-invalid @enderror"
                value="{{ old('category', $entry->category ?? '') }}" placeholder="Ex: Energia, Manutencao, Receita extra" required>
            @error('category')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-4 mb-3">
            <label for="payment_method" class="form-label">Forma de pagamento</label>
            <select id="payment_method" name="payment_method" class="form-control @error('payment_method') is-invalid @enderror">
                <option value="">Selecione...</option>
                @foreach (['dinheiro' => 'Dinheiro', 'pix' => 'Pix', 'cartao_credito' => 'Cartao credito', 'cartao_debito' => 'Cartao debito', 'transferencia' => 'Transferencia', 'boleto' => 'Boleto', 'outro' => 'Outro'] as $value => $label)
                    <option value="{{ $value }}" {{ old('payment_method', $entry->payment_method ?? '') === $value ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            @error('payment_method')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-6 mb-3">
            <label for="description" class="form-label">Descricao</label>
            <input id="description" name="description" type="text" class="form-control @error('description') is-invalid @enderror"
                value="{{ old('description', $entry->description ?? '') }}" placeholder="Descricao curta do lancamento">
            @error('description')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-3 mb-3">
            <label for="amount" class="form-label">Valor (R$) *</label>
            <input id="amount" name="amount" type="text" class="form-control @error('amount') is-invalid @enderror"
                value="{{ old('amount', isset($entry) ? number_format((float) $entry->amount, 2, ',', '.') : '') }}"
                placeholder="0,00" required>
            @error('amount')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-3 mb-3">
            <label for="occurred_at" class="form-label">Data do lancamento *</label>
            <input id="occurred_at" name="occurred_at" type="date" class="form-control @error('occurred_at') is-invalid @enderror"
                value="{{ old('occurred_at', isset($entry) && $entry->occurred_at ? $entry->occurred_at->format('Y-m-d') : now()->format('Y-m-d')) }}"
                required>
            @error('occurred_at')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-12 mb-3">
            <label for="notes" class="form-label">Observacoes</label>
            <textarea id="notes" name="notes" class="form-control @error('notes') is-invalid @enderror" rows="3"
                placeholder="Detalhes adicionais (opcional)">{{ old('notes', $entry->notes ?? '') }}</textarea>
            @error('notes')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="d-flex justify-content-between mt-2">
        <a href="{{ route('accounting.index') }}" class="btn btn-outline-primary">
            <i class="fas fa-arrow-left mr-1"></i> Voltar
        </a>
        <button type="submit" class="btn btn-theme">
            <i class="fas fa-save mr-1"></i> Salvar lancamento
        </button>
    </div>
</form>
