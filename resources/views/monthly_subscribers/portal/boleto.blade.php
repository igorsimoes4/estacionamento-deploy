<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Boleto Mensalista</title>
    <style>
        * { box-sizing: border-box; font-family: DejaVu Sans, Arial, sans-serif; }
        body { margin: 0; color: #1f2937; font-size: 12px; }
        .sheet { padding: 20px; }
        .header {
            border: 1px solid #d1deeb;
            border-radius: 8px;
            padding: 14px;
            margin-bottom: 14px;
        }
        .title { margin: 0; font-size: 18px; font-weight: 700; color: #0f4454; }
        .subtitle { margin: 5px 0 0; color: #4b5563; }
        .grid { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
        .grid td {
            border: 1px solid #d1deeb;
            padding: 8px 10px;
            vertical-align: top;
        }
        .label { display: block; color: #6b7280; font-size: 10px; text-transform: uppercase; margin-bottom: 3px; }
        .value { font-size: 13px; font-weight: 600; color: #1f2937; }
        .line-box {
            border: 1px dashed #9aaec2;
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 14px;
            background: #f8fbff;
        }
        .line-box code {
            font-size: 14px;
            letter-spacing: .3px;
        }
        .barcode-box {
            border: 1px solid #d1deeb;
            border-radius: 8px;
            padding: 10px 12px;
            margin-bottom: 14px;
            background: #fff;
            text-align: center;
        }
        .barcode-svg {
            display: inline-block;
            max-width: 100%;
        }
        .barcode-img {
            width: 100%;
            max-width: 760px;
            height: 74px;
            object-fit: contain;
        }
        .barcode-digits {
            margin-top: 6px;
            font-size: 11px;
            letter-spacing: .4px;
            color: #374151;
            word-break: break-all;
        }
        .note {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 10px 12px;
            color: #374151;
            background: #fcfcfd;
            line-height: 1.4;
        }
        .small { font-size: 10px; color: #6b7280; margin-top: 8px; }
    </style>
</head>
<body>
    <div class="sheet">
        <div class="header">
            <h1 class="title">Boleto de Mensalidade</h1>
            <p class="subtitle">
                {{ $settings->nome_da_empresa ?: 'Estacionamento' }} - Referencia {{ $boleto['reference'] }}
            </p>
        </div>

        <table class="grid">
            <tr>
                <td width="50%">
                    <span class="label">Pagador</span>
                    <span class="value">{{ $subscriber->name }}</span>
                </td>
                <td width="50%">
                    <span class="label">CPF</span>
                    <span class="value">{{ $subscriber->cpf }}</span>
                </td>
            </tr>
            <tr>
                <td>
                    <span class="label">Vencimento</span>
                    <span class="value">{{ optional($boleto['due_date'])->format('d/m/Y') }}</span>
                </td>
                <td>
                    <span class="label">Valor</span>
                    <span class="value">R$ {{ number_format((float) $boleto['amount'], 2, ',', '.') }}</span>
                </td>
            </tr>
            <tr>
                <td>
                    <span class="label">Gateway</span>
                    <span class="value">{{ strtoupper((string) ($boleto['provider'] ?? 'manual')) }}</span>
                </td>
                <td>
                    <span class="label">Status</span>
                    <span class="value">{{ (string) ($boleto['status'] ?? 'PENDING') }}</span>
                </td>
            </tr>
        </table>

        <div class="line-box">
            <span class="label">Linha Digitavel</span>
            <code>{{ $boleto['digitable_line'] ?: 'Nao informada pelo gateway' }}</code>
        </div>

        <div class="barcode-box">
            <span class="label">Codigo de Barras</span>
            @if (!empty($barcodeSvgDataUri))
                <img src="{{ $barcodeSvgDataUri }}" alt="Codigo de barras do boleto" class="barcode-img">
            @elseif (!empty($barcodeSvg))
                <div class="barcode-svg">{!! $barcodeSvg !!}</div>
                <div class="barcode-digits">{{ $barcodeDigits }}</div>
            @else
                <div class="barcode-digits">Codigo de barras indisponivel para este boleto.</div>
            @endif
            @if (!empty($barcodeDigits))
                <div class="barcode-digits">{{ $barcodeDigits }}</div>
            @endif
        </div>

        <table class="grid">
            <tr>
                <td width="50%">
                    <span class="label">Beneficiario</span>
                    <span class="value">{{ $settings->nome_da_empresa ?: 'Estacionamento' }}</span>
                </td>
                <td width="50%">
                    <span class="label">Documento</span>
                    <span class="value">{{ $settings->cnpj_cpf_da_empresa ?: '-' }}</span>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <span class="label">Endereco</span>
                    <span class="value">
                        {{ trim(($settings->endereco ?: '-') . ' - ' . ($settings->cidade ?: '-') . '/' . ($settings->estado ?: '-')) }}
                    </span>
                </td>
            </tr>
        </table>

        <div class="note">
            @if (!empty($boleto['url']))
                Link para pagamento online: {{ $boleto['url'] }}
            @else
                Este boleto foi emitido em modo manual. Efetue o pagamento conforme orientacao da administracao.
            @endif

            @if (!empty($boleto['warning']))
                <div class="small">Observacao: {{ $boleto['warning'] }}</div>
            @endif
        </div>
    </div>
</body>
</html>
