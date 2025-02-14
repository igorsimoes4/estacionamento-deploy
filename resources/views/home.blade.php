@extends('adminlte::page')
@section('adminlte_css')
    <!-- Adiciona o favicon -->
    <link rel="icon" type="image/png" href="{{ asset('img/LogoEstacionamento.png') }}">

    <link rel="stylesheet" href="{{ asset('adminlte/dist/css/adminlte.css') }}" />
    <link rel="stylesheet" href="{{ asset('fontawesome-free/css/all.min.css') }}" />

    <!-- Inclui os estilos padrão do AdminLTE -->
    @parent
@endsection
<script src="{{ asset('js/jquery.min.js') }}"></script>
    <script src="{{ asset('adminlte/dist/js/adminlte.min.js') }}"></script>
    <script src="{{ asset('overlayScrollbars/js/jquery.overlayScrollbars.min.js') }}"></script>
    <script src="{{ asset('popper/popper.min.js') }}"></script>
    <script src="{{ asset('bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.0/chart.min.js"></script>

@section('plugins.Chartjs', true)

@section('plugins.Chartjs', true)

@section('title', 'Painel | Home')

@section('logo', 'Tetse')

@section('content_header')
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-md">
                    <div class="small-box" style="background-color: rgba(54, 162, 235, 0.7);">
                        <div class="inner">
                            <h3>{{ $data['car_parking'] }}</h3>
                            <p>Carros Estacionados</p>
                        </div>
                        <div class="icon"><i class="fa fa-fw fa-car"></i></div>
                    </div>
                </div>
                <div class="col-md">
                    <div class="small-box" style="background-color: rgba(255, 206, 86, 0.7);">
                        <div class="inner">
                            <h3>{{ $data['moto_parking'] }}</h3>
                            <p>Motos Estacionadas</p>
                        </div>
                        <div class="icon"><i class="fa fa-fw fa-motorcycle"></i></div>
                    </div>
                </div>
                <div class="col-md">
                    <div class="small-box" style="background-color: rgba(255, 99, 132, 0.7);">
                        <div class="inner">
                            <h3>{{ $data['caminhonete_parking'] }}</h3>
                            <p>Caminhonetes Estacionadas</p>
                        </div>
                        <div class="icon"><i class="fa fa-fw fa-truck-pickup"></i></div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Veículos mais Estacionados no Mês</h3>
                        </div>
                        <div class="card-body">
                            <canvas id="carPie" width="400" height="300"></canvas>
                            <script>
                                // Função para gerar cores aleatórias
                                function getRandomColors(numColors) {
                                    var colors = [];
                                    for (var i = 0; i < numColors; i++) {
                                        var r = Math.floor(Math.random() * 255);
                                        var g = Math.floor(Math.random() * 255);
                                        var b = Math.floor(Math.random() * 255);
                                        colors.push('rgba(' + r + ',' + g + ',' + b + ', 0.7)');
                                    }
                                    return colors;
                                }

                                // Cores predefinidas
                                var predefinedColors = [
                                    'rgba(255, 99, 132, 0.7)', // Cor 1
                                    'rgba(54, 162, 235, 0.7)', // Cor 2
                                    'rgba(255, 206, 86, 0.7)', // Cor 3
                                ];

                                // Garantir que haja cores suficientes para cada tipo de carro
                                var numLabels = {{ count($data['CarLabels']) }};
                                var backgroundColors = predefinedColors.slice(0, numLabels);
                                if (numLabels > predefinedColors.length) {
                                    backgroundColors = backgroundColors.concat(getRandomColors(numLabels - predefinedColors.length));
                                }

                                var ctx = document.getElementById('carPie').getContext('2d');
                                var carPie = new Chart(ctx, {
                                    type: 'pie',
                                    data: {
                                        labels: {!! json_encode($data['CarLabels']) !!},
                                        datasets: [{
                                            data: {!! json_encode($data['CarValues']) !!},
                                            backgroundColor: backgroundColors,
                                            borderColor: backgroundColors.map(color => color.replace('0.7', '1')),
                                            borderWidth: 1
                                        }],
                                    },
                                    options: {
                                        responsive: false,
                                        plugins: {
                                            legend: {
                                                display: true,
                                                position: 'top'
                                            },
                                            tooltip: {
                                                callbacks: {
                                                    label: function(tooltipItem) {
                                                        var value = tooltipItem.raw;
                                                        var label = tooltipItem.label;
                                                        return label + ': ' + value;
                                                    }
                                                }
                                            }
                                        }
                                    }
                                });
                            </script>

                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Veículos Estacionados no Ano</h3>
                        </div>
                        <div class="card-body">
                            <canvas id="myBarChart" width="400" height="220"></canvas>
                            <script>
                                var ctx = document.getElementById('myBarChart').getContext('2d');

                                // Dados dos tipos de carros e valores agrupados por mês
                                var CarLabelsYear = {!! json_encode($data['CarLabelsYear']) !!};
                                var CarValuesYear = {!! json_encode($data['CarValuesYear']) !!};

                                // Array de cores para os tipos de carros
                                var backgroundColors = [
                                    'rgba(255, 99, 132, 0.5)', // Cor 1
                                    'rgba(54, 162, 235, 0.5)', // Cor 2
                                    'rgba(255, 206, 86, 0.5)', // Cor 3
                                ];

                                var borderColors = [
                                    'rgba(255, 99, 132, 1)', // Cor 1
                                    'rgba(54, 162, 235, 1)', // Cor 2
                                    'rgba(255, 206, 86, 1)', // Cor 3
                                ];

                                // Configurar os datasets para o gráfico de barras empilhadas
                                var datasets = CarLabelsYear.map((label, index) => {
                                    return {
                                        label: label,
                                        data: Object.values(CarValuesYear).map(monthValues => monthValues[index]),
                                        backgroundColor: backgroundColors[index % backgroundColors.length],
                                        borderColor: borderColors[index % borderColors.length],
                                        borderWidth: 1
                                    };
                                });

                                // Criação do gráfico de barras empilhadas
                                var myBarChart = new Chart(ctx, {
                                    type: 'line',
                                    data: {
                                        labels: ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro',
                                            'Outubro', 'Novembro', 'Dezembro'
                                        ],
                                        datasets: datasets
                                    },
                                    options: {
                                        scales: {
                                            x: {
                                                stacked: true // Empilhar as barras no eixo X
                                            },
                                            y: {
                                                beginAtZero: true,
                                                stacked: true // Empilhar as barras no eixo Y
                                            }
                                        }
                                    }
                                });
                            </script>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


@endsection
