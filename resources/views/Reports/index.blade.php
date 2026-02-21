@extends('adminlte::page')

@section('title', 'Painel | Relatorios')

@section('content_header')
    <div class="report-hero theme-fade-in">
        <div class="d-flex flex-wrap justify-content-between align-items-center" style="gap: 12px;">
            <div>
                <h1 class="m-0">Central de Relatorios</h1>
                <p>Escolha o formato ideal para analise operacional e financeira.</p>
            </div>
            <span class="badge badge-light p-2">{{ count($reports) }} relatorios disponiveis</span>
        </div>
    </div>
@endsection

@section('content')
    <div class="row mt-3">
        @foreach ($reports as $report)
            @php
                $name = $report['name'];
                $icon = 'fa-file-lines';

                if (\Illuminate\Support\Str::contains($name, ['Financeiro'])) {
                    $icon = 'fa-chart-line';
                } elseif (\Illuminate\Support\Str::contains($name, ['Pagamento'])) {
                    $icon = 'fa-credit-card';
                } elseif (\Illuminate\Support\Str::contains($name, ['Top 20', 'Faturamento'])) {
                    $icon = 'fa-trophy';
                } elseif (\Illuminate\Support\Str::contains($name, ['Ocupacao'])) {
                    $icon = 'fa-chart-pie';
                } elseif (\Illuminate\Support\Str::contains($name, ['Movimentacao'])) {
                    $icon = 'fa-chart-bar';
                } elseif (\Illuminate\Support\Str::contains($name, ['Mensalistas'])) {
                    $icon = 'fa-users';
                } elseif (\Illuminate\Support\Str::contains($name, ['Entrada', 'Saida'])) {
                    $icon = 'fa-right-left';
                } elseif (\Illuminate\Support\Str::contains($name, ['Carros', 'Motos', 'Caminhonetes', 'Veiculos'])) {
                    $icon = 'fa-car';
                }
            @endphp

            <div class="col-xl-4 col-md-6 mb-4">
                <div class="card report-card h-100">
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <span class="report-icon"><i class="fas {{ $icon }}"></i></span>
                            <span class="report-meta">
                                <i class="fas fa-file-export"></i>
                                {{ empty($report['excel_route']) ? 'PDF' : 'PDF + Excel' }}
                            </span>
                        </div>

                        <h5 class="mb-2">{{ $report['name'] }}</h5>
                        <p class="text-muted mb-4" style="min-height: 52px;">{{ $report['description'] }}</p>

                        <div class="mt-auto d-flex flex-wrap" style="gap: 8px;">
                            <a href="{{ $report['pdf_route'] }}" target="_blank" class="btn btn-sm btn-report-pdf">
                                <i class="fas fa-file-pdf mr-1"></i> Exportar PDF
                            </a>

                            @if (!empty($report['excel_route']))
                                <a href="{{ $report['excel_route'] }}" target="_blank" class="btn btn-sm btn-report-excel">
                                    <i class="fas fa-file-excel mr-1"></i> Exportar Excel
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endsection
