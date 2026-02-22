@extends('adminlte::page')

@section('adminlte_css')
    <link rel="icon" type="image/png" href="{{ asset('img/LogoEstacionamento.png') }}">
    <link rel="stylesheet" href="{{ asset('adminlte/dist/css/adminlte.css') }}">
    <link rel="stylesheet" href="{{ asset('fontawesome-free/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/panel-theme.css') }}">
    @parent

    <style>
        :root {
            --login-primary: #0f6c74;
            --login-primary-dark: #084a57;
            --login-accent: #ef9b20;
            --login-surface: #ffffff;
            --login-muted: #6b7280;
        }

        body {
            min-height: 100vh;
            margin: 0;
            background:
                radial-gradient(circle at 14% 12%, rgba(239, 155, 32, .25), transparent 34%),
                radial-gradient(circle at 88% 86%, rgba(15, 108, 116, .22), transparent 38%),
                linear-gradient(140deg, #edf2f7 0%, #e2edf7 48%, #f4f8fc 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px 14px;
            color: #1f2937;
            font-family: 'Manrope', sans-serif;
        }

        .auth-shell {
            width: 100%;
            max-width: 980px;
            border-radius: 22px;
            overflow: hidden;
            box-shadow: 0 24px 46px rgba(9, 44, 72, .16);
            display: grid;
            grid-template-columns: 1.05fr .95fr;
            background: rgba(255, 255, 255, .74);
            backdrop-filter: blur(6px);
        }

        .auth-hero {
            background: linear-gradient(140deg, var(--login-primary-dark) 0%, var(--login-primary) 54%, #13a4b0 100%);
            color: #fff;
            padding: 40px 36px;
            position: relative;
            overflow: hidden;
        }

        .auth-hero::before {
            content: "";
            position: absolute;
            width: 240px;
            height: 240px;
            border-radius: 999px;
            top: -100px;
            right: -70px;
            background: rgba(255, 255, 255, .13);
        }

        .auth-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 30px;
            position: relative;
            z-index: 2;
        }

        .auth-brand img {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            object-fit: cover;
            box-shadow: 0 6px 12px rgba(0, 0, 0, .18);
        }

        .auth-brand h1 {
            margin: 0;
            font-size: 1.08rem;
            font-family: 'Sora', sans-serif;
            letter-spacing: .2px;
        }

        .auth-brand p {
            margin: 2px 0 0;
            opacity: .86;
            font-size: .85rem;
        }

        .auth-hero h2 {
            font-family: 'Sora', sans-serif;
            font-weight: 700;
            font-size: 1.85rem;
            line-height: 1.24;
            margin: 0 0 12px;
            position: relative;
            z-index: 2;
        }

        .auth-hero .hero-subtitle {
            margin: 0 0 22px;
            opacity: .91;
            max-width: 470px;
            position: relative;
            z-index: 2;
        }

        .hero-list {
            list-style: none;
            margin: 0;
            padding: 0;
            display: grid;
            gap: 10px;
            position: relative;
            z-index: 2;
        }

        .hero-list li {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: .93rem;
        }

        .hero-list i {
            width: 28px;
            height: 28px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, .18);
        }

        .auth-panel {
            background: var(--login-surface);
            padding: 36px 32px;
            display: flex;
            align-items: center;
        }

        .auth-card {
            width: 100%;
            border: 1px solid #d9e7f2;
            border-radius: 18px;
            box-shadow: 0 14px 30px rgba(8, 53, 76, .08);
            padding: 24px 22px;
        }

        .auth-card h3 {
            margin: 0;
            font-family: 'Sora', sans-serif;
            font-size: 1.3rem;
            color: #133f56;
        }

        .auth-card p {
            margin: 6px 0 0;
            color: var(--login-muted);
            font-size: .92rem;
        }

        .auth-form {
            margin-top: 18px;
        }

        .auth-label {
            font-size: .72rem;
            text-transform: uppercase;
            letter-spacing: .5px;
            font-weight: 800;
            color: #23415f;
            margin-bottom: 6px;
        }

        .auth-form .form-control {
            height: calc(2.45rem + 2px);
            border-radius: 11px;
            border-color: #d1deeb;
            padding-right: 42px;
        }

        .auth-form .form-control:focus {
            border-color: #6aa7b2;
            box-shadow: 0 0 0 .12rem rgba(15, 108, 116, .16);
        }

        .input-icon {
            position: absolute;
            right: 12px;
            top: 38px;
            color: #6b7280;
            pointer-events: none;
        }

        .password-toggle {
            position: absolute;
            right: 8px;
            top: 33px;
            border: 0;
            background: transparent;
            color: #6b7280;
            width: 30px;
            height: 30px;
            border-radius: 8px;
            cursor: pointer;
        }

        .password-toggle:hover {
            background: #f2f7fd;
            color: #1f455f;
        }

        .btn-login {
            border: 0;
            width: 100%;
            border-radius: 12px;
            padding: .67rem 1rem;
            font-weight: 700;
            color: #fff;
            background: linear-gradient(135deg, var(--login-primary) 0%, var(--login-primary-dark) 100%);
            box-shadow: 0 10px 22px rgba(15, 108, 116, .24);
        }

        .btn-login:hover {
            color: #fff;
            filter: brightness(1.03);
        }

        .auth-help {
            margin-top: 14px;
            font-size: .83rem;
            color: #5a6f82;
            display: flex;
            justify-content: space-between;
            gap: 10px;
            flex-wrap: wrap;
        }

        .auth-help strong {
            color: #23415f;
        }

        .alert {
            border-radius: 12px;
            font-size: .9rem;
        }

        @media (max-width: 991px) {
            .auth-shell {
                grid-template-columns: 1fr;
                max-width: 560px;
            }

            .auth-hero {
                padding: 28px 24px;
            }

            .auth-panel {
                padding: 22px 20px;
            }
        }

        @media (max-width: 576px) {
            body {
                padding: 14px;
            }

            .auth-card {
                padding: 20px 16px;
            }

            .auth-hero h2 {
                font-size: 1.5rem;
            }
        }
    </style>
@endsection

@section('title', 'Login | Sistema de Estacionamento')

@section('body')
    <div class="auth-shell theme-fade-in">
        <section class="auth-hero">
            <div class="auth-brand">
                <img src="{{ asset('img/LogoEstacionamento.png') }}" alt="Logo Estacionamento">
                <div>
                    <h1>Estacionamento</h1>
                    <p>Painel operacional</p>
                </div>
            </div>

            <h2>Controle de vagas, pagamentos e relatorios em um unico painel.</h2>
            <p class="hero-subtitle">
                Acesse para acompanhar entradas, saidas e resultados financeiros em tempo real.
            </p>

            <ul class="hero-list">
                <li><i class="fas fa-car-side"></i>Gestao de carros, motos e caminhonetes</li>
                <li><i class="fas fa-qrcode"></i>Fluxo de pagamento com Pix, cartao e boleto</li>
                <li><i class="fas fa-chart-line"></i>Relatorios e contabilidade no mesmo sistema</li>
            </ul>
        </section>

        <section class="auth-panel">
            <div class="auth-card">
                <h3>Entrar no sistema</h3>
                <p>Use seu e-mail e senha para acessar o painel administrativo.</p>

                @if (session('error'))
                    <div class="alert alert-danger mt-3 mb-0">
                        {{ session('error') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger mt-3 mb-0">
                        Verifique os dados informados e tente novamente.
                    </div>
                @endif

                <form action="{{ route('login') }}" method="post" autocomplete="off" class="auth-form">
                    @csrf

                    <div class="mb-3 position-relative">
                        <label class="auth-label" for="email">E-mail</label>
                        <input id="email" type="email" name="email"
                            class="form-control @error('email') is-invalid @enderror"
                            placeholder="seuemail@empresa.com.br" value="{{ old('email') }}" autocomplete="off"
                            autofocus>
                        <i class="fas fa-envelope input-icon"></i>
                        @error('email')
                            <span class="invalid-feedback d-block" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="mb-3 position-relative">
                        <label class="auth-label" for="password">Senha</label>
                        <input id="password" type="password" name="password"
                            class="form-control @error('password') is-invalid @enderror"
                            placeholder="Digite sua senha" autocomplete="off">
                        <button class="password-toggle" type="button" id="togglePassword" aria-label="Mostrar senha">
                            <i class="fas fa-eye"></i>
                        </button>
                        @error('password')
                            <span class="invalid-feedback d-block" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-login">Entrar</button>
                </form>

                <div class="auth-help">
                    <span>Sem acesso? <strong>Solicite cadastro ao administrador.</strong></span>
                    <span>Suporte: <strong>contato@estacionamento.com</strong></span>
                </div>

                <a href="{{ route('monthly-access.login') }}" class="btn btn-outline-primary btn-block mt-3">
                    <i class="fas fa-user-lock mr-1"></i> Sou mensalista
                </a>
            </div>
        </section>
    </div>
@endsection

@section('adminlte_js')
    <script src="{{ asset('js/jquery.min.js') }}"></script>
    <script src="{{ asset('adminlte/dist/js/adminlte.min.js') }}"></script>
    <script src="{{ asset('overlayScrollbars/js/jquery.overlayScrollbars.min.js') }}"></script>
    <script src="{{ asset('popper/popper.min.js') }}"></script>
    <script src="{{ asset('bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script>
        (function () {
            var input = document.getElementById('password');
            var button = document.getElementById('togglePassword');
            if (!input || !button) {
                return;
            }

            button.addEventListener('click', function () {
                var icon = button.querySelector('i');
                var show = input.type === 'password';
                input.type = show ? 'text' : 'password';
                button.setAttribute('aria-label', show ? 'Ocultar senha' : 'Mostrar senha');
                if (icon) {
                    icon.classList.toggle('fa-eye', !show);
                    icon.classList.toggle('fa-eye-slash', show);
                }
            });
        })();
    </script>
    @parent
@endsection
