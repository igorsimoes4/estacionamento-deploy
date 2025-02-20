@extends('adminlte::page')

@section('content_header')
    <h1>Relatórios Disponíveis</h1>
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            <ul class="list-group" style="flex-direction: row;">
                @foreach($reports as $report)
                    <li class="list-group-item">
                        <h5>{{ $report['name'] }}</h5>
                        <p>{{ $report['description'] }}</p>
                        <a href="{{ $report['route'] }}" class="btn btn-primary" target="_blank">Gerar Relatório</a>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
@endsection
