<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal do Mensalista | Painel</title>
    @laravelPWA
    <link rel="icon" type="image/png" href="{{ asset('img/LogoEstacionamento.png') }}">
    <link rel="stylesheet" href="{{ asset('adminlte/dist/css/adminlte.css') }}">
    <link rel="stylesheet" href="{{ asset('fontawesome-free/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/panel-theme.css') }}">
    <style>
        body {
            min-height: 100vh;
            margin: 0;
            background: linear-gradient(145deg, #edf3f9 0%, #f5f8fc 100%);
            color: #1f2937;
        }

        .portal-wrapper {
            max-width: 980px;
            margin: 24px auto;
            padding: 0 12px;
        }

        .portal-hero {
            border-radius: 18px;
            padding: 18px 20px;
            color: #fff;
            background: linear-gradient(135deg, #0f6c74 0%, #084a57 100%);
            box-shadow: 0 14px 30px rgba(8, 53, 76, .22);
        }

        .portal-hero h1 {
            margin: 0;
            font-size: 1.4rem;
            font-family: 'Sora', sans-serif;
        }

        .portal-hero p {
            margin: 6px 0 0;
            opacity: .9;
        }
    </style>
</head>
<body>
    <div class="portal-wrapper">
        <div class="portal-hero d-flex flex-wrap justify-content-between align-items-center" style="gap: 10px;">
            <div>
                <h1>Ola, {{ $subscriber->name }}</h1>
                <p>Painel do mensalista</p>
            </div>
            <form method="POST" action="{{ route('monthly-access.logout') }}">
                @csrf
                <button type="submit" class="btn btn-outline-light btn-sm">
                    <i class="fas fa-sign-out-alt mr-1"></i> Sair
                </button>
            </form>
        </div>

        <div class="row mt-3">
            <div class="col-md-4 mb-3">
                <div class="theme-stat-card h-100">
                    <p class="theme-stat-label">Status da Mensalidade</p>
                    <h3>{{ $subscriber->is_active ? 'Ativa' : 'Inativa' }}</h3>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="theme-stat-card h-100">
                    <p class="theme-stat-label">Vencimento</p>
                    <h3>{{ optional($subscriber->end_date)->format('d/m/Y') }}</h3>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="theme-stat-card h-100">
                    <p class="theme-stat-label">Valor Mensal</p>
                    <h3>R$ {{ number_format((float) $subscriber->monthly_fee, 2, ',', '.') }}</h3>
                </div>
            </div>
        </div>

        <div class="card theme-card">
            <div class="card-header border-0">
                <h5 class="mb-0">Dados do Cadastro</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-2"><strong>CPF:</strong> {{ $subscriber->cpf }}</div>
                    <div class="col-md-6 mb-2"><strong>Telefone:</strong> {{ $subscriber->phone }}</div>
                    <div class="col-md-6 mb-2"><strong>E-mail:</strong> {{ $subscriber->email ?: 'Nao informado' }}</div>
                    <div class="col-md-6 mb-2"><strong>Inicio:</strong> {{ optional($subscriber->start_date)->format('d/m/Y') }}</div>
                    <div class="col-md-6 mb-2"><strong>Placa:</strong> {{ $subscriber->vehicle_plate }}</div>
                    <div class="col-md-6 mb-2"><strong>Tipo:</strong> {{ ucfirst($subscriber->vehicle_type) }}</div>
                    <div class="col-md-6 mb-2"><strong>Modelo:</strong> {{ $subscriber->vehicle_model ?: 'Nao informado' }}</div>
                    <div class="col-md-6 mb-2"><strong>Cor:</strong> {{ $subscriber->vehicle_color ?: 'Nao informado' }}</div>
                    <div class="col-12 mt-2">
                        <strong>Observacoes:</strong> {{ $subscriber->observations ?: 'Sem observacoes.' }}
                    </div>
                </div>
            </div>
        </div>

        <div class="card theme-card mt-3">
            <div class="card-header border-0 d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Boleto da Mensalidade</h5>
                <a href="{{ route('monthly-access.boleto.download') }}" class="btn btn-theme btn-sm" style="color: #fff;">
                    <i class="fas fa-file-download mr-1"></i> Baixar boleto
                </a>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-2">
                        <strong>Referencia:</strong>
                        {{ $subscriber->boleto_reference ?: ('MS-' . $subscriber->id . '-' . now()->format('Ym')) }}
                    </div>
                    <div class="col-md-4 mb-2">
                        <strong>Vencimento:</strong>
                        {{ optional($subscriber->boleto_due_date)->format('d/m/Y') ?: 'Sera definido ao gerar boleto' }}
                    </div>
                    <div class="col-md-4 mb-2">
                        <strong>Status:</strong>
                        {{ $subscriber->boleto_status ?: 'PENDING' }}
                    </div>
                    <div class="col-md-4 mb-2">
                        <strong>Gateway:</strong>
                        {{ strtoupper($subscriber->boleto_provider ?: 'manual') }}
                    </div>
                    <div class="col-md-4 mb-2">
                        <strong>Gerado em:</strong>
                        {{ optional($subscriber->boleto_generated_at)->format('d/m/Y H:i') ?: 'Ainda nao gerado' }}
                    </div>
                    <div class="col-md-4 mb-2">
                        <strong>Valor:</strong>
                        R$ {{ number_format((float) $subscriber->monthly_fee, 2, ',', '.') }}
                    </div>
                </div>

                @if (!empty($subscriber->boleto_digitable_line))
                    <div class="mt-2">
                        <strong>Linha digitavel:</strong>
                        <div class="mt-1 p-2 border rounded bg-light">
                            <code>{{ $subscriber->boleto_digitable_line }}</code>
                        </div>
                    </div>
                @endif

                @if (!empty($subscriber->boleto_url))
                    <div class="mt-3">
                        <a href="{{ $subscriber->boleto_url }}" target="_blank" rel="noopener" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-external-link-alt mr-1"></i> Abrir boleto no gateway
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</body>
</html>
