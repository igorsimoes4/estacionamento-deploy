@php
    $isEdit = isset($monthlySubscriber);
    $route = $isEdit ? route('monthly-subscribers.update', $monthlySubscriber) : route('monthly-subscribers.store');
    $method = $isEdit ? 'PUT' : 'POST';
@endphp

<form action="{{ $route }}" method="POST">
    @csrf
    @method($method)

    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="name" class="form-label">Nome *</label>
            <input type="text" 
                   class="form-control @error('name') is-invalid @enderror" 
                   id="name" 
                   name="name" 
                   value="{{ old('name', $monthlySubscriber->name ?? '') }}"
                   required>
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-6 mb-3">
            <label for="cpf" class="form-label">CPF *</label>
            <input type="text" 
                   class="form-control @error('cpf') is-invalid @enderror" 
                   id="cpf" 
                   name="cpf" 
                   value="{{ old('cpf', $monthlySubscriber->cpf ?? '') }}"
                   data-mask="000.000.000-00"
                   required>
            @error('cpf')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-6 mb-3">
            <label for="phone" class="form-label">Telefone *</label>
            <input type="text" 
                   class="form-control @error('phone') is-invalid @enderror" 
                   id="phone" 
                   name="phone" 
                   value="{{ old('phone', $monthlySubscriber->phone ?? '') }}"
                   data-mask="(00) 00000-0000"
                   required>
            @error('phone')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-6 mb-3">
            <label for="email" class="form-label">E-mail</label>
            <input type="email" 
                   class="form-control @error('email') is-invalid @enderror" 
                   id="email" 
                   name="email" 
                   value="{{ old('email', $monthlySubscriber->email ?? '') }}">
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-6 mb-3">
            <label for="vehicle_plate" class="form-label">Placa do Veículo *</label>
            <input type="text" 
                   class="form-control @error('vehicle_plate') is-invalid @enderror" 
                   id="vehicle_plate" 
                   name="vehicle_plate" 
                   value="{{ old('vehicle_plate', $monthlySubscriber->vehicle_plate ?? '') }}"
                   data-mask="AAA-0000"
                   required>
            @error('vehicle_plate')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-6 mb-3">
            <label for="vehicle_type" class="form-label">Tipo de Veículo *</label>
            <select class="form-select @error('vehicle_type') is-invalid @enderror" 
                    id="vehicle_type" 
                    name="vehicle_type" 
                    required>
                <option value="">Selecione...</option>
                <option value="carro" {{ old('vehicle_type', $monthlySubscriber->vehicle_type ?? '') == 'carro' ? 'selected' : '' }}>Carro</option>
                <option value="moto" {{ old('vehicle_type', $monthlySubscriber->vehicle_type ?? '') == 'moto' ? 'selected' : '' }}>Moto</option>
                <option value="caminhonete" {{ old('vehicle_type', $monthlySubscriber->vehicle_type ?? '') == 'caminhonete' ? 'selected' : '' }}>Caminhonete</option>
            </select>
            @error('vehicle_type')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-6 mb-3">
            <label for="vehicle_model" class="form-label">Modelo do Veículo</label>
            <input type="text" 
                   class="form-control @error('vehicle_model') is-invalid @enderror" 
                   id="vehicle_model" 
                   name="vehicle_model" 
                   value="{{ old('vehicle_model', $monthlySubscriber->vehicle_model ?? '') }}">
            @error('vehicle_model')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-6 mb-3">
            <label for="vehicle_color" class="form-label">Cor do Veículo</label>
            <input type="text" 
                   class="form-control @error('vehicle_color') is-invalid @enderror" 
                   id="vehicle_color" 
                   name="vehicle_color" 
                   value="{{ old('vehicle_color', $monthlySubscriber->vehicle_color ?? '') }}">
            @error('vehicle_color')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-6 mb-3">
            <label for="start_date" class="form-label">Data de Início *</label>
            <input type="date" 
                   class="form-control @error('start_date') is-invalid @enderror" 
                   id="start_date" 
                   name="start_date" 
                   value="{{ old('start_date', $monthlySubscriber->start_date ?? '') }}"
                   required>
            @error('start_date')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-6 mb-3">
            <label for="end_date" class="form-label">Data de Término *</label>
            <input type="date" 
                   class="form-control @error('end_date') is-invalid @enderror" 
                   id="end_date" 
                   name="end_date" 
                   value="{{ old('end_date', $monthlySubscriber->end_date ?? '') }}"
                   required>
            @error('end_date')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-6 mb-3">
            <label for="monthly_fee" class="form-label">Valor Mensal *</label>
            <div class="input-group">
                <span class="input-group-text">R$</span>
                <input type="text" 
                       class="form-control @error('monthly_fee') is-invalid @enderror" 
                       id="monthly_fee" 
                       name="monthly_fee" 
                       value="{{ old('monthly_fee', $monthlySubscriber->monthly_fee ?? '') }}"
                       data-mask="#.##0,00" 
                       data-mask-reverse="true"
                       required>
            </div>
            @error('monthly_fee')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-12 mb-3">
            <label for="observations" class="form-label">Observações</label>
            <textarea class="form-control @error('observations') is-invalid @enderror" 
                      id="observations" 
                      name="observations" 
                      rows="3">{{ old('observations', $monthlySubscriber->observations ?? '') }}</textarea>
            @error('observations')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="d-flex justify-content-between mt-4">
        <a href="{{ route('monthly-subscribers.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Voltar
        </a>
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save"></i> Salvar
        </button>
    </div>
</form>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
<script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
<script>
    const price = document.getElementById('vehicle_type');

    price.addEventListener('change', function() {
        console.log('Tipo selecionado:', price.value);
        const type = price.value;
        if (type) {
            const url = "{{ route('get-vehicle-price', ['type' => ':type']) }}".replace(':type', type);
            console.log('URL da requisição:', url);
            
            $.ajax({
                url: url,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    console.log('Resposta do servidor:', response);
                    if (response.price) {
                        // Converter o valor para string e formatar
                        const price = parseFloat(response.price).toFixed(2);
                        console.log('Preço formatado:', price);
                        
                        // Definir o novo valor
                        $('#monthly_fee').val(price);
                        
                        // Reaplicar a máscara
                        $('#monthly_fee').mask('#.##0,00', {reverse: true});
                        
                        // Forçar atualização da máscara
                        $('#monthly_fee').trigger('input');
                    } else {
                        console.error('Preço não encontrado na resposta:', response);
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.error('Erro ao buscar preço:', textStatus, errorThrown);
                    console.error('Detalhes do erro:', jqXHR.responseText);
                }
            });
        }
    });
</script>