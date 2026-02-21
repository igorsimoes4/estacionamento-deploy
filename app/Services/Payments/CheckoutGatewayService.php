<?php

namespace App\Services\Payments;

use App\Models\Settings;
use App\Support\PixPayload;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class CheckoutGatewayService
{
    public function createPix(string $provider, Settings $settings, array $data): array
    {
        return match ($provider) {
            'pagbank' => $this->createPagBankPix($settings, $data),
            'cielo' => $this->createCieloPix($settings, $data),
            'stone', 'rede', 'getnet' => $this->createFallbackPix($provider, $settings, $data),
            default => $this->createFallbackPix('manual', $settings, $data),
        };
    }

    public function createBoleto(string $provider, Settings $settings, array $data): array
    {
        return match ($provider) {
            'pagbank' => $this->createPagBankBoleto($settings, $data),
            'cielo' => $this->createCieloBoleto($settings, $data),
            default => throw new RuntimeException('Boleto automatico disponivel apenas para PagBank e Cielo.'),
        };
    }

    private function createPagBankPix(Settings $settings, array $data): array
    {
        $token = trim((string) $settings->pagbank_token);
        if ($token === '') {
            throw new RuntimeException('Token PagBank nao configurado.');
        }

        $baseUrl = trim((string) $settings->pagbank_api_base_url);
        if ($baseUrl === '') {
            $baseUrl = ($settings->payment_environment === 'production')
                ? 'https://api.pagseguro.com'
                : 'https://sandbox.api.pagseguro.com';
        }

        $customerEmail = $this->resolveCustomerEmail($settings, $data);
        $customerTaxId = $this->resolveCustomerTaxId($settings, $data);
        $customerPhones = $this->resolveCustomerPhones($settings, $data);

        $requestBody = [
            'reference_id' => $data['reference'],
            'customer' => [
                'name' => $data['customer_name'],
                'email' => $customerEmail,
                'tax_id' => $customerTaxId,
                'phones' => $customerPhones,
            ],
            'items' => [
                [
                    'reference_id' => $data['reference'],
                    'name' => $data['description'],
                    'quantity' => 1,
                    'unit_amount' => $data['amount_cents'],
                ],
            ],
            'qr_codes' => [
                [
                    'amount' => [
                        'value' => $data['amount_cents'],
                    ],
                    'expiration_date' => Carbon::now()->addMinutes(30)->toIso8601String(),
                ],
            ],
        ];

        $response = Http::acceptJson()
            ->withToken($token)
            ->post(rtrim($baseUrl, '/') . '/orders', $requestBody);

        if (!$response->successful()) {
            throw new RuntimeException('Falha no PagBank (Pix): ' . $response->body());
        }

        $json = $response->json();
        $qrCode = $json['qr_codes'][0] ?? [];

        return [
            'provider' => 'pagbank',
            'charge_id' => (string) ($json['id'] ?? $data['reference']),
            'reference' => (string) ($json['reference_id'] ?? $data['reference']),
            'pix_copy_paste' => (string) ($qrCode['text'] ?? ''),
            'pix_qr_image_url' => (string) ($qrCode['links'][0]['href'] ?? ''),
            'status' => (string) ($json['charges'][0]['status'] ?? 'PENDING'),
            'payment_url' => (string) ($qrCode['links'][0]['href'] ?? ''),
            'raw' => $json,
        ];
    }

    private function createPagBankBoleto(Settings $settings, array $data): array
    {
        $token = trim((string) $settings->pagbank_token);
        if ($token === '') {
            throw new RuntimeException('Token PagBank nao configurado.');
        }

        $baseUrl = trim((string) $settings->pagbank_api_base_url);
        if ($baseUrl === '') {
            $baseUrl = ($settings->payment_environment === 'production')
                ? 'https://api.pagseguro.com'
                : 'https://sandbox.api.pagseguro.com';
        }

        $customerEmail = $this->resolveCustomerEmail($settings, $data);
        $customerTaxId = $this->resolveCustomerTaxId($settings, $data);
        $holderAddress = $this->resolveBoletoHolderAddress($settings, $data);
        $shippingAddress = $this->resolveShippingAddress($settings, $data, $holderAddress);
        $customerPhones = $this->resolveCustomerPhones($settings, $data);

        $daysUntilExpiration = max(1, (int) ($settings->boleto_due_days ?: 3));
        $dueDate = Carbon::today()->addDays($daysUntilExpiration)->format('Y-m-d');

        $requestBody = [
            'reference_id' => $data['reference'],
            'customer' => [
                'name' => $data['customer_name'],
                'email' => $customerEmail,
                'tax_id' => $customerTaxId,
                'phones' => $customerPhones,
            ],
            'items' => [
                [
                    'reference_id' => $data['reference'],
                    'name' => $data['description'],
                    'quantity' => 1,
                    'unit_amount' => $data['amount_cents'],
                ],
            ],
            'shipping' => [
                'address' => $shippingAddress,
            ],
            'charges' => [
                [
                    'reference_id' => $data['reference'],
                    'description' => $data['description'],
                    'amount' => [
                        'value' => $data['amount_cents'],
                        'currency' => 'BRL',
                    ],
                    'payment_method' => [
                        'type' => 'BOLETO',
                        'boleto' => [
                            'template' => 'COBRANCA',
                            'due_date' => $dueDate,
                            'days_until_expiration' => (string) $daysUntilExpiration,
                            'instruction_lines' => [
                                'line_1' => 'Pagamento referente ao ticket de estacionamento.',
                                'line_2' => 'Apos o pagamento, confirmar no sistema.',
                            ],
                            'holder' => [
                                'name' => $data['customer_name'],
                                'tax_id' => $customerTaxId,
                                'email' => $customerEmail,
                                'address' => $holderAddress,
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $response = Http::acceptJson()
            ->withToken($token)
            ->post(rtrim($baseUrl, '/') . '/orders', $requestBody);

        if (!$response->successful()) {
            throw new RuntimeException('Falha no PagBank (Boleto): ' . $response->body());
        }

        $json = $response->json();
        $charge = $json['charges'][0] ?? [];
        $paymentMethod = $charge['payment_method']['boleto'] ?? [];
        $linkList = $charge['links'] ?? [];
        $paymentUrl = '';

        foreach ($linkList as $link) {
            if (($link['rel'] ?? '') === 'PAY') {
                $paymentUrl = (string) ($link['href'] ?? '');
                break;
            }
        }

        return [
            'provider' => 'pagbank',
            'charge_id' => (string) ($charge['id'] ?? $json['id'] ?? $data['reference']),
            'reference' => (string) ($json['reference_id'] ?? $data['reference']),
            'boleto_url' => $paymentUrl,
            'boleto_barcode' => (string) ($paymentMethod['barcode'] ?? ''),
            'boleto_digitable_line' => (string) ($paymentMethod['formatted_barcode'] ?? ''),
            'boleto_due_date' => (string) ($paymentMethod['due_date'] ?? $dueDate),
            'status' => (string) ($charge['status'] ?? 'PENDING'),
            'payment_url' => $paymentUrl,
            'raw' => $json,
        ];
    }

    private function createCieloPix(Settings $settings, array $data): array
    {
        $merchantId = trim((string) $settings->cielo_merchant_id);
        $merchantKey = trim((string) $settings->cielo_merchant_key);

        if ($merchantId === '' || $merchantKey === '') {
            throw new RuntimeException('Credenciais Cielo nao configuradas.');
        }

        $baseUrl = trim((string) $settings->cielo_api_base_url);
        if ($baseUrl === '') {
            $baseUrl = ($settings->payment_environment === 'production')
                ? 'https://api.cieloecommerce.cielo.com.br'
                : 'https://apisandbox.cieloecommerce.cielo.com.br';
        }

        $requestBody = [
            'MerchantOrderId' => $data['reference'],
            'Customer' => [
                'Name' => $data['customer_name'],
            ],
            'Payment' => [
                'Type' => 'Pix',
                'Amount' => $data['amount_cents'],
            ],
        ];

        $response = Http::acceptJson()
            ->withHeaders([
                'MerchantId' => $merchantId,
                'MerchantKey' => $merchantKey,
            ])
            ->post(rtrim($baseUrl, '/') . '/1/sales', $requestBody);

        if (!$response->successful()) {
            throw new RuntimeException('Falha na Cielo (Pix): ' . $response->body());
        }

        $json = $response->json();
        $payment = $json['Payment'] ?? [];

        return [
            'provider' => 'cielo',
            'charge_id' => (string) ($payment['PaymentId'] ?? $data['reference']),
            'reference' => (string) ($json['MerchantOrderId'] ?? $data['reference']),
            'pix_copy_paste' => (string) ($payment['QrCodeString'] ?? ''),
            'pix_qr_image_url' => (string) ($payment['QrCodeBase64Image'] ?? ''),
            'status' => (string) ($payment['Status'] ?? 'PENDING'),
            'payment_url' => '',
            'raw' => $json,
        ];
    }

    private function createCieloBoleto(Settings $settings, array $data): array
    {
        $merchantId = trim((string) $settings->cielo_merchant_id);
        $merchantKey = trim((string) $settings->cielo_merchant_key);

        if ($merchantId === '' || $merchantKey === '') {
            throw new RuntimeException('Credenciais Cielo nao configuradas.');
        }

        $baseUrl = trim((string) $settings->cielo_api_base_url);
        if ($baseUrl === '') {
            $baseUrl = ($settings->payment_environment === 'production')
                ? 'https://api.cieloecommerce.cielo.com.br'
                : 'https://apisandbox.cieloecommerce.cielo.com.br';
        }

        $dueDate = Carbon::today()->addDays((int) ($settings->boleto_due_days ?: 3))->format('Y-m-d');

        $requestBody = [
            'MerchantOrderId' => $data['reference'],
            'Customer' => [
                'Name' => $data['customer_name'],
            ],
            'Payment' => [
                'Type' => 'Boleto',
                'Amount' => $data['amount_cents'],
                'Provider' => 'Bradesco2',
                'Address' => null,
                'BoletoNumber' => substr(preg_replace('/\D/', '', $data['reference']), 0, 12),
                'Assignor' => $data['beneficiary_name'],
                'Demonstrative' => 'Pagamento de estacionamento',
                'ExpirationDate' => $dueDate,
                'Identification' => $data['customer_name'],
                'Instructions' => 'Nao receber apos o vencimento',
            ],
        ];

        $response = Http::acceptJson()
            ->withHeaders([
                'MerchantId' => $merchantId,
                'MerchantKey' => $merchantKey,
            ])
            ->post(rtrim($baseUrl, '/') . '/1/sales', $requestBody);

        if (!$response->successful()) {
            throw new RuntimeException('Falha na Cielo (Boleto): ' . $response->body());
        }

        $json = $response->json();
        $payment = $json['Payment'] ?? [];

        return [
            'provider' => 'cielo',
            'charge_id' => (string) ($payment['PaymentId'] ?? $data['reference']),
            'reference' => (string) ($json['MerchantOrderId'] ?? $data['reference']),
            'boleto_url' => (string) ($payment['Url'] ?? ''),
            'boleto_barcode' => (string) ($payment['BarCodeNumber'] ?? ''),
            'boleto_digitable_line' => (string) ($payment['DigitableLine'] ?? ''),
            'boleto_due_date' => (string) ($payment['ExpirationDate'] ?? $dueDate),
            'status' => (string) ($payment['Status'] ?? 'PENDING'),
            'payment_url' => (string) ($payment['Url'] ?? ''),
            'raw' => $json,
        ];
    }

    private function createFallbackPix(string $provider, Settings $settings, array $data): array
    {
        if (trim((string) $settings->pix_key) === '') {
            throw new RuntimeException('Chave Pix nao configurada nas configuracoes.');
        }

        $payload = PixPayload::generate(
            (string) $settings->pix_key,
            (float) $data['amount'],
            (string) ($settings->pix_beneficiary_name ?: $settings->nome_da_empresa ?: 'Estacionamento'),
            (string) ($settings->pix_city ?: $settings->cidade ?: 'Cidade'),
            (string) $data['reference'],
            (string) ($settings->pix_description ?: $data['description'])
        );

        return [
            'provider' => $provider,
            'charge_id' => (string) $data['reference'],
            'reference' => (string) $data['reference'],
            'pix_copy_paste' => $payload,
            'pix_qr_image_url' => '',
            'status' => 'PENDING',
            'payment_url' => '',
            'warning' => 'Pix gerado via padrao Copia e Cola local. Integracao online deste provedor requer homologacao da API contratada.',
            'raw' => [],
        ];
    }

    private function resolveCustomerEmail(Settings $settings, array $data): string
    {
        $rawCustomerEmail = strtolower(trim((string) ($data['customer_email'] ?? '')));
        $merchantEmail = strtolower(trim((string) ($settings->email_da_empresa ?? '')));

        if (!$this->isGatewayFriendlyEmail($rawCustomerEmail)) {
            $rawCustomerEmail = '';
        }

        if ($rawCustomerEmail !== '' && ($merchantEmail === '' || $rawCustomerEmail !== $merchantEmail)) {
            return $rawCustomerEmail;
        }

        $reference = strtolower((string) ($data['reference'] ?? uniqid('cli', true)));
        $suffix = preg_replace('/[^a-z0-9]/', '', substr($reference, -12)) ?: 'cliente';

        if ($merchantEmail !== '' && $this->isGatewayFriendlyEmail($merchantEmail)) {
            [$local, $domain] = explode('@', $merchantEmail, 2);
            $local = preg_replace('/[^a-z0-9._-]/', '', strtolower($local)) ?: 'cliente';
            $local = substr($local, 0, 32);
            $aliasLocal = trim($local . '.cli' . $suffix, '.');

            return $aliasLocal . '@' . $domain;
        }

        return 'cliente.cli' . $suffix . '@gmail.com';
    }

    private function isGatewayFriendlyEmail(string $email): bool
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        [$local, $domain] = explode('@', strtolower($email), 2);
        if ($local === '' || $domain === '') {
            return false;
        }

        if (str_contains($local, '+')) {
            return false;
        }

        $blockedDomains = [
            'localhost',
            'local.invalid',
            'example.com',
            'example.net',
            'example.org',
            'invalid',
            'test',
        ];

        if (in_array($domain, $blockedDomains, true)) {
            return false;
        }

        return str_contains($domain, '.');
    }

    private function resolveCustomerTaxId(Settings $settings, array $data): string
    {
        $customerTaxId = $this->sanitizeTaxId((string) ($data['customer_tax_id'] ?? ''));
        if ($this->isValidTaxId($customerTaxId)) {
            return $customerTaxId;
        }

        $merchantTaxId = $this->sanitizeTaxId((string) ($settings->cnpj_cpf_da_empresa ?? ''));
        if ($this->isValidTaxId($merchantTaxId)) {
            return $merchantTaxId;
        }

        if ((string) ($settings->payment_environment ?? 'sandbox') !== 'production') {
            return '10642432074';
        }

        throw new RuntimeException('CPF/CNPJ do cliente invalido para emissao no PagBank.');
    }

    private function sanitizeTaxId(string $value): string
    {
        return preg_replace('/\D+/', '', $value) ?? '';
    }

    private function isValidTaxId(string $taxId): bool
    {
        return $this->isValidCpf($taxId) || $this->isValidCnpj($taxId);
    }

    private function isValidCpf(string $cpf): bool
    {
        if (strlen($cpf) !== 11 || preg_match('/^(\d)\1{10}$/', $cpf) === 1) {
            return false;
        }

        for ($t = 9; $t < 11; $t++) {
            $sum = 0;
            for ($i = 0; $i < $t; $i++) {
                $sum += ((int) $cpf[$i]) * (($t + 1) - $i);
            }
            $digit = ((10 * $sum) % 11) % 10;
            if ((int) $cpf[$t] !== $digit) {
                return false;
            }
        }

        return true;
    }

    private function isValidCnpj(string $cnpj): bool
    {
        if (strlen($cnpj) !== 14 || preg_match('/^(\d)\1{13}$/', $cnpj) === 1) {
            return false;
        }

        $weightsFirst = [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        $weightsSecond = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];

        $sum = 0;
        for ($i = 0; $i < 12; $i++) {
            $sum += ((int) $cnpj[$i]) * $weightsFirst[$i];
        }
        $remainder = $sum % 11;
        $digit1 = $remainder < 2 ? 0 : 11 - $remainder;
        if ((int) $cnpj[12] !== $digit1) {
            return false;
        }

        $sum = 0;
        for ($i = 0; $i < 13; $i++) {
            $sum += ((int) $cnpj[$i]) * $weightsSecond[$i];
        }
        $remainder = $sum % 11;
        $digit2 = $remainder < 2 ? 0 : 11 - $remainder;
        if ((int) $cnpj[13] !== $digit2) {
            return false;
        }

        return true;
    }

    private function resolveBoletoHolderAddress(Settings $settings, array $data): array
    {
        $rawAddress = trim((string) ($data['customer_address']['street'] ?? $settings->endereco ?? 'Rua Principal'));
        $street = $rawAddress;
        $number = trim((string) ($data['customer_address']['number'] ?? ''));

        if ($number === '' && preg_match('/\b(\d{1,6})\b/', $rawAddress, $matches) === 1) {
            $number = $matches[1];
            $street = trim(str_replace($matches[0], '', $rawAddress));
            if ($street === '') {
                $street = $rawAddress;
            }
        }

        $street = $street !== '' ? $street : 'Rua Principal';
        $number = $number !== '' ? $number : '100';

        $postalCode = preg_replace('/\D+/', '', (string) ($data['customer_address']['postal_code'] ?? $settings->cep ?? '')) ?? '';
        $postalCode = $postalCode !== '' ? substr(str_pad($postalCode, 8, '0'), 0, 8) : '01001000';

        $city = trim((string) ($data['customer_address']['city'] ?? $settings->cidade ?? 'Sao Paulo'));
        $city = $city !== '' ? $city : 'Sao Paulo';

        $regionCode = strtoupper(trim((string) ($data['customer_address']['region_code'] ?? $settings->estado ?? 'SP')));
        $regionCode = preg_replace('/[^A-Z]/', '', $regionCode) ?? 'SP';
        $regionCode = strlen($regionCode) === 2 ? $regionCode : 'SP';

        $locality = trim((string) ($data['customer_address']['locality'] ?? 'Centro'));
        $locality = $locality !== '' ? $locality : 'Centro';

        return [
            'street' => $street,
            'number' => $number,
            'locality' => $locality,
            'city' => $city,
            'region' => $regionCode,
            'region_code' => $regionCode,
            'country' => 'Brasil',
            'postal_code' => $postalCode,
        ];
    }

    private function resolveShippingAddress(Settings $settings, array $data, array $holderAddress): array
    {
        $address = $holderAddress;

        $address['country'] = 'BRA';

        if (!isset($address['region_code']) || trim((string) $address['region_code']) === '') {
            $address['region_code'] = strtoupper(trim((string) ($settings->estado ?? 'SP')));
        }

        if (!isset($address['region']) || trim((string) $address['region']) === '') {
            $address['region'] = $address['region_code'];
        }

        if (!empty($data['customer_address']['complement'])) {
            $address['complement'] = (string) $data['customer_address']['complement'];
        }

        return $address;
    }

    private function resolveCustomerPhones(Settings $settings, array $data): array
    {
        $raw = (string) ($data['customer_phone'] ?? $settings->telefone_da_empresa ?? '');
        $digits = preg_replace('/\D+/', '', $raw) ?? '';

        if (strlen($digits) >= 13) {
            $country = substr($digits, 0, 2);
            $area = substr($digits, 2, 2);
            $number = substr($digits, 4);
        } elseif (strlen($digits) >= 10) {
            $country = '55';
            $area = substr($digits, 0, 2);
            $number = substr($digits, 2);
        } else {
            $country = '55';
            $area = '11';
            $number = '999999998';
        }

        $number = substr(preg_replace('/\D+/', '', $number) ?? '999999998', 0, 9);
        if ($number === '') {
            $number = '999999998';
        }

        return [
            [
                'country' => $country,
                'area' => $area,
                'number' => $number,
                'type' => 'MOBILE',
            ],
        ];
    }
}
