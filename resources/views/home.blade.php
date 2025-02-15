@extends('adminlte::page')

@section('adminlte_css')
    <!-- Adiciona o favicon -->
    <link rel="icon" type="image/png" href="{{ asset('img/LogoEstacionamento.png') }}">

    <!-- Estilos personalizados -->
    <link rel="stylesheet" href="{{ asset('fontawesome-free/css/all.min.css') }}" />
    <style>
        /* Garantir que o layout ocupe 100% da altura da tela */
        html, body {
            height: 100%;
            margin: 0;
        }

        /* O conteúdo principal deve ocupar o espaço restante da tela */
        .content-wrapper {
            min-height: calc(100vh - 56px); /* 56px é a altura do cabeçalho padrão do AdminLTE */
        }

        .content {
            padding: 20px;
        }

        /* Ajustar o tamanho dos gráficos */
        .chart-container {
            width: 100%;
            height: 400px;
        }

        .card-body canvas {
            width: 100% !important;
            height: 100% !important;
        }
        </style>

        @parent
@endsection

@section('title', 'Painel | Home')

@section('content_header')
<h1>Dashboard de Estacionamento</h1>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-4 col-md-6 col-sm-12">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $data['car_parking'] }}</h3>
                    <p>Carros Estacionados</p>
                </div>
                <div class="icon">
                    <i class="fa fa-fw fa-car"></i>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-6 col-sm-12">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $data['moto_parking'] }}</h3>
                    <p>Motos Estacionadas</p>
                </div>
                <div class="icon">
                    <i class="fa fa-fw fa-motorcycle"></i>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-6 col-sm-12">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ $data['caminhonete_parking'] }}</h3>
                    <p>Caminhonetes Estacionadas</p>
                </div>
                <div class="icon">
                    <i class="fa fa-fw fa-truck-pickup"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Gráfico de Veículos mais Estacionados no Mês -->
        <div class="col-lg-6 col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Veículos mais Estacionados no Mês</h3>
                </div>
                <div class="card-body chart-container">
                    <canvas id="carDoughnut"></canvas>
                </div>
            </div>
        </div>

        <!-- Gráfico de Veículos Estacionados no Ano (Gráfico de Linha) -->
        <div class="col-lg-6 col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Veículos Estacionados no Ano</h3>
                </div>
                <div class="card-body chart-container">
                    <canvas id="myLineChart"></canvas>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <!-- Inclusão de scripts -->
    <script src="{{ asset('overlayScrollbars/js/jquery.overlayScrollbars.min.js') }}"></script>
    <script src="{{ asset('popper/popper.min.js') }}"></script>
    <script src="{{ asset('bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.0/chart.min.js"></script>
    <script src="{{ asset('js/jquery.min.js') }}"></script>

    <script>
        // Função para gerar cores aleatórias mais sóbrias
        function getRandomColors(numColors) {
            let colors = [];
            for (let i = 0; i < numColors; i++) {
                let r = Math.floor(Math.random() * 150) + 100;
                let g = Math.floor(Math.random() * 150) + 100;
                let b = Math.floor(Math.random() * 150) + 100;
                colors.push(`rgba(${r}, ${g}, ${b}, 0.7)`);
            }
            return colors;
        }

        // Gráfico de Rosca (Doughnut) para Veículos mais Estacionados no Mês
        var ctx = document.getElementById('carDoughnut').getContext('2d');
        var carDoughnut = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: {!! json_encode($data['CarLabels']) !!},
                datasets: [{
                    data: {!! json_encode($data['CarValues']) !!},
                    backgroundColor: getRandomColors({{ count($data['CarLabels']) }}),
                    borderColor: getRandomColors({{ count($data['CarLabels']) }}).map(color => color.replace('0.7', '1')),
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                        labels: {
                            font: { size: 14, weight: 'bold' },
                            color: 'rgba(0, 0, 0, 0.7)'
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(tooltipItem) {
                                return tooltipItem.label + ': ' + tooltipItem.raw;
                            }
                        },
                        backgroundColor: 'rgba(0, 0, 0, 0.7)',
                        titleFont: { size: 16, weight: 'bold' },
                        bodyFont: { size: 14 }
                    }
                }
            }
        });

        // Gráfico de Linha para Veículos Estacionados no Ano
        var ctx2 = document.getElementById('myLineChart').getContext('2d');
        var datasets = {!! json_encode($data['CarLabelsYear']) !!}.map((label, index) => ({
            label: label,
            data: Object.values({!! json_encode($data['CarValuesYear']) !!}).map(monthValues => monthValues[index]),
            backgroundColor: `rgba(${255 - (index * 50)}, ${99 + (index * 50)}, 132, 0.2)`,
            borderColor: `rgba(${255 - (index * 50)}, ${99 + (index * 50)}, 132, 1)`,
            borderWidth: 2,
            fill: true,  // Preencher a área abaixo da linha
            tension: 0.4 // Deixar a linha suavizada
        }));

        var myLineChart = new Chart(ctx2, {
            type: 'line',
            data: {
                labels: ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'],
                datasets: datasets
            },
            options: {
                responsive: true,
                scales: {
                    x: {
                        beginAtZero: true,
                        grid: { display: true },
                        ticks: {
                            font: { size: 12, weight: 'bold' }
                        }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { display: true },
                        ticks: {
                            font: { size: 12, weight: 'bold' }
                        }
                    }
                },
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            font: { size: 14, weight: 'bold' },
                            color: 'rgba(0, 0, 0, 0.7)'
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.7)',
                        titleFont: { size: 16, weight: 'bold' },
                        bodyFont: { size: 14 },
                        callbacks: {
                            label: function(tooltipItem) {
                                return tooltipItem.dataset.label + ': ' + tooltipItem.raw;
                            }
                        }
                    }
                },
                animation: {
                    duration: 1000, // Suavizar a animação
                    easing: 'easeInOutQuart'
                }
            }
        });
    </script>
@endsection
