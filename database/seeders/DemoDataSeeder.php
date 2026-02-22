<?php

namespace Database\Seeders;

use App\Models\AccountingEntry;
use App\Models\ActivityLog;
use App\Models\Cars;
use App\Models\CashShift;
use App\Models\CashShiftMovement;
use App\Models\DynamicPricingRule;
use App\Models\IntegrationEndpoint;
use App\Models\MonthlyBillingCycle;
use App\Models\MonthlySubscriber;
use App\Models\NotificationLog;
use App\Models\ParkingReservation;
use App\Models\ParkingSector;
use App\Models\ParkingSpot;
use App\Models\PaymentTransaction;
use App\Models\PriceCar;
use App\Models\PriceMotorcycle;
use App\Models\PriceTruck;
use App\Models\Settings;
use App\Models\SystemBackup;
use App\Models\SystemHealthCheck;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DemoDataSeeder extends Seeder
{
    private const SECTOR_CODES = ['A', 'B', 'C'];

    private array $usedPlates = [];
    private array $usedCpfs = [];

    public function run(): void
    {
        $originalAudit = (bool) config('audit.enabled', true);
        config(['audit.enabled' => false]);

        $faker = fake('pt_BR');

        $this->seedSettings();
        $this->seedUsers();
        $this->seedPriceTables();
        $sectors = $this->seedSectorsAndSpots();
        $this->seedDynamicPricingRules();

        $subscribers = $this->seedMonthlySubscribers($faker, 30);
        $this->seedMonthlyBillingCycles($subscribers);

        $this->seedCars($faker, $sectors, 90);
        $this->seedReservations($faker, $sectors, 28);
        $this->seedCashShifts();
        $this->seedAccountingEntries($faker, 45);
        $this->seedIntegrations();
        $this->seedNotifications($subscribers, $faker, 45);
        $this->seedHealthAndBackup();
        $this->seedActivityLogs($faker, 120);

        config(['audit.enabled' => $originalAudit]);
    }

    private function seedSettings(): void
    {
        $settings = Settings::firstOrCreate(['id' => 1], []);

        $settings->fill([
            'nome_da_empresa' => 'Estacionamento Demo',
            'endereco' => 'Av. Central, 1200',
            'cidade' => 'Sao Paulo',
            'estado' => 'SP',
            'cep' => '01001000',
            'telefone_da_empresa' => '(11) 4000-1000',
            'email_da_empresa' => 'contato@estacionamentodemo.com.br',
            'numero_de_registro_da_empresa' => 'REG-DEMO-001',
            'cnpj_cpf_da_empresa' => '12345678000199',
            'descricao_da_empresa' => 'Base de dados de teste para validacao funcional.',
            'coordenadas_gps' => '-23.550520,-46.633308',
            'pix_key' => 'demo-pix-key@estacionamentodemo.com.br',
            'pix_beneficiary_name' => 'ESTACIONAMENTO DEMO',
            'pix_city' => 'SAO PAULO',
            'pix_description' => 'Pagamento estacionamento',
            'card_machine_instructions' => 'Passe o cartao na maquininha e confirme o valor no sistema.',
            'payment_provider_default' => 'manual',
            'payment_environment' => 'sandbox',
            'boleto_due_days' => 3,
        ]);

        $settings->save();
    }

    private function seedUsers(): void
    {
        $users = [
            ['name' => 'Admin Demo', 'email' => 'admin@demo.local', 'role' => User::ROLE_ADMIN],
            ['name' => 'Operador Demo', 'email' => 'operador@demo.local', 'role' => User::ROLE_OPERATOR],
            ['name' => 'Financeiro Demo', 'email' => 'financeiro@demo.local', 'role' => User::ROLE_FINANCIAL],
        ];

        foreach ($users as $userData) {
            User::query()->updateOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'password' => Hash::make('12345678'),
                    'role' => $userData['role'],
                    'is_active' => true,
                ]
            );
        }
    }

    private function seedPriceTables(): void
    {
        PriceCar::query()->firstOrCreate([], [
            'valorHora' => 8,
            'valorMinimo' => 12,
            'valorDiaria' => 58,
            'taxaAdicional' => 5,
            'taxaMensal' => 400,
        ]);

        PriceMotorcycle::query()->firstOrCreate([], [
            'valorHora' => 4,
            'valorMinimo' => 7,
            'valorDiaria' => 35,
            'taxaAdicional' => 3,
            'taxaMensal' => 180,
        ]);

        PriceTruck::query()->firstOrCreate([], [
            'valorHora' => 10,
            'valorMinimo' => 16,
            'valorDiaria' => 70,
            'taxaAdicional' => 8,
            'taxaMensal' => 620,
        ]);
    }

    private function seedSectorsAndSpots(): Collection
    {
        $sectors = collect();

        foreach (self::SECTOR_CODES as $index => $code) {
            $sector = ParkingSector::query()->firstOrCreate(
                ['code' => $code],
                [
                    'name' => 'Setor ' . $code,
                    'capacity' => 24,
                    'is_active' => true,
                    'color' => ['#0f6c74', '#ef9b20', '#2c9f60'][$index] ?? '#0f6c74',
                    'map_rows' => 4,
                    'map_columns' => 6,
                    'notes' => 'Setor de teste ' . $code,
                ]
            );

            $sectors->push($sector);

            for ($i = 1; $i <= 24; $i++) {
                $vehicleType = $i <= 14 ? 'carro' : ($i <= 20 ? 'moto' : 'caminhonete');

                ParkingSpot::query()->firstOrCreate(
                    [
                        'parking_sector_id' => $sector->id,
                        'code' => sprintf('%s-%02d', $code, $i),
                    ],
                    [
                        'vehicle_type' => $vehicleType,
                        'status' => ParkingSpot::STATUS_AVAILABLE,
                    ]
                );
            }
        }

        return $sectors;
    }

    private function seedDynamicPricingRules(): void
    {
        $rules = [
            [
                'name' => 'Pico dia util',
                'vehicle_type' => null,
                'day_of_week' => 1,
                'starts_at' => '08:00',
                'ends_at' => '18:00',
                'occupancy_from' => 60,
                'occupancy_to' => 100,
                'multiplier' => 1.20,
                'flat_addition_cents' => 0,
                'priority' => 10,
                'is_active' => true,
            ],
            [
                'name' => 'Noite promocional',
                'vehicle_type' => 'carro',
                'day_of_week' => null,
                'starts_at' => '19:00',
                'ends_at' => '23:59',
                'occupancy_from' => 0,
                'occupancy_to' => 59,
                'multiplier' => 0.90,
                'flat_addition_cents' => 0,
                'priority' => 20,
                'is_active' => true,
            ],
            [
                'name' => 'Sabado alta demanda',
                'vehicle_type' => null,
                'day_of_week' => 6,
                'starts_at' => '09:00',
                'ends_at' => '14:00',
                'occupancy_from' => 70,
                'occupancy_to' => 100,
                'multiplier' => 1.35,
                'flat_addition_cents' => 300,
                'priority' => 5,
                'is_active' => true,
            ],
        ];

        foreach ($rules as $rule) {
            DynamicPricingRule::query()->updateOrCreate(
                ['name' => $rule['name']],
                $rule
            );
        }
    }

    private function seedMonthlySubscribers($faker, int $count): Collection
    {
        $subscribers = collect();
        $types = ['carro', 'moto', 'caminhonete'];

        for ($i = 0; $i < $count; $i++) {
            $cpf = $this->generateUniqueCpf();
            $startDate = Carbon::now()->subMonths(random_int(1, 12))->startOfMonth();
            $isOverdue = $i % 6 === 0;
            $endDate = $isOverdue
                ? Carbon::now()->subDays(random_int(2, 30))
                : Carbon::now()->addDays(random_int(10, 120));

            $subscriber = MonthlySubscriber::query()->create([
                'name' => $faker->name(),
                'cpf' => $cpf,
                'phone' => $faker->numerify('(11) 9####-####'),
                'email' => $faker->unique()->safeEmail(),
                'vehicle_plate' => $this->generateUniquePlate(),
                'vehicle_model' => $faker->words(2, true),
                'vehicle_color' => $faker->safeColorName(),
                'vehicle_type' => $types[array_rand($types)],
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
                'monthly_fee' => random_int(120, 780),
                'observations' => $faker->sentence(8),
                'access_enabled' => !$isOverdue,
                'access_password' => '12345678',
                'auto_renew_enabled' => $i % 5 !== 0,
                'recurring_payment_method' => ['boleto', 'pix', 'cartao_credito'][array_rand(['boleto', 'pix', 'cartao_credito'])],
                'delinquent_since' => $isOverdue ? Carbon::now()->subDays(random_int(1, 25))->toDateString() : null,
                'blocked_at' => $isOverdue && $i % 3 === 0 ? Carbon::now()->subDays(random_int(1, 10)) : null,
                'late_fee_percent' => 2,
                'daily_interest_percent' => 0.033,
                'boleto_reference' => 'MS-' . ($i + 1) . '-' . now()->format('Ym'),
                'boleto_provider' => 'manual',
                'boleto_digitable_line' => $this->fakeDigitableLine(),
                'boleto_barcode' => preg_replace('/\D+/', '', $this->fakeDigitableLine()) ?? null,
                'boleto_due_date' => Carbon::now()->addDays(random_int(1, 10))->toDateString(),
                'boleto_amount_cents' => random_int(12000, 78000),
                'boleto_status' => $isOverdue ? 'OVERDUE' : 'PENDING',
                'boleto_generated_at' => now()->subDays(random_int(0, 15)),
            ]);

            $subscribers->push($subscriber);
        }

        return $subscribers;
    }

    private function seedMonthlyBillingCycles(Collection $subscribers): void
    {
        foreach ($subscribers as $subscriber) {
            $competency = now()->format('Y-m');
            $reference = 'MS-' . $subscriber->id . '-' . now()->format('Ym');
            $isOverdue = $subscriber->delinquent_since !== null;
            $amountCents = (int) round((float) $subscriber->monthly_fee * 100);
            $fineCents = $isOverdue ? (int) round($amountCents * 0.02) : 0;
            $interestCents = $isOverdue ? (int) round($amountCents * 0.006) : 0;
            $total = $amountCents + $fineCents + $interestCents;

            $cycle = MonthlyBillingCycle::query()->firstOrCreate(
                ['reference' => $reference],
                [
                    'monthly_subscriber_id' => $subscriber->id,
                    'competency' => $competency,
                    'due_date' => now()->day(10)->toDateString(),
                    'amount_cents' => $amountCents,
                    'fine_cents' => $fineCents,
                    'interest_cents' => $interestCents,
                    'total_amount_cents' => $total,
                    'status' => $isOverdue ? 'overdue' : 'pending',
                ]
            );

            $transaction = PaymentTransaction::query()->firstOrCreate(
                ['reference' => 'TX-' . $reference],
                [
                    'provider' => 'manual',
                    'method' => $subscriber->recurring_payment_method ?: 'boleto',
                    'status' => $isOverdue ? 'pending' : ($subscriber->id % 3 === 0 ? 'paid' : 'pending'),
                    'type' => 'recurring',
                    'amount_cents' => $total,
                    'currency' => 'BRL',
                    'monthly_subscriber_id' => $subscriber->id,
                    'monthly_billing_cycle_id' => $cycle->id,
                    'barcode' => preg_replace('/\D+/', '', $this->fakeDigitableLine()) ?? null,
                    'digitable_line' => $this->fakeDigitableLine(),
                    'due_date' => now()->day(10)->toDateString(),
                    'paid_at' => $subscriber->id % 3 === 0 ? now()->subDays(random_int(1, 5)) : null,
                ]
            );

            if (!$cycle->payment_transaction_id) {
                $cycle->payment_transaction_id = $transaction->id;
                if ($transaction->status === 'paid') {
                    $cycle->status = 'paid';
                    $cycle->paid_at = $transaction->paid_at ?: now();
                }
                $cycle->save();
            }
        }
    }

    private function seedCars($faker, Collection $sectors, int $count): void
    {
        $vehicleTypes = ['carro', 'moto', 'caminhonete'];
        $paymentMethods = ['dinheiro', 'pix', 'cartao_credito', 'cartao_debito', 'boleto'];
        $paymentProviders = ['manual', 'pagbank', 'cielo', 'stone'];

        $availableSpots = ParkingSpot::query()
            ->where('status', ParkingSpot::STATUS_AVAILABLE)
            ->orderBy('id')
            ->get();

        for ($i = 0; $i < $count; $i++) {
            $type = $vehicleTypes[array_rand($vehicleTypes)];
            $entryAt = Carbon::now()->subHours(random_int(1, 300));
            $isActive = $i % 4 === 0;
            $plate = $this->generateUniquePlate();
            $price = random_int(800, 18500) / 100;

            $car = Cars::query()->create([
                'modelo' => $faker->words(2, true),
                'placa' => $plate,
                'entrada' => $entryAt->format('H:i:s'),
                'preco' => $isActive ? 0 : $price,
                'tipo_car' => $type,
                'status' => $isActive ? null : 'finalizado',
                'saida' => $isActive ? null : $entryAt->copy()->addMinutes(random_int(20, 720)),
                'payment_method' => $isActive ? null : $paymentMethods[array_rand($paymentMethods)],
                'payment_provider' => $isActive ? null : $paymentProviders[array_rand($paymentProviders)],
                'payment_status' => $isActive ? null : 'paid',
                'external_payment_id' => $isActive ? null : Str::upper(Str::random(16)),
                'payment_url' => null,
                'payment_reference' => $isActive ? null : 'PAY-' . now()->format('Ymd') . '-' . Str::upper(Str::random(8)),
                'paid_at' => $isActive ? null : $entryAt->copy()->addMinutes(random_int(30, 800)),
                'entry_source' => ['manual', 'api', 'anpr'][array_rand(['manual', 'api', 'anpr'])],
                'anpr_confidence' => $isActive ? random_int(78, 99) : null,
                'created_at' => $entryAt,
                'updated_at' => $isActive ? $entryAt : $entryAt->copy()->addMinutes(random_int(5, 400)),
            ]);

            if ($isActive && $availableSpots->isNotEmpty()) {
                $spot = $availableSpots->shift();
                $spot->status = ParkingSpot::STATUS_OCCUPIED;
                $spot->current_car_id = $car->id;
                $spot->occupied_since = $entryAt;
                $spot->save();

                $car->parking_sector_id = $spot->parking_sector_id;
                $car->parking_spot_id = $spot->id;
                $car->save();
            }

            if (!$isActive) {
                PaymentTransaction::query()->create([
                    'reference' => 'TX-CAR-' . $car->id,
                    'provider' => $car->payment_provider ?: 'manual',
                    'method' => $car->payment_method ?: 'dinheiro',
                    'status' => 'paid',
                    'type' => 'one_time',
                    'amount_cents' => (int) round($price * 100),
                    'currency' => 'BRL',
                    'car_id' => $car->id,
                    'external_id' => $car->external_payment_id,
                    'paid_at' => $car->paid_at ?: now(),
                    'reconciled_at' => $car->paid_at ?: now(),
                ]);
            }
        }
    }

    private function seedReservations($faker, Collection $sectors, int $count): void
    {
        $statusPool = ['pending', 'confirmed', 'cancelled', 'completed'];
        $availableSpots = ParkingSpot::query()
            ->where('status', ParkingSpot::STATUS_AVAILABLE)
            ->orderBy('id')
            ->get();

        for ($i = 0; $i < $count; $i++) {
            $status = $statusPool[array_rand($statusPool)];
            $startsAt = Carbon::now()->addHours(random_int(-72, 120));
            $endsAt = $startsAt->copy()->addHours(random_int(2, 12));
            $spot = $availableSpots->isNotEmpty() ? $availableSpots->shift() : null;

            $reservation = ParkingReservation::query()->create([
                'reference' => 'RES-' . now()->format('ymd') . '-' . Str::upper(Str::random(8)),
                'customer_name' => $faker->name(),
                'customer_email' => $faker->safeEmail(),
                'customer_phone' => $faker->numerify('(11) 9####-####'),
                'vehicle_plate' => $this->generateUniquePlate(),
                'vehicle_model' => $faker->words(2, true),
                'vehicle_type' => ['carro', 'moto', 'caminhonete'][array_rand(['carro', 'moto', 'caminhonete'])],
                'parking_sector_id' => $spot?->parking_sector_id,
                'parking_spot_id' => $spot?->id,
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
                'status' => $status,
                'estimated_amount_cents' => random_int(1200, 10000),
                'prepaid_amount_cents' => $status === 'confirmed' ? random_int(1200, 10000) : 0,
                'payment_status' => $status === 'confirmed' ? 'paid' : 'pending',
                'payment_provider' => 'manual',
                'external_payment_reference' => $status === 'confirmed' ? Str::upper(Str::random(12)) : null,
                'notes' => $faker->sentence(6),
                'checked_in_at' => $status === 'completed' ? $startsAt->copy()->addMinutes(10) : null,
                'checked_out_at' => $status === 'completed' ? $endsAt->copy()->addMinutes(20) : null,
            ]);

            if ($spot) {
                $spot->status = $status === 'confirmed' ? ParkingSpot::STATUS_RESERVED : ParkingSpot::STATUS_AVAILABLE;
                $spot->current_reservation_id = $status === 'confirmed' ? $reservation->id : null;
                $spot->save();
            }

            if ($reservation->payment_status === 'paid') {
                PaymentTransaction::query()->create([
                    'reference' => 'TX-RES-' . $reservation->id,
                    'provider' => 'manual',
                    'method' => 'pix',
                    'status' => 'paid',
                    'type' => 'reservation',
                    'amount_cents' => (int) $reservation->prepaid_amount_cents,
                    'currency' => 'BRL',
                    'parking_reservation_id' => $reservation->id,
                    'paid_at' => now()->subDays(random_int(1, 10)),
                    'reconciled_at' => now()->subDays(random_int(1, 10)),
                ]);
            }
        }
    }

    private function seedCashShifts(): void
    {
        $users = User::query()->whereIn('role', [User::ROLE_ADMIN, User::ROLE_OPERATOR])->get();
        if ($users->isEmpty()) {
            return;
        }

        for ($i = 0; $i < 6; $i++) {
            $openedAt = Carbon::now()->subDays($i + 1)->setTime(random_int(6, 9), 0);
            $closingAt = $openedAt->copy()->addHours(10);
            $opening = random_int(12000, 35000);
            $expected = $opening + random_int(25000, 160000);
            $counted = $expected + random_int(-7000, 7000);

            $shift = CashShift::query()->create([
                'user_id' => $users->random()->id,
                'code' => 'CX-' . $openedAt->format('Ymd') . '-' . Str::upper(Str::random(6)),
                'opened_at' => $openedAt,
                'closed_at' => $closingAt,
                'opening_amount_cents' => $opening,
                'expected_amount_cents' => $expected,
                'counted_amount_cents' => $counted,
                'difference_amount_cents' => $counted - $expected,
                'status' => 'closed',
                'notes' => 'Turno de teste gerado automaticamente.',
            ]);

            for ($m = 0; $m < 8; $m++) {
                $type = ['venda', 'entrada', 'reforco', 'sangria', 'saida'][array_rand(['venda', 'entrada', 'reforco', 'sangria', 'saida'])];
                CashShiftMovement::query()->create([
                    'cash_shift_id' => $shift->id,
                    'user_id' => $shift->user_id,
                    'type' => $type,
                    'method' => ['dinheiro', 'pix', 'cartao_credito', 'cartao_debito'][array_rand(['dinheiro', 'pix', 'cartao_credito', 'cartao_debito'])],
                    'amount_cents' => random_int(800, 12000),
                    'description' => 'Movimentacao automatica ' . ($m + 1),
                    'occurred_at' => $openedAt->copy()->addMinutes(random_int(30, 540)),
                ]);
            }
        }

        $openShift = CashShift::query()->where('status', 'open')->first();
        if (!$openShift) {
            CashShift::query()->create([
                'user_id' => $users->random()->id,
                'code' => 'CX-' . now()->format('Ymd') . '-' . Str::upper(Str::random(6)),
                'opened_at' => now()->subHours(3),
                'opening_amount_cents' => 20000,
                'expected_amount_cents' => 48500,
                'status' => 'open',
                'notes' => 'Caixa aberto para testes.',
            ]);
        }
    }

    private function seedAccountingEntries($faker, int $count): void
    {
        for ($i = 0; $i < $count; $i++) {
            $type = $i % 5 === 0 ? 'despesa' : 'receita';

            AccountingEntry::query()->create([
                'type' => $type,
                'category' => $type === 'receita' ? 'operacional' : 'administrativo',
                'description' => $type === 'receita' ? 'Recebimento ticket avulso' : 'Despesa operacional',
                'amount' => random_int(1500, 25000) / 100,
                'occurred_at' => Carbon::now()->subDays(random_int(0, 45))->toDateString(),
                'payment_method' => ['dinheiro', 'pix', 'cartao_credito', 'cartao_debito', 'boleto'][array_rand(['dinheiro', 'pix', 'cartao_credito', 'cartao_debito', 'boleto'])],
                'notes' => $faker->sentence(6),
            ]);
        }
    }

    private function seedIntegrations(): void
    {
        $items = [
            ['name' => 'Cancela Principal', 'type' => 'cancela', 'base_url' => 'https://api.cancela-demo.local'],
            ['name' => 'ANPR Entrada', 'type' => 'anpr', 'base_url' => 'https://api.anpr-demo.local'],
            ['name' => 'ERP Fiscal', 'type' => 'fiscal', 'base_url' => 'https://api.fiscal-demo.local'],
            ['name' => 'Webhook Pagamentos', 'type' => 'webhook', 'base_url' => 'https://api.payments-demo.local'],
        ];

        foreach ($items as $item) {
            IntegrationEndpoint::query()->updateOrCreate(
                ['name' => $item['name']],
                [
                    'type' => $item['type'],
                    'base_url' => $item['base_url'],
                    'auth_token' => Str::random(40),
                    'auth_secret' => Str::random(40),
                    'settings' => ['timeout' => 15, 'retry' => 2],
                    'is_active' => true,
                    'last_health_status' => 'ok',
                    'last_health_message' => 'Integracao operacional para ambiente de teste.',
                    'last_checked_at' => now()->subMinutes(random_int(5, 60)),
                ]
            );
        }
    }

    private function seedNotifications(Collection $subscribers, $faker, int $count): void
    {
        if ($subscribers->isEmpty()) {
            return;
        }

        for ($i = 0; $i < $count; $i++) {
            $subscriber = $subscribers->random();
            $channel = ['email', 'whatsapp'][array_rand(['email', 'whatsapp'])];
            $status = ['queued', 'sent', 'retry'][array_rand(['queued', 'sent', 'retry'])];

            NotificationLog::query()->create([
                'channel' => $channel,
                'recipient' => $channel === 'email' ? ($subscriber->email ?: $faker->safeEmail()) : ($subscriber->phone ?: $faker->numerify('(11) 9####-####')),
                'title' => 'Comunicado de teste',
                'message' => $faker->sentence(12),
                'status' => $status,
                'notifiable_type' => MonthlySubscriber::class,
                'notifiable_id' => $subscriber->id,
                'scheduled_at' => now()->subHours(random_int(1, 120)),
                'sent_at' => $status === 'sent' ? now()->subHours(random_int(1, 48)) : null,
                'provider_response' => $status === 'sent' ? 'ok' : null,
                'error_message' => $status === 'retry' ? 'Falha simulada no provider.' : null,
            ]);
        }
    }

    private function seedHealthAndBackup(): void
    {
        $checks = [
            'database' => ['status' => 'ok', 'message' => 'Conexao ativa.'],
            'storage' => ['status' => 'ok', 'message' => 'Leitura e escrita normal.'],
            'integrations' => ['status' => 'ok', 'message' => 'Integracoes com resposta.'],
            'queue' => ['status' => 'ok', 'message' => 'Fila operacional.'],
        ];

        foreach ($checks as $key => $data) {
            SystemHealthCheck::query()->updateOrCreate(
                ['check_key' => $key],
                [
                    'status' => $data['status'],
                    'message' => $data['message'],
                    'details' => ['latency_ms' => random_int(10, 120)],
                    'checked_at' => now()->subMinutes(random_int(1, 60)),
                ]
            );
        }

        for ($i = 0; $i < 4; $i++) {
            SystemBackup::query()->create([
                'backup_type' => 'app',
                'storage_disk' => 'local',
                'path' => 'backups/demo-backup-' . now()->subDays($i + 1)->format('Ymd-His') . '.json',
                'status' => 'completed',
                'size_bytes' => random_int(150000, 880000),
                'started_at' => now()->subDays($i + 1)->subMinutes(12),
                'finished_at' => now()->subDays($i + 1),
                'error_message' => null,
            ]);
        }
    }

    private function seedActivityLogs($faker, int $count): void
    {
        $events = [
            'http.request',
            'model.created',
            'model.updated',
            'auth.login_success',
            'payment.webhook.processed',
            'billing.overdue',
            'cash.shift.closed',
        ];

        $levels = ['info', 'warning', 'error'];

        for ($i = 0; $i < $count; $i++) {
            ActivityLog::query()->create([
                'event' => $events[array_rand($events)],
                'level' => $levels[array_rand($levels)],
                'description' => $faker->sentence(10),
                'actor_type' => User::class,
                'actor_id' => User::query()->inRandomOrder()->value('id'),
                'request_method' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'][array_rand(['GET', 'POST', 'PUT', 'PATCH', 'DELETE'])],
                'request_path' => ['painel/cars', 'painel/reservas', 'painel/operacao/financeiro', 'api/v1/gate/entry'][array_rand(['painel/cars', 'painel/reservas', 'painel/operacao/financeiro', 'api/v1/gate/entry'])],
                'route_name' => 'demo.route',
                'url' => 'https://demo.local/' . Str::random(8),
                'status_code' => [200, 201, 204, 400, 422, 500][array_rand([200, 201, 204, 400, 422, 500])],
                'ip_address' => $faker->ipv4(),
                'user_agent' => 'DemoSeeder/1.0',
                'subject_type' => Cars::class,
                'subject_id' => (string) random_int(1, 300),
                'old_values' => ['status' => 'pending'],
                'new_values' => ['status' => 'paid'],
                'metadata' => ['source' => 'demo_seeder'],
                'created_at' => now()->subMinutes(random_int(1, 9000)),
                'updated_at' => now(),
            ]);
        }
    }

    private function generateUniquePlate(): string
    {
        do {
            $letters = strtoupper(Str::random(3));
            $numbers = str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
            $plate = $letters . '-' . $numbers;
        } while (isset($this->usedPlates[$plate]));

        $this->usedPlates[$plate] = true;

        return $plate;
    }

    private function generateUniqueCpf(): string
    {
        do {
            $cpf = '';
            for ($i = 0; $i < 11; $i++) {
                $cpf .= (string) random_int(0, 9);
            }
        } while (isset($this->usedCpfs[$cpf]));

        $this->usedCpfs[$cpf] = true;

        return $cpf;
    }

    private function fakeDigitableLine(): string
    {
        $base = '';
        for ($i = 0; $i < 47; $i++) {
            $base .= (string) random_int(0, 9);
        }

        return substr($base, 0, 5) . '.' . substr($base, 5, 5)
            . ' ' . substr($base, 10, 5) . '.' . substr($base, 15, 6)
            . ' ' . substr($base, 21, 5) . '.' . substr($base, 26, 6)
            . ' ' . substr($base, 32, 1)
            . ' ' . substr($base, 33, 14);
    }
}
