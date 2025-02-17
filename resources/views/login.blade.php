@extends('adminlte::page')

@section('adminlte_css')
    <link rel="icon" type="image/png" href="{{ asset('img/LogoEstacionamento.png') }}">
    <link rel="stylesheet" href="{{ asset('adminlte/dist/css/adminlte.css') }}" />
    <link rel="stylesheet" href="{{ asset('fontawesome-free/css/all.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/style.css') }}" />
    @parent
    <style>
        body {
            height: 100vh;
        }
        .login-box {
            background-color: rgb(0, 105, 217);
            border-radius: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
            width: 400px;
        }
        .card {
            border-radius: 20px;
        }
        .card-body {
            border-radius: 20px;
        }
        .input-group-text {
            border-radius: 0px 10px 10px 0px !important;
        }
        .input-group input {
            border-radius: 10px 0px 0px 10px !important;
        }
        button {
            border-radius: 10px !important;
        }
    </style>
@endsection

@section('title', 'Login | Sistema de Estacionamento')

@section('body')
    <div class="login-box">
        <div class="login-logo">
            <img src="{{ asset('img/LogoEstacionamento.png') }}" height="50px" alt="Logo Estacionamento">
        </div>

        <div class="card">
            <div class="card-body login-card-body">
                <p class="login-box-msg">Faça login para acessar o painel</p>

                @if (session('error'))
                    <div class="alert alert-danger">
                        {{ session('error') }}
                    </div>
                @endif

                <form action="{{ route('login') }}" method="post" autocomplete="off">
                    @csrf

                    <div class="input-group mb-3">
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                            placeholder="Email" value="{{ old('email') }}" autocomplete="off">
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-envelope"></span>
                            </div>
                        </div>
                        @error('email')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="input-group mb-3">
                        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror"
                            placeholder="Senha" autocomplete="off">
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-lock"></span>
                            </div>
                        </div>
                        @error('password')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary btn-block">Entrar</button>
                        </div>
                    </div>
                </form>

                <div class="row mt-3">
                    <div class="col-6">
                        <a href="" class="text-center">Esqueci minha senha</a>
                    </div>
                    <div class="col-6 text-right">
                        <a href="{{ route('register') }}" class="text-center">Criar uma conta</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('adminlte_js')
    <script src="{{ asset('js/jquery.min.js') }}"></script>
    <script src="{{ asset('adminlte/dist/js/adminlte.min.js') }}"></script>
    <script src="{{ asset('overlayScrollbars/js/jquery.overlayScrollbars.min.js') }}"></script>
    <script src="{{ asset('popper/popper.min.js') }}"></script>
    <script src="{{ asset('bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    @parent
@endsection
