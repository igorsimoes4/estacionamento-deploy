<?php

namespace App\Http\Livewire;

use App\Models\Cars;
use App\Models\PaymentTransaction;
use App\Models\PriceCar;
use App\Models\PriceMotorcycle;
use App\Models\PriceTruck;
use App\Models\Settings;
use App\Services\Parking\DynamicPricingService;
use App\Services\Parking\ParkingSpotAllocatorService;
use App\Services\Payments\CheckoutGatewayService;
use App\Support\ItfBarcode;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;
use Livewire\WithPagination;

class VehiclesTable extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public string $search = '';
    public string $type = '';
    public string $status = 'ativo';
    public int $perPage = 15;
    public string $sortField = 'created_at';
    public string $sortDirection = 'desc';

    public bool $showCheckoutModal = false;
    public ?int $checkoutVehicleId = null;
    public string $checkoutVehicleModel = '';
    public string $checkoutVehiclePlate = '';
    public string $checkoutVehicleType = '';
    public float $checkoutAmount = 0.0;
    public string $checkoutMethod = 'pix';
    public string $checkoutProvider = 'manual';
    public string $checkoutReference = '';
    public string $checkoutCustomerName = '';
    public string $checkoutCustomerTaxId = '';
    public string $checkoutCustomerEmail = '';

    public string $pixPayload = '';
    public string $pixWarning = '';
    public string $pixQrImageUrl = '';
    public string $checkoutGatewayNotice = '';
    public string $cardMachineInstructions = '';

    public string $checkoutBoletoUrl = '';
    public string $checkoutBoletoBarcode = '';
    public string $checkoutBoletoDigitableLine = '';
    public string $checkoutBoletoDueDate = '';
    public string $checkoutBoletoBarcodeSvg = '';
    public string $checkoutExternalPaymentId = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'type' => ['except' => ''],
        'status' => ['except' => 'ativo'],
        'perPage' => ['except' => 15],
        'sortField' => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingType(): void
    {
        $this->resetPage();
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function updatingPerPage(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
            return;
        }

        $this->sortField = $field;
        $this->sortDirection = 'asc';
    }

    public function startCheckout(int $vehicleId): void
    {
        $car = Cars::query()->findOrFail($vehicleId);

        if ($car->status === 'finalizado') {
            session()->flash('create', 'Este veiculo ja foi finalizado.');
            return;
        }

        $settings = Settings::firstOrCreate(['id' => 1], []);

        $this->checkoutVehicleId = $car->id;
        $this->checkoutVehicleModel = (string) $car->modelo;
        $this->checkoutVehiclePlate = (string) $car->placa;
        $this->checkoutVehicleType = (string) $car->tipo_car;
        $this->checkoutAmount = $this->estimatePrice($car);
        $this->checkoutMethod = 'pix';
        $this->checkoutProvider = in_array((string) $settings->payment_provider_default, Cars::PAYMENT_PROVIDERS, true)
            ? (string) $settings->payment_provider_default
            : 'manual';
        $this->checkoutReference = '';
        $defaultTaxId = preg_replace('/\D+/', '', (string) $settings->cnpj_cpf_da_empresa);
        $this->checkoutCustomerName = 'Cliente ' . $car->placa;
        $this->checkoutCustomerTaxId = ($defaultTaxId && (strlen($defaultTaxId) === 11 || strlen($defaultTaxId) === 14))
            ? $defaultTaxId
            : '12345678909';
        $this->checkoutCustomerEmail = $this->defaultCustomerEmail($settings, (string) $car->placa);
        $this->clearGeneratedPaymentArtifacts();

        $this->cardMachineInstructions = trim((string) $settings->card_machine_instructions) !== ''
            ? (string) $settings->card_machine_instructions
            : 'Passe na maquininha, confirme o valor e depois clique em "Confirmar pagamento e finalizar".';

        $this->showCheckoutModal = true;
        $this->generatePix(false);
    }

    public function closeCheckout(): void
    {
        $this->resetCheckoutState();
    }

    public function updatedCheckoutMethod(string $value): void
    {
        if (!in_array($value, array_merge(Cars::PAYMENT_METHODS, ['boleto']), true)) {
            $this->checkoutMethod = 'pix';
            return;
        }

        $this->checkoutReference = '';
        $this->clearGeneratedPaymentArtifacts();

        if ($this->checkoutMethod === 'pix') {
            $this->generatePix(false);
        }
    }

    public function updatedCheckoutProvider(string $value): void
    {
        if (!in_array($value, Cars::PAYMENT_PROVIDERS, true)) {
            $this->checkoutProvider = 'manual';
        }

        $this->checkoutReference = '';
        $this->clearGeneratedPaymentArtifacts();

        if ($this->checkoutMethod === 'pix') {
            $this->generatePix(false);
        }
    }

    public function generatePix(bool $withFlash = true): void
    {
        if ($this->checkoutVehicleId === null) {
            return;
        }

        $settings = Settings::firstOrCreate(['id' => 1], []);

        try {
            $result = app(CheckoutGatewayService::class)->createPix(
                $this->checkoutProvider,
                $settings,
                $this->checkoutPayloadData($settings)
            );

            $this->pixPayload = (string) ($result['pix_copy_paste'] ?? '');
            $this->pixQrImageUrl = (string) ($result['pix_qr_image_url'] ?? '');
            $this->checkoutExternalPaymentId = (string) ($result['charge_id'] ?? '');
            $this->checkoutReference = (string) ($result['reference'] ?? $this->checkoutReference);
            $this->checkoutGatewayNotice = (string) ($result['warning'] ?? '');
            $this->pixWarning = '';

            $this->dispatchBrowserEvent('pix-qr-updated', [
                'payload' => $this->pixPayload,
            ]);
        } catch (\Throwable $e) {
            $this->pixPayload = '';
            $this->pixQrImageUrl = '';
            $this->checkoutExternalPaymentId = '';
            $this->pixWarning = $e->getMessage();

            if ($withFlash) {
                session()->flash('create', $this->pixWarning);
            }
        }
    }

    public function generateBoleto(bool $withFlash = true): void
    {
        if ($this->checkoutVehicleId === null) {
            return;
        }

        $settings = Settings::firstOrCreate(['id' => 1], []);

        try {
            $result = app(CheckoutGatewayService::class)->createBoleto(
                $this->checkoutProvider,
                $settings,
                $this->checkoutPayloadData($settings)
            );

            $this->checkoutBoletoUrl = (string) ($result['boleto_url'] ?? '');
            $this->checkoutBoletoBarcode = (string) ($result['boleto_barcode'] ?? '');
            $this->checkoutBoletoDigitableLine = (string) ($result['boleto_digitable_line'] ?? '');
            $this->checkoutBoletoDueDate = (string) ($result['boleto_due_date'] ?? '');
            if ($this->checkoutBoletoBarcode === '' && $this->checkoutBoletoDigitableLine !== '') {
                $this->checkoutBoletoBarcode = preg_replace('/\D+/', '', $this->checkoutBoletoDigitableLine) ?? '';
            }
            $this->checkoutBoletoBarcodeSvg = $this->buildBoletoBarcodeSvg(
                $this->checkoutBoletoBarcode,
                $this->checkoutBoletoDigitableLine
            );
            $this->checkoutExternalPaymentId = (string) ($result['charge_id'] ?? '');
            $this->checkoutReference = (string) ($result['reference'] ?? $this->checkoutReference);
            $this->checkoutGatewayNotice = (string) ($result['warning'] ?? '');
            $this->pixWarning = '';
        } catch (\Throwable $e) {
            $this->checkoutBoletoUrl = '';
            $this->checkoutBoletoBarcode = '';
            $this->checkoutBoletoDigitableLine = '';
            $this->checkoutBoletoDueDate = '';
            $this->checkoutBoletoBarcodeSvg = '';
            $this->checkoutExternalPaymentId = '';
            $this->pixWarning = $e->getMessage();

            if ($withFlash) {
                session()->flash('create', $this->pixWarning);
            }
        }
    }

    public function confirmCheckout(): void
    {
        if ($this->checkoutVehicleId === null) {
            return;
        }

        if (!in_array($this->checkoutProvider, Cars::PAYMENT_PROVIDERS, true)) {
            $this->addError('checkoutProvider', 'Gateway invalido.');
            return;
        }

        if (!in_array($this->checkoutMethod, array_merge(Cars::PAYMENT_METHODS, ['boleto']), true)) {
            $this->addError('checkoutMethod', 'Metodo de pagamento invalido.');
            return;
        }

        if ($this->checkoutMethod === 'pix' && $this->pixPayload === '') {
            $this->generatePix(false);

            if ($this->pixPayload === '') {
                $this->addError('checkoutMethod', 'Nao foi possivel gerar o Pix.');
                return;
            }
        }

        if ($this->checkoutMethod === 'boleto' && $this->checkoutBoletoUrl === '' && $this->checkoutBoletoDigitableLine === '') {
            $this->addError('checkoutMethod', 'Gere o boleto antes de confirmar o pagamento.');
            return;
        }

        $car = Cars::query()->findOrFail($this->checkoutVehicleId);

        if ($car->status === 'finalizado') {
            $this->resetCheckoutState();
            session()->flash('create', 'Este veiculo ja foi finalizado.');
            return;
        }

        $reference = trim($this->checkoutReference);

        if (($this->checkoutMethod === 'cartao_credito' || $this->checkoutMethod === 'cartao_debito') && $reference === '') {
            $reference = 'MAQ-' . now()->format('YmdHis');
        }

        if ($this->checkoutMethod === 'boleto' && $reference === '') {
            $reference = 'BOL-' . now()->format('YmdHis');
        }

        $car->preco = $this->estimatePrice($car);
        $car->status = 'finalizado';
        $car->saida = now();
        $car->payment_method = $this->checkoutMethod;
        $car->payment_provider = $this->checkoutProvider;
        $car->payment_status = 'paid';
        $car->external_payment_id = $this->checkoutExternalPaymentId !== '' ? $this->checkoutExternalPaymentId : null;
        $car->payment_reference = $reference !== '' ? $reference : null;
        $car->payment_url = $this->checkoutMethod === 'boleto' ? ($this->checkoutBoletoUrl !== '' ? $this->checkoutBoletoUrl : null) : null;
        $car->paid_at = now();
        $car->save();

        PaymentTransaction::query()->create([
            'reference' => 'CAR-' . $car->id . '-' . now()->format('YmdHis'),
            'provider' => $car->payment_provider ?: 'manual',
            'method' => $car->payment_method ?: 'dinheiro',
            'status' => 'paid',
            'type' => 'one_time',
            'amount_cents' => (int) round(((float) $car->preco) * 100),
            'currency' => 'BRL',
            'car_id' => $car->id,
            'external_id' => $car->external_payment_id,
            'payment_url' => $car->payment_url,
            'paid_at' => now(),
            'reconciled_at' => now(),
        ]);

        app(ParkingSpotAllocatorService::class)->releaseSpotByCar($car);

        $methodLabel = Cars::paymentMethodLabel($this->checkoutMethod);
        $providerLabel = Cars::paymentProviderLabel($this->checkoutProvider);
        $this->resetCheckoutState();

        session()->flash('create', "Pagamento confirmado via {$methodLabel} ({$providerLabel}) e veiculo finalizado.");
    }

    // Compatibilidade com fluxo anterior: finaliza direto com dinheiro/manual.
    public function finalize(int $vehicleId): void
    {
        $car = Cars::query()->findOrFail($vehicleId);

        if ($car->status === 'finalizado') {
            return;
        }

        $car->preco = $this->estimatePrice($car);
        $car->status = 'finalizado';
        $car->saida = now();
        $car->payment_method = 'dinheiro';
        $car->payment_provider = 'manual';
        $car->payment_status = 'paid';
        $car->paid_at = now();
        $car->save();

        PaymentTransaction::query()->create([
            'reference' => 'CAR-' . $car->id . '-' . now()->format('YmdHis'),
            'provider' => $car->payment_provider ?: 'manual',
            'method' => $car->payment_method ?: 'dinheiro',
            'status' => 'paid',
            'type' => 'one_time',
            'amount_cents' => (int) round(((float) $car->preco) * 100),
            'currency' => 'BRL',
            'car_id' => $car->id,
            'paid_at' => now(),
            'reconciled_at' => now(),
        ]);

        app(ParkingSpotAllocatorService::class)->releaseSpotByCar($car);

        session()->flash('create', 'Veiculo finalizado com sucesso.');
    }

    public function getStatsProperty(): array
    {
        return [
            'ativos' => Cars::parked()->count(),
            'finalizados_hoje' => Cars::finished()->whereDate('saida', today())->count(),
            'receita_hoje' => (float) Cars::finished()->whereDate('saida', today())->sum('preco'),
        ];
    }

    public function render()
    {
        $cars = Cars::query()
            ->when($this->search !== '', function (Builder $query) {
                $term = '%' . trim($this->search) . '%';
                $query->where(function (Builder $subQuery) use ($term) {
                    $subQuery->where('placa', 'like', $term)
                        ->orWhere('modelo', 'like', $term);
                });
            })
            ->when($this->type !== '', fn (Builder $query) => $query->where('tipo_car', $this->type))
            ->when($this->status === 'ativo', fn (Builder $query) => $query->parked())
            ->when($this->status === 'finalizado', fn (Builder $query) => $query->finished())
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        $cars->getCollection()->transform(function (Cars $car) {
            $car->live_price = $this->estimatePrice($car);
            $car->live_duration = $this->formatDuration($car);
            $car->payment_method_label = Cars::paymentMethodLabel($car->payment_method);
            $car->payment_provider_label = Cars::paymentProviderLabel($car->payment_provider);
            return $car;
        });

        return view('livewire.vehicles-table', [
            'cars' => $cars,
            'stats' => $this->stats,
        ]);
    }

    private function resetCheckoutState(): void
    {
        $this->showCheckoutModal = false;
        $this->checkoutVehicleId = null;
        $this->checkoutVehicleModel = '';
        $this->checkoutVehiclePlate = '';
        $this->checkoutVehicleType = '';
        $this->checkoutAmount = 0.0;
        $this->checkoutMethod = 'pix';
        $this->checkoutProvider = 'manual';
        $this->checkoutReference = '';
        $this->checkoutCustomerName = '';
        $this->checkoutCustomerTaxId = '';
        $this->checkoutCustomerEmail = '';
        $this->clearGeneratedPaymentArtifacts();
        $this->cardMachineInstructions = '';
        $this->resetErrorBag();
    }

    private function clearGeneratedPaymentArtifacts(): void
    {
        $this->pixPayload = '';
        $this->pixWarning = '';
        $this->pixQrImageUrl = '';
        $this->checkoutGatewayNotice = '';
        $this->checkoutBoletoUrl = '';
        $this->checkoutBoletoBarcode = '';
        $this->checkoutBoletoDigitableLine = '';
        $this->checkoutBoletoDueDate = '';
        $this->checkoutBoletoBarcodeSvg = '';
        $this->checkoutExternalPaymentId = '';
    }

    private function buildBoletoBarcodeSvg(string $barcode, string $digitableLine): string
    {
        $digits = preg_replace('/\D+/', '', $barcode !== '' ? $barcode : $digitableLine) ?? '';
        if ($digits === '') {
            return '';
        }

        return ItfBarcode::renderSvg($digits, 72, 2, 5, 10);
    }

    private function checkoutPayloadData(Settings $settings): array
    {
        $digits = preg_replace('/\D+/', '', (string) $this->checkoutCustomerTaxId);

        if (strlen($digits) !== 11 && strlen($digits) !== 14) {
            $digits = '12345678909';
        }

        $reference = strtoupper('EST' . str_pad((string) $this->checkoutVehicleId, 6, '0', STR_PAD_LEFT) . date('mdHis'));

        return [
            'reference' => $reference,
            'amount' => (float) $this->checkoutAmount,
            'amount_cents' => (int) round($this->checkoutAmount * 100),
            'description' => 'Ticket estacionamento ' . $this->checkoutVehiclePlate,
            'customer_name' => trim($this->checkoutCustomerName) !== '' ? trim($this->checkoutCustomerName) : ('Cliente ' . $this->checkoutVehiclePlate),
            'customer_email' => trim($this->checkoutCustomerEmail) !== '' ? trim($this->checkoutCustomerEmail) : $this->defaultCustomerEmail($settings, $this->checkoutVehiclePlate),
            'customer_tax_id' => $digits,
            'beneficiary_name' => (string) ($settings->pix_beneficiary_name ?: $settings->nome_da_empresa ?: 'Estacionamento'),
        ];
    }

    private function defaultCustomerEmail(Settings $settings, string $plate): string
    {
        $merchantEmail = strtolower(trim((string) ($settings->email_da_empresa ?? '')));
        $suffix = strtolower(preg_replace('/[^a-z0-9]/', '', $plate) ?: 'cliente');
        $suffix = substr($suffix, -12);

        if ($merchantEmail !== '' && filter_var($merchantEmail, FILTER_VALIDATE_EMAIL)) {
            [$local, $domain] = explode('@', $merchantEmail, 2);
            $local = preg_replace('/[^a-z0-9._-]/', '', strtolower($local)) ?: 'cliente';
            $local = substr($local, 0, 32);
            $aliasLocal = trim($local . '.cli' . $suffix, '.');

            return $aliasLocal . '@' . $domain;
        }

        return 'cliente.cli' . $suffix . '@gmail.com';
    }

    private function estimatePrice(Cars $car): float
    {
        if ($car->status === 'finalizado' && $car->preco !== null) {
            return (float) $car->preco;
        }

        $profile = $this->priceProfile((string) $car->tipo_car);
        $entryAt = Carbon::parse($car->created_at);
        $exitAt = $car->saida ? Carbon::parse($car->saida) : now();

        $minutes = max(1, $entryAt->diffInMinutes($exitAt));
        $days = intdiv($minutes, 1440);
        $remainingMinutes = $minutes % 1440;

        $months = intdiv($days, 30);
        $days = $days % 30;

        $amount = ($months * $profile['taxaMensal']) + ($days * $profile['valorDiaria']);

        if ($remainingMinutes > 0) {
            if ($remainingMinutes <= 30 && $months === 0 && $days === 0) {
                $amount += $profile['valorMinimo'];
            } else {
                $hourBlocks = (int) ceil($remainingMinutes / 60);
                $amount += $hourBlocks * $profile['valorHora'];

                if ($hourBlocks > 1) {
                    $amount += $profile['taxaAdicional'];
                }
            }
        }

        $amount = round($amount, 2);

        return app(DynamicPricingService::class)->apply($amount, (string) $car->tipo_car, $entryAt, $exitAt);
    }

    private function priceProfile(string $type): array
    {
        $defaults = [
            'valorHora' => 5,
            'valorMinimo' => 10,
            'valorDiaria' => 50,
            'taxaAdicional' => 17,
            'taxaMensal' => 400,
        ];

        $source = match ($type) {
            'moto' => PriceMotorcycle::query()->first(),
            'caminhonete' => PriceTruck::query()->first(),
            default => PriceCar::query()->first(),
        };

        if (!$source) {
            return $defaults;
        }

        return [
            'valorHora' => (float) $source->valorHora,
            'valorMinimo' => (float) $source->valorMinimo,
            'valorDiaria' => (float) $source->valorDiaria,
            'taxaAdicional' => (float) $source->taxaAdicional,
            'taxaMensal' => (float) $source->taxaMensal,
        ];
    }

    private function formatDuration(Cars $car): string
    {
        $start = Carbon::parse($car->created_at);
        $end = $car->saida ? Carbon::parse($car->saida) : now();
        $diff = $start->diff($end);

        $parts = [];

        if ($diff->m > 0) {
            $parts[] = $diff->m . ' mes(es)';
        }

        if ($diff->d > 0) {
            $parts[] = $diff->d . ' dia(s)';
        }

        if ($diff->h > 0) {
            $parts[] = $diff->h . ' h';
        }

        if ($diff->i > 0) {
            $parts[] = $diff->i . ' min';
        }

        return empty($parts) ? '1 min' : implode(' ', $parts);
    }
}
