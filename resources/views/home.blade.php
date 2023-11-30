@extends('adminlte::page')
{{-- <script src="{{ asset('js/chart.min.js') }}"></script> --}}
<script src="/public/js/jquery.min.js"></script>
<script src="/public/vendor/jquery/jquery.js"></script>
<script src="/public/vendor/jquery/jquery.min.js"></script>
<script src="/public/vendor/adminlte/dist/js/adminlte.min.js"></script>
<script src="/public/vendor/overlayScrollbars/js/jquery.overlayScrollbars.min.js"></script>
<script src="/public/vendor/popper/popper.min.js"></script>
<script src="/public/vendor/popper/popper-utils.min.js"></script>
<script src="/public/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

<link rel="stylesheet" href="/public/vendor/adminlte/dist/css/adminlte.css" />
<link rel="stylesheet" href="/public/vendor/fontawesome-free/css/all.min.css" />
{{-- <script src="{{ asset('js/jquery.min.js') }}"></script> --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.0/chart.min.js"></script>

{{-- @section('plugins.Chartjs', true)

@section('plugins.Chartjs', true) --}}

@section('title', 'Painel | Home')

@section('logo', 'Tetse')

@section('content_header')
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-md">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3>{{$data['car_parking']}}</h3>
                            <p>Carros Estacionados</p>
                        </div>
                        <div class="icon"><i class="fa fa-fw fa-car"></i></div>
                    </div>
                </div>
                <div class="col-md">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3>{{$data['moto_parking']}}</h3>
                            <p>Motos Estacionadas</p>
                        </div>
                        <div class="icon"><i class="fa fa-fw fa-motorcycle"></i></div>
                    </div>
                </div>
                <div class="col-md">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3>{{$data['caminhonete_parking']}}</h3>
                            <p>Caminhonetes Estacionadas</p>
                        </div>
                        <div class="icon"><i class="fa fa-fw fa-car"></i></div>
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
                                let ctx = document.getElementById('carPie').getContext('2d');
                                var carPie = new Chart(ctx, {
                                    type: 'pie',
                                    data: {
                                        labels: {!! json_encode($data['CarLabels']) !!},
                                        datasets: [{
                                            data: {!! json_encode($data['CarValues']) !!},
                                            backgroundColor: getRandomColors({{ count($data['CarLabels']) }}) // Função para gerar cores aleatórias
                                        }],
                                    },
                                    options: {
                                        responsive: false,
                                        legend: {
                                            display: true
                                        }
                                    }
                                });

                                // Função para gerar cores aleatórias
                                function getRandomColors(count) {
                                    var colors = [];
                                    for (var i = 0; i < count; i++) {
                                        colors.push('#' + Math.floor(Math.random()*16777215).toString(16));
                                    }
                                    return colors;
                                }
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
                                var atx = document.getElementById('myBarChart').getContext('2d');
                                var myBarChart = new Chart(atx, {
                                    type: 'bar',
                                    data: {
                                        labels: {!! json_encode($data['CarLabelsYear']) !!},
                                        datasets: [{
                                            label: 'Número de Veículos por Ano',
                                            data: {!! json_encode($data['CarValuesYear']) !!},
                                            backgroundColor: 'rgba(75, 192, 192, 0.2)',
                                            borderColor: 'rgba(75, 192, 192, 1)',
                                            borderWidth: 1
                                        }],
                                    },
                                    options: {
                                        scales: {
                                            y: {
                                                beginAtZero: true
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
