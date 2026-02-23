<form action="{{ route($route) }}" class="form-horizontal" method="POST">
    @csrf

    <h5 class="mb-3">Pagamentos (Pix e Cartao)</h5>

    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="pix_key">Chave Pix</label>
                <input type="text" placeholder="CPF, CNPJ, email, telefone ou chave aleatoria"
                    name="pix_key" id="pix_key"
                    class="form-control @error('pix_key') is-invalid @enderror"
                    value="{{ old('pix_key', $estacionamentos->pix_key) }}" />
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="pix_beneficiary_name">Nome para recebimento Pix</label>
                <input type="text" placeholder="Nome do favorecido no QR Code"
                    name="pix_beneficiary_name" id="pix_beneficiary_name"
                    class="form-control @error('pix_beneficiary_name') is-invalid @enderror"
                    value="{{ old('pix_beneficiary_name', $estacionamentos->pix_beneficiary_name) }}" />
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="pix_city">Cidade Pix</label>
                <input type="text" placeholder="Cidade usada no padrao do Banco Central"
                    name="pix_city" id="pix_city"
                    class="form-control @error('pix_city') is-invalid @enderror"
                    value="{{ old('pix_city', $estacionamentos->pix_city ?? $estacionamentos->cidade) }}" />
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="pix_description">Descricao padrao do Pix</label>
                <input type="text" placeholder="Ex: Estacionamento - Ticket"
                    name="pix_description" id="pix_description"
                    class="form-control @error('pix_description') is-invalid @enderror"
                    value="{{ old('pix_description', $estacionamentos->pix_description) }}" />
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                <label for="card_machine_instructions">Instrucoes da maquininha de cartao</label>
                <textarea name="card_machine_instructions" id="card_machine_instructions"
                    class="form-control @error('card_machine_instructions') is-invalid @enderror"
                    placeholder="Ex: Passe no debito/credito, confirme o valor e clique em Confirmar Pagamento">{{ old('card_machine_instructions', $estacionamentos->card_machine_instructions) }}</textarea>
            </div>
        </div>
    </div>

    <hr>
    <h5 class="mb-3">Integracoes de Gateway (Stone/Cielo/Rede/Getnet/PagBank)</h5>

    <div class="row">
        <div class="col-md-4">
            <div class="form-group">
                <label for="payment_provider_default">Gateway padrao</label>
                <select name="payment_provider_default" id="payment_provider_default"
                    class="form-control @error('payment_provider_default') is-invalid @enderror">
                    <option value="manual" {{ old('payment_provider_default', $estacionamentos->payment_provider_default) === 'manual' ? 'selected' : '' }}>Manual</option>
                    <option value="stone" {{ old('payment_provider_default', $estacionamentos->payment_provider_default) === 'stone' ? 'selected' : '' }}>Stone</option>
                    <option value="cielo" {{ old('payment_provider_default', $estacionamentos->payment_provider_default) === 'cielo' ? 'selected' : '' }}>Cielo</option>
                    <option value="rede" {{ old('payment_provider_default', $estacionamentos->payment_provider_default) === 'rede' ? 'selected' : '' }}>Rede</option>
                    <option value="getnet" {{ old('payment_provider_default', $estacionamentos->payment_provider_default) === 'getnet' ? 'selected' : '' }}>Getnet</option>
                    <option value="pagbank" {{ old('payment_provider_default', $estacionamentos->payment_provider_default) === 'pagbank' ? 'selected' : '' }}>PagBank</option>
                </select>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label for="payment_environment">Ambiente</label>
                <select name="payment_environment" id="payment_environment"
                    class="form-control @error('payment_environment') is-invalid @enderror">
                    <option value="sandbox" {{ old('payment_environment', $estacionamentos->payment_environment) === 'sandbox' ? 'selected' : '' }}>Sandbox</option>
                    <option value="production" {{ old('payment_environment', $estacionamentos->payment_environment) === 'production' ? 'selected' : '' }}>Producao</option>
                </select>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label for="boleto_due_days">Vencimento boleto (dias)</label>
                <input type="number" min="1" max="30" name="boleto_due_days" id="boleto_due_days"
                    class="form-control @error('boleto_due_days') is-invalid @enderror"
                    value="{{ old('boleto_due_days', $estacionamentos->boleto_due_days ?: 3) }}" />
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="form-group">
                <label for="pagbank_token">PagBank Token</label>
                <input type="text" name="pagbank_token" id="pagbank_token"
                    class="form-control @error('pagbank_token') is-invalid @enderror"
                    value="{{ old('pagbank_token', $estacionamentos->pagbank_token) }}" />
            </div>
        </div>
        <div class="col-md-8">
            <div class="form-group">
                <label for="pagbank_api_base_url">PagBank Base URL (opcional)</label>
                <input type="url" name="pagbank_api_base_url" id="pagbank_api_base_url"
                    class="form-control @error('pagbank_api_base_url') is-invalid @enderror"
                    placeholder="https://sandbox.api.pagseguro.com"
                    value="{{ old('pagbank_api_base_url', $estacionamentos->pagbank_api_base_url) }}" />
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="form-group">
                <label for="cielo_merchant_id">Cielo Merchant ID</label>
                <input type="text" name="cielo_merchant_id" id="cielo_merchant_id"
                    class="form-control @error('cielo_merchant_id') is-invalid @enderror"
                    value="{{ old('cielo_merchant_id', $estacionamentos->cielo_merchant_id) }}" />
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label for="cielo_merchant_key">Cielo Merchant Key</label>
                <input type="text" name="cielo_merchant_key" id="cielo_merchant_key"
                    class="form-control @error('cielo_merchant_key') is-invalid @enderror"
                    value="{{ old('cielo_merchant_key', $estacionamentos->cielo_merchant_key) }}" />
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label for="cielo_api_base_url">Cielo Base URL (opcional)</label>
                <input type="url" name="cielo_api_base_url" id="cielo_api_base_url"
                    class="form-control @error('cielo_api_base_url') is-invalid @enderror"
                    placeholder="https://apisandbox.cieloecommerce.cielo.com.br"
                    value="{{ old('cielo_api_base_url', $estacionamentos->cielo_api_base_url) }}" />
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="stone_api_token">Stone API Token (futuro uso)</label>
                <input type="text" name="stone_api_token" id="stone_api_token"
                    class="form-control @error('stone_api_token') is-invalid @enderror"
                    value="{{ old('stone_api_token', $estacionamentos->stone_api_token) }}" />
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="stone_api_base_url">Stone Base URL (futuro uso)</label>
                <input type="url" name="stone_api_base_url" id="stone_api_base_url"
                    class="form-control @error('stone_api_base_url') is-invalid @enderror"
                    value="{{ old('stone_api_base_url', $estacionamentos->stone_api_base_url) }}" />
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="rede_api_token">Rede API Token (futuro uso)</label>
                <input type="text" name="rede_api_token" id="rede_api_token"
                    class="form-control @error('rede_api_token') is-invalid @enderror"
                    value="{{ old('rede_api_token', $estacionamentos->rede_api_token) }}" />
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="rede_api_base_url">Rede Base URL (futuro uso)</label>
                <input type="url" name="rede_api_base_url" id="rede_api_base_url"
                    class="form-control @error('rede_api_base_url') is-invalid @enderror"
                    value="{{ old('rede_api_base_url', $estacionamentos->rede_api_base_url) }}" />
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-3">
            <div class="form-group">
                <label for="getnet_client_id">Getnet Client ID (futuro uso)</label>
                <input type="text" name="getnet_client_id" id="getnet_client_id"
                    class="form-control @error('getnet_client_id') is-invalid @enderror"
                    value="{{ old('getnet_client_id', $estacionamentos->getnet_client_id) }}" />
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label for="getnet_client_secret">Getnet Client Secret</label>
                <input type="text" name="getnet_client_secret" id="getnet_client_secret"
                    class="form-control @error('getnet_client_secret') is-invalid @enderror"
                    value="{{ old('getnet_client_secret', $estacionamentos->getnet_client_secret) }}" />
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label for="getnet_seller_id">Getnet Seller ID</label>
                <input type="text" name="getnet_seller_id" id="getnet_seller_id"
                    class="form-control @error('getnet_seller_id') is-invalid @enderror"
                    value="{{ old('getnet_seller_id', $estacionamentos->getnet_seller_id) }}" />
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label for="getnet_api_base_url">Getnet Base URL</label>
                <input type="url" name="getnet_api_base_url" id="getnet_api_base_url"
                    class="form-control @error('getnet_api_base_url') is-invalid @enderror"
                    value="{{ old('getnet_api_base_url', $estacionamentos->getnet_api_base_url) }}" />
            </div>
        </div>
    </div>

    <hr>
    <h5 class="mb-3">Impressora de Ticket (ESC/POS)</h5>

    <div class="row">
        <div class="col-md-3">
            <div class="form-group">
                <input type="hidden" name="ticket_print_enabled" value="0">
                <div class="form-check mt-4">
                    <input class="form-check-input" type="checkbox" value="1" id="ticket_print_enabled"
                        name="ticket_print_enabled"
                        {{ old('ticket_print_enabled', $estacionamentos->ticket_print_enabled) ? 'checked' : '' }}>
                    <label class="form-check-label" for="ticket_print_enabled">
                        Ativar impressao direta
                    </label>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="form-group">
                <label for="ticket_printer_driver">Driver</label>
                <select name="ticket_printer_driver" id="ticket_printer_driver"
                    class="form-control @error('ticket_printer_driver') is-invalid @enderror">
                    <option value="windows" {{ old('ticket_printer_driver', $estacionamentos->ticket_printer_driver) === 'windows' ? 'selected' : '' }}>Windows (compartilhada)</option>
                    <option value="cups" {{ old('ticket_printer_driver', $estacionamentos->ticket_printer_driver) === 'cups' ? 'selected' : '' }}>CUPS (Linux)</option>
                    <option value="network" {{ old('ticket_printer_driver', $estacionamentos->ticket_printer_driver) === 'network' ? 'selected' : '' }}>Rede TCP/IP</option>
                    <option value="file" {{ old('ticket_printer_driver', $estacionamentos->ticket_printer_driver) === 'file' ? 'selected' : '' }}>Arquivo/Dispositivo</option>
                </select>
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-group">
                <label for="ticket_printer_target">Destino da impressora</label>
                <input type="text" name="ticket_printer_target" id="ticket_printer_target"
                    class="form-control @error('ticket_printer_target') is-invalid @enderror"
                    placeholder="Ex: POS-58 / localhost:9100 / /dev/usb/lp0"
                    value="{{ old('ticket_printer_target', $estacionamentos->ticket_printer_target) }}" />
                <small class="text-muted">Windows/CUPS: nome da impressora. Rede: IP/host (porta separada abaixo).</small>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-2">
            <div class="form-group">
                <label for="ticket_printer_port">Porta (rede)</label>
                <input type="number" min="1" max="65535" name="ticket_printer_port" id="ticket_printer_port"
                    class="form-control @error('ticket_printer_port') is-invalid @enderror"
                    value="{{ old('ticket_printer_port', $estacionamentos->ticket_printer_port ?: 9100) }}" />
            </div>
        </div>
        <div class="col-md-2">
            <div class="form-group">
                <label for="ticket_printer_timeout">Timeout (s)</label>
                <input type="number" min="1" max="60" name="ticket_printer_timeout" id="ticket_printer_timeout"
                    class="form-control @error('ticket_printer_timeout') is-invalid @enderror"
                    value="{{ old('ticket_printer_timeout', $estacionamentos->ticket_printer_timeout ?: 10) }}" />
            </div>
        </div>
        <div class="col-md-2">
            <div class="form-group">
                <label for="ticket_print_copies">Copias</label>
                <input type="number" min="1" max="5" name="ticket_print_copies" id="ticket_print_copies"
                    class="form-control @error('ticket_print_copies') is-invalid @enderror"
                    value="{{ old('ticket_print_copies', $estacionamentos->ticket_print_copies ?: 1) }}" />
            </div>
        </div>
        <div class="col-md-2">
            <div class="form-group">
                <label for="ticket_line_width">Largura (chars)</label>
                <input type="number" min="16" max="64" name="ticket_line_width" id="ticket_line_width"
                    class="form-control @error('ticket_line_width') is-invalid @enderror"
                    value="{{ old('ticket_line_width', $estacionamentos->ticket_line_width ?: 42) }}" />
            </div>
        </div>
        <div class="col-md-4">
            <div class="alert alert-light border mt-4 mb-0 small">
                Exemplo Windows: <code>POS-58</code> <br>
                Exemplo Rede: <code>192.168.0.120</code> e porta <code>9100</code>.
            </div>
        </div>
    </div>

    <div class="form-group">
        <div class="row">
            <div class="col-md-9"></div>
            <div class="col-md-3">
                <button class="btn btn-success btn-block">
                    <i style="margin-right: 5px; font-size: 15px;" class="fa fa-save" aria-hidden="true"></i> Salvar
                </button>
            </div>
        </div>
    </div>
</form>
