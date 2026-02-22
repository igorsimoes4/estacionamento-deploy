<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal do Mensalista | Login</title>
    @laravelPWA
    <link rel="icon" type="image/png" href="{{ asset('img/LogoEstacionamento.png') }}">
    <link rel="stylesheet" href="{{ asset('adminlte/dist/css/adminlte.css') }}">
    <link rel="stylesheet" href="{{ asset('fontawesome-free/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/panel-theme.css') }}">
    <style>
        body {
            min-height: 100vh;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 16px;
            background:
                radial-gradient(circle at 10% 15%, rgba(239, 155, 32, .24), transparent 28%),
                radial-gradient(circle at 90% 85%, rgba(15, 108, 116, .2), transparent 34%),
                linear-gradient(145deg, #ecf2f8 0%, #f4f8fc 100%);
        }

        .portal-auth {
            width: 100%;
            max-width: 460px;
            border-radius: 18px;
            background: #fff;
            box-shadow: 0 20px 44px rgba(10, 48, 74, .16);
            border: 1px solid #d8e5f1;
            overflow: hidden;
        }

        .portal-auth-header {
            padding: 18px 20px;
            color: #fff;
            background: linear-gradient(135deg, #0f6c74 0%, #084a57 100%);
        }

        .portal-auth-header p {
            margin: 0;
            opacity: .88;
            font-size: .85rem;
        }

        .portal-auth-header h1 {
            margin: 6px 0 0;
            font-size: 1.25rem;
            font-family: 'Sora', sans-serif;
        }

        .portal-auth-body {
            padding: 20px;
        }
    </style>
</head>
<body>
    <div class="portal-auth theme-fade-in">
        <div class="portal-auth-header">
            <p>Acesso do Cliente</p>
            <h1>Portal do Mensalista</h1>
        </div>
        <div class="portal-auth-body">
            @if (session('status'))
                <div class="alert alert-success">{{ session('status') }}</div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger">Verifique as informacoes e tente novamente.</div>
            @endif

            <form method="POST" action="{{ route('monthly-access.authenticate') }}" autocomplete="off">
                @csrf
                <div class="form-group">
                    <label for="cpf" class="small text-uppercase font-weight-bold">CPF</label>
                    <input type="text" id="cpf" name="cpf" class="form-control @error('cpf') is-invalid @enderror"
                        value="{{ old('cpf') }}" placeholder="000.000.000-00" required>
                    @error('cpf')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="access_password" class="small text-uppercase font-weight-bold">Senha</label>
                    <input type="password" id="access_password" name="access_password"
                        class="form-control @error('access_password') is-invalid @enderror"
                        placeholder="Sua senha de acesso" required>
                    @error('access_password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-theme btn-block" style="color: #fff;">
                    <i class="fas fa-sign-in-alt mr-1"></i> Entrar no Portal
                </button>
            </form>

            <div class="mt-3 text-muted" style="font-size: .9rem;">
                Nao possui senha? Solicite ativacao para a administracao do estacionamento.
            </div>

            <a href="{{ route('login') }}" class="btn btn-outline-primary btn-block mt-3">
                <i class="fas fa-arrow-left mr-1"></i> Voltar para Login Administrativo
            </a>
        </div>
    </div>
</body>
</html>
