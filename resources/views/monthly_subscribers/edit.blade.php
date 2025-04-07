@extends('adminlte::page')

@section('adminlte_css')
    <link rel="icon" type="image/png" href="{{ asset('img/LogoEstacionamento.png') }}">
    <link rel="stylesheet" href="{{ asset('fontawesome-free/css/all.min.css') }}" />
    @parent
    <style>
        .wrapper {
            background-color: #F4F6F9;
        }
    </style>
@endsection

@section('content')
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Editar Mensalista</h5>
                </div>

                <div class="card-body">
                    @include('monthly_subscribers.form')
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 