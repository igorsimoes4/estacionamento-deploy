<form action="{{route('cars.update')}}" class="form-horizontal" method="POST">
    @csrf
    <div class="form-group row">

            <label class="col-sm-2 col-form-label" for="">Modelo do Carro</label>
            <div class="col-sm-10">
                <input type="text" name="modelo" value="{{$car->modelo}}" class="form-control @error('modelo') is-invalid @enderror" />
            </div>

    </div>

    <div class="form-group row">

            <label class="col-sm-2 col-form-label" for="">Placa do Carro</label>
            <div class="col-sm-10">
                <input type="text" name="placa" value="{{$car->placa}}" class="form-control @error('placa') is-invalid @enderror" />
            </div>

    </div>

    <div class="form-group row">

        <label class="col-sm-2 col-form-label" for="">Hora de Entrada</label>
        <div class="col-sm-10">
            <input type="datetime-local" name="entrada" value="{{$car->created_at}}" class="form-control @error('entrada') is-invalid @enderror" />
        </div>

    </div>

    @php
        date_default_timezone_set('America/Sao_Paulo');
        $saida = new DateTime();
        $entrada = new DateTime($car->created_at);
        $tempo = date_diff($entrada, $saida);

        $hora = $tempo->h;
        $minuto = $tempo->i;
        $dia = $tempo->d;
    @endphp

    <div class="form-group row">

        <label class="col-sm-2 col-form-label" for="">Escolha o tipo de Veiculo</label>
        <div class="col-sm-10">

            <select class="form-control" name="tipo_car">
                <option value="carro" {{$car->tipo_car=='carro'?'selected="selected"':''}}>Carro</option>
                <option value="moto" {{$car->tipo_car=='moto'?'selected="selected"':''}}>Moto</option>
                <option value="caminhonete" {{$car->tipo_car=='caminhonete'?'selected="selected"':''}}>Caminhonete</option>
            </select>
        </div>

    </div>

    <div class="form-group row">

        <label class="col-sm-2 col-form-label" for="">Tempo Estacionado</label>
        <div class="col-sm-10">
            <input type="text" disabled name="entrada" value="{{$tempo->format('%d dias %h horas %i minutos')}}" class="form-control @error('entrada') is-invalid @enderror" />
        </div>

    </div>

    <div class="form-group row">



        <label class="col-sm-2 col-form-label" for="">Preço</label>
        <div class="col-sm-10">
            <input type="price" value="{{$car->price}}" class="form-control @error('entrada') is-invalid @enderror">
        </div>

    </div>

    <div class="form-group row">

            <label class="col-sm-8 col-form-label" for=""></label>
            <div class="col-sm-4">
                <button class="btn btn-danger form-control">
                    <i style="margin-right: 5px; font-size:15px;" class="fa fa-times" aria-hidden="true"></i> Finalizar
                </button>
            </div>

    </div>
</form>
