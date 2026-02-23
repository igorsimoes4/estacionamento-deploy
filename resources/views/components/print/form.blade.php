<form action="{{ route('pembayaran.printTicket') }}" method="POST">
    <input type="hidden" name="tipo_car" value="{{$car->tipo_car}}" class="form-control">
    <input type="hidden" name="preco" value="{{$car->price ?? $car->preco}}" class="form-control">
    <input type="hidden" name="placa" value="{{$car->placa}}" class="form-control">
    <input type="hidden" name="entrada" value="{{ optional($entrada)->format('d/m/Y H:i:s') }}" class="form-control">
    <input type="hidden" name="data" value="{{ optional($entrada)->format('d/m/Y') }}" class="form-control">
    <input type="hidden" name="hora" value="{{ optional($entrada)->format('H:i:s') }}" class="form-control">
    <input type="hidden" name="_token" class="form-control" value="{!! csrf_token() !!}">
    <button type="submit" name="submit" class="btn btn-sm btn-info"><i style="margin-right: 5px; font-size:13px;" class="fas fa-solid fa-print"></i> Imprimir</button>
</form>
