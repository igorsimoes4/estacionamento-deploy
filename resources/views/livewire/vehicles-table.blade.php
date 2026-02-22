<div class="theme-fade-in" wire:poll.20s>
    @if (session()->has('create'))
        <div class="alert alert-success shadow-sm">
            {{ session('create') }}
        </div>
    @endif

    @if ($pixWarning !== '')
        <div class="alert alert-warning shadow-sm">
            {{ $pixWarning }}
        </div>
    @endif

    <div class="row mb-4">
        <div class="col-md-4">
            <div class="theme-stat-card">
                <p class="theme-stat-label">Veiculos ativos</p>
                <h3>{{ $stats['ativos'] }}</h3>
            </div>
        </div>
        <div class="col-md-4">
            <div class="theme-stat-card">
                <p class="theme-stat-label">Finalizados hoje</p>
                <h3>{{ $stats['finalizados_hoje'] }}</h3>
            </div>
        </div>
        <div class="col-md-4">
            <div class="theme-stat-card">
                <p class="theme-stat-label">Receita de hoje</p>
                <h3>R$ {{ number_format($stats['receita_hoje'], 2, ',', '.') }}</h3>
            </div>
        </div>
    </div>

    <div class="card theme-card mb-4">
        <div class="card-body">
            <div class="row align-items-end">
                <div class="col-md-4 mb-2">
                    <label class="small text-uppercase font-weight-bold">Buscar</label>
                    <input type="text" wire:model.debounce.400ms="search" class="form-control"
                        placeholder="Placa ou modelo">
                </div>
                <div class="col-md-2 mb-2">
                    <label class="small text-uppercase font-weight-bold">Tipo</label>
                    <select wire:model="type" class="form-control">
                        <option value="">Todos</option>
                        <option value="carro">Carro</option>
                        <option value="moto">Moto</option>
                        <option value="caminhonete">Caminhonete</option>
                    </select>
                </div>
                <div class="col-md-2 mb-2">
                    <label class="small text-uppercase font-weight-bold">Status</label>
                    <select wire:model="status" class="form-control">
                        <option value="ativo">Ativos</option>
                        <option value="finalizado">Finalizados</option>
                        <option value="todos">Todos</option>
                    </select>
                </div>
                <div class="col-md-2 mb-2">
                    <label class="small text-uppercase font-weight-bold">Por pagina</label>
                    <select wire:model="perPage" class="form-control">
                        <option value="10">10</option>
                        <option value="15">15</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                    </select>
                </div>
                <div class="col-md-2 mb-2 text-md-end">
                    <a href="{{ route('cars.create') }}" class="btn btn-theme w-100">
                        <i class="fas fa-plus-circle"></i> Novo
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="card theme-card overflow-hidden">
        <div class="table-responsive">
            <table class="table table-theme mb-0">
                <thead>
                    <tr>
                        <th role="button" wire:click="sortBy('tipo_car')">Tipo</th>
                        <th role="button" wire:click="sortBy('modelo')">Modelo</th>
                        <th role="button" wire:click="sortBy('placa')">Placa</th>
                        <th role="button" wire:click="sortBy('created_at')">Entrada</th>
                        <th>Tempo</th>
                        <th>Status</th>
                        <th>Pagamento</th>
                        <th>Preco</th>
                        <th>Acoes</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($cars as $car)
                        <tr>
                            <td>
                                <span class="badge badge-pill
                                    {{ $car->tipo_car === 'carro' ? 'badge-primary' : ($car->tipo_car === 'moto' ? 'badge-warning' : 'badge-danger') }}">
                                    {{ ucfirst($car->tipo_car) }}
                                </span>
                            </td>
                            <td>{{ $car->modelo }}</td>
                            <td class="font-weight-bold">{{ $car->placa }}</td>
                            <td>{{ optional($car->created_at)->format('d/m/Y H:i') }}</td>
                            <td>{{ $car->live_duration }}</td>
                            <td>
                                @if ($car->status === 'finalizado')
                                    <span class="badge badge-secondary">Finalizado</span>
                                @else
                                    <span class="badge badge-success">Ativo</span>
                                @endif
                            </td>
                            <td>
                                @if ($car->status === 'finalizado')
                                    <span class="badge badge-info">{{ $car->payment_method_label }}</span>
                                    <div class="small text-muted mt-1">{{ $car->payment_provider_label }}</div>
                                    @if ($car->paid_at)
                                        <div class="small text-muted mt-1">{{ optional($car->paid_at)->format('d/m H:i') }}</div>
                                    @endif
                                @else
                                    <span class="text-muted">A cobrar</span>
                                @endif
                            </td>
                            <td>R$ {{ number_format((float) $car->live_price, 2, ',', '.') }}</td>
                            <td>
                                <div class="d-flex flex-wrap" style="gap: 6px;">
                                    <a href="{{ route('cars.edit', $car->id) }}" class="btn btn-sm btn-outline-primary">
                                        Editar
                                    </a>

                                    @if ($car->status !== 'finalizado')
                                        <button type="button" class="btn btn-sm btn-outline-success"
                                            wire:click="startCheckout({{ $car->id }})">
                                            Cobrar
                                        </button>

                                        <form action="{{ route('pembayaran.print') }}" method="POST" target="_blank">
                                            @csrf
                                            <input type="hidden" name="tipo_car" value="{{ $car->tipo_car }}">
                                            <input type="hidden" name="placa" value="{{ $car->placa }}">
                                            <input type="hidden" name="data" value="{{ optional($car->created_at)->format('d/m/Y') }}">
                                            <input type="hidden" name="hora" value="{{ optional($car->created_at)->format('H:i:s') }}">
                                            <button class="btn btn-sm btn-outline-info" type="submit">Imprimir</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-5">Nenhum veiculo encontrado.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-footer bg-white border-0">
            {{ $cars->links() }}
        </div>
    </div>

    @if ($showCheckoutModal)
        @php
            $isPix = $checkoutMethod === 'pix';
            $isBoleto = $checkoutMethod === 'boleto';
            $isCard = in_array($checkoutMethod, ['cartao_credito', 'cartao_debito'], true);
        @endphp

        <div class="modal fade show d-block checkout-modal-overlay" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg modal-dialog-scrollable checkout-modal-dialog" role="document">
                <div class="modal-content theme-fade-in">
                    <div class="modal-header">
                        <h5 class="modal-title mb-0">Checkout de Pagamento</h5>
                        <button type="button" class="close" wire:click="closeCheckout" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row mb-3">
                            <div class="col-md-8">
                                <p class="mb-1"><strong>Veiculo:</strong> {{ $checkoutVehicleModel }} ({{ ucfirst($checkoutVehicleType) }})</p>
                                <p class="mb-0"><strong>Placa:</strong> {{ $checkoutVehiclePlate }}</p>
                            </div>
                            <div class="col-md-4 text-md-right">
                                <p class="mb-1 text-muted">Valor a receber</p>
                                <h4 class="m-0">R$ {{ number_format((float) $checkoutAmount, 2, ',', '.') }}</h4>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="small text-uppercase font-weight-bold">Gateway</label>
                                <select wire:model="checkoutProvider" class="form-control">
                                    <option value="manual">Manual</option>
                                    <option value="stone">Stone</option>
                                    <option value="cielo">Cielo</option>
                                    <option value="rede">Rede</option>
                                    <option value="getnet">Getnet</option>
                                    <option value="pagbank">PagBank</option>
                                </select>
                                @error('checkoutProvider')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="small text-uppercase font-weight-bold">Metodo de pagamento</label>
                                <select wire:model="checkoutMethod" class="form-control">
                                    <option value="pix">Pix (QR Code)</option>
                                    <option value="boleto">Boleto</option>
                                    <option value="cartao_credito">Cartao credito (maquininha)</option>
                                    <option value="cartao_debito">Cartao debito (maquininha)</option>
                                    <option value="dinheiro">Dinheiro</option>
                                    <option value="transferencia">Transferencia</option>
                                    <option value="outro">Outro</option>
                                </select>
                                @error('checkoutMethod')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="small text-uppercase font-weight-bold">
                                    {{ $isPix ? 'TxID Pix' : ($isBoleto ? 'Referencia Boleto' : 'Referencia / NSU') }}
                                </label>
                                <input type="text" wire:model.defer="checkoutReference" class="form-control"
                                    placeholder="{{ $isPix ? 'Gerado automaticamente no Pix' : ($isBoleto ? 'Gerado pelo gateway de boleto' : 'Ex: NSU da maquininha') }}">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-5 mb-3">
                                <label class="small text-uppercase font-weight-bold">Cliente (nome)</label>
                                <input type="text" wire:model.defer="checkoutCustomerName" class="form-control"
                                    placeholder="Nome do pagador">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="small text-uppercase font-weight-bold">CPF/CNPJ</label>
                                <input type="text" wire:model.defer="checkoutCustomerTaxId" class="form-control"
                                    placeholder="Somente numeros">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="small text-uppercase font-weight-bold">Email</label>
                                <input type="email" wire:model.defer="checkoutCustomerEmail" class="form-control"
                                    placeholder="email@cliente.com">
                            </div>
                        </div>

                        @if ($checkoutGatewayNotice !== '')
                            <div class="alert alert-warning">{{ $checkoutGatewayNotice }}</div>
                        @endif

                        @if ($isPix)
                            @if ($pixWarning !== '')
                                <div class="alert alert-warning">{{ $pixWarning }}</div>
                            @endif

                            <div class="d-flex flex-wrap align-items-center mb-3" style="gap: 10px;">
                                <button type="button" class="btn btn-theme btn-sm" wire:click="generatePix">
                                    <i class="fas fa-qrcode mr-1"></i> Gerar/Atualizar QR Code Pix
                                </button>
                            </div>

                            <div class="row">
                                <div class="col-md-4 text-center mb-3">
                                    <div id="pix-qrcode" class="d-inline-block p-2 border rounded bg-white"></div>
                                    @if ($pixQrImageUrl !== '' && \Illuminate\Support\Str::startsWith($pixQrImageUrl, 'data:image'))
                                        <div class="mt-2">
                                            <img src="{{ $pixQrImageUrl }}" alt="QR Code Pix" style="max-width: 180px;">
                                        </div>
                                    @endif
                                    @if ($pixQrImageUrl !== '')
                                        <div class="mt-2">
                                            <a href="{{ $pixQrImageUrl }}" target="_blank" class="btn btn-outline-secondary btn-sm">
                                                Abrir QR no gateway
                                            </a>
                                        </div>
                                    @endif
                                </div>
                                <div class="col-md-8 mb-3">
                                    <label class="small text-uppercase font-weight-bold">Pix copia e cola</label>
                                    <textarea id="pix-copy-paste" class="form-control" rows="5" readonly>{{ $pixPayload }}</textarea>
                                    <button type="button" class="btn btn-outline-primary btn-sm mt-2" onclick="copyPixPayload()">
                                        <i class="fas fa-copy mr-1"></i> Copiar codigo Pix
                                    </button>
                                </div>
                            </div>
                        @elseif ($isBoleto)
                            @if ($pixWarning !== '')
                                <div class="alert alert-warning">{{ $pixWarning }}</div>
                            @endif

                            <div class="d-flex flex-wrap align-items-center mb-3" style="gap: 10px;">
                                <button type="button" class="btn btn-theme btn-sm" wire:click="generateBoleto">
                                    <i class="fas fa-file-invoice-dollar mr-1"></i> Gerar Boleto
                                </button>
                            </div>

                            <div class="row">
                                <div class="col-md-12 mb-2">
                                    <label class="small text-uppercase font-weight-bold">Linha digitavel</label>
                                    <input type="text" class="form-control" value="{{ $checkoutBoletoDigitableLine }}" readonly>
                                </div>
                                <div class="col-md-8 mb-2">
                                    <label class="small text-uppercase font-weight-bold">Codigo de barras</label>
                                    <input type="text" class="form-control" value="{{ $checkoutBoletoBarcode }}" readonly>
                                </div>
                                <div class="col-md-4 mb-2">
                                    <label class="small text-uppercase font-weight-bold">Vencimento</label>
                                    <input type="text" class="form-control" value="{{ $checkoutBoletoDueDate }}" readonly>
                                </div>
                                @if ($checkoutBoletoBarcodeSvg !== '')
                                    <div class="col-md-12 mb-2">
                                        <label class="small text-uppercase font-weight-bold">Visual do codigo de barras</label>
                                        <div class="border rounded bg-white p-2 text-center">
                                            <div class="d-inline-block checkout-boleto-barcode" style="max-width: 100%;">
                                                {!! $checkoutBoletoBarcodeSvg !!}
                                            </div>
                                        </div>
                                    </div>
                                @endif
                                <div class="col-md-12">
                                    @if ($checkoutBoletoUrl !== '')
                                        <a href="{{ $checkoutBoletoUrl }}" target="_blank" class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-external-link-alt mr-1"></i> Abrir boleto
                                        </a>
                                    @else
                                        <p class="text-muted mb-0">Boleto ainda nao gerado.</p>
                                    @endif
                                </div>
                            </div>
                        @elseif ($isCard)
                            <div class="alert alert-info">
                                <strong>Pagamento na maquininha:</strong><br>
                                {{ $cardMachineInstructions }}
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" wire:click="closeCheckout">Cancelar</button>
                        <button type="button" class="btn btn-theme" wire:click="confirmCheckout">
                            <i class="fas fa-check-circle mr-1"></i> Confirmar pagamento e finalizar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

@once
    <style>
        .checkout-modal-overlay {
            position: fixed;
            inset: 0;
            z-index: 1050;
            overflow-y: auto;
            padding: .75rem .4rem;
            background: rgba(8, 15, 30, .58);
        }

        .checkout-modal-dialog {
            margin: 0 auto;
            min-height: calc(100% - 1.5rem);
        }

        .checkout-modal-dialog .modal-content {
            max-height: calc(100vh - 1.5rem);
        }

        .checkout-modal-dialog .modal-body {
            overflow-y: auto;
        }

        .checkout-boleto-barcode svg {
            display: block;
            width: 100%;
            max-width: 100%;
            height: 72px;
            margin: 0 auto;
        }

        @media (min-width: 768px) {
            .checkout-modal-overlay {
                padding: 1rem;
            }

            .checkout-modal-dialog .modal-content {
                max-height: calc(100vh - 2rem);
            }
        }
    </style>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script>
        (function() {
            function renderPixQrcode(payload) {
                var container = document.getElementById('pix-qrcode');
                if (!container) return;

                container.innerHTML = '';

                if (!payload) return;

                new QRCode(container, {
                    text: payload,
                    width: 180,
                    height: 180
                });
            }

            function renderFromField() {
                var field = document.getElementById('pix-copy-paste');
                renderPixQrcode(field ? field.value : '');
            }

            window.addEventListener('pix-qr-updated', function(event) {
                renderPixQrcode(event.detail.payload || '');
            });

            document.addEventListener('livewire:load', function() {
                if (window.Livewire && typeof window.Livewire.hook === 'function') {
                    window.Livewire.hook('message.processed', function() {
                        renderFromField();
                    });
                }

                renderFromField();
            });

            window.copyPixPayload = function() {
                var field = document.getElementById('pix-copy-paste');
                if (!field || !field.value) return;

                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(field.value);
                    return;
                }

                field.select();
                field.setSelectionRange(0, 99999);
                document.execCommand('copy');
            };
        })();
    </script>
@endonce
