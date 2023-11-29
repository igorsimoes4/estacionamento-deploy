<form action="{{route('cars.store')}}" class="form-horizontal" method="POST">
    @csrf
    <div class="form-group row">

            <label class="col-sm-2 col-form-label" for="">Modelo do Carro</label>
            <div class="col-sm-10">
                <input type="text" name="modelo" value="{{old('modelo')}}" class="form-control @error('modelo') is-invalid @enderror" />
            </div>

    </div>

    <div class="form-group row">

            <label class="col-sm-2 col-form-label" for="">Placa do Carro</label>
            <div class="col-sm-10">
                <input type="text" id="placa" name="placa" data-mask="YYY-YYYY" data-mask-selectonfocus="true" value="{{old('placa')}}" class="form-control @error('placa') is-invalid @enderror" />
            </div>

    </div>

    <div class="form-group row">

        <label class="col-sm-2 col-form-label" for="">Hora Atual</label>
        <div class="col-sm-10">
            @php
                $data_atual = new DateTime();
                $data_atual = $data_atual->format('d/m/Y H:i');
            @endphp
            <input type="datetime-local" autocomplete="" name="entrada" value="{{old('entrada')}}" class="form-control @error('entrada') is-invalid @enderror" />
        </div>

    </div>

    <div class="form-group row">

        <label class="col-sm-2 col-form-label" for="">Escolha o tipo de Veiculo</label>
        <div class="col-sm-10">
            <select class="form-control" name="tipo_car">
                <option value="carro" selected>Carro</option>
                <option value="moto">Moto</option>
                <option value="caminhonete">Caminhonete</option>
            </select>
        </div>

    </div>

    <div class="form-group row">

            <label class="col-sm-8 col-form-label" for=""></label>
            <div class="col-sm-4">
                <button class="btn btn-success form-control">
                    <i style="margin-right: 5px; font-size:15px;" class="fa fa-plus-circle" aria-hidden="true"></i> Adicionar
                </button>
            </div>

    </div>
</form>
