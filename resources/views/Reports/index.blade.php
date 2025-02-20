@extends('adminlte::page')

@section('content_header')
    <h1 class="text-center mb-5">📊 Relatórios Disponíveis</h1>
@endsection

@section('content')
    <div class="container">
        <div class="row d-flex justify-content-center">
            @foreach($reports as $report)
                <div class="col-md-6 col-lg-4 mb-4 d-flex">
                    <div class="card shadow-sm border-0 flex-fill" style="min-height: 230px;">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title text-center mb-3">
                                <i class="fas fa-file-alt"></i> {{ $report['name'] }}
                            </h5>
                            <p class="card-text text-muted flex-grow-1">{{ $report['description'] }}</p>
                            <div class="text-end row " style="gap: 10px; justify-content: center;">
                                <a href="{{ $report['route'] }}" class="btn btn-primary btn-sm col-12 col-md-5" target="_blank">
                                    <i class="fas fa-file-pdf"></i> Gerar Relatório em PDF
                                </a>
                                <a href="{{ $report['route'] }}" class="btn btn-success btn-sm col-12 col-md-5" target="_blank">
                                    <i class="fas fa-file-excel"></i> Gerar Relatório em Excel
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection
