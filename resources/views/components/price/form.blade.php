<form action="{{route($route)}}" class="form-horizontal" method="POST">
    @csrf
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label class="col-form-label" for="valorHora">Valor Hora</label>
                <input type="price" name="valorHora" value="{{$price->valorHora}}" class="form-control @error('valorHora') is-invalid @enderror" />
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label class="col-form-label" for="valorMinimo">Valor Minimo</label>
                <input type="price" name="valorMinimo" value="{{$price->valorMinimo}}" class="form-control @error('valorMinimo') is-invalid @enderror" />
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label class="col-form-label" for="valorDiaria">Valor Diária</label>
                <input type="price" name="valorDiaria" value="{{$price->valorDiaria}}" class="form-control @error('valorDiaria') is-invalid @enderror" />
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label class="col-form-label" for="taxaAdicional">Valor Hora Adicional</label>
                <input type="price"  name="taxaAdicional" value="{{$price->taxaAdicional}}" class="form-control @error('taxaAdicional') is-invalid @enderror" />
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label class="col-form-label" for="taxaMensal">Valor Mensal</label>
                <input type="price"  name="taxaMensal" value="{{$price->taxaMensal}}" class="form-control @error('taxaMensal') is-invalid @enderror" />
            </div>
        </div>
    </div>
    <div class="form-group">
        <div class="row">
            <div class="col-md-9"></div>
            <div class="col-md-3">
                <button class="btn btn-success btn-block">
                    <i style="margin-right: 5px; font-size: 15px;" class="fa fa-save" aria-hidden="true"></i> Salvar
                </button>
            </div>
        </div>
    </div>

</form>
