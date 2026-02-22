<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('parking_sectors', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->unsignedInteger('capacity')->default(0);
            $table->boolean('is_active')->default(true);
            $table->string('color', 16)->default('#0f6c74');
            $table->unsignedSmallInteger('map_rows')->default(5);
            $table->unsignedSmallInteger('map_columns')->default(10);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('parking_spots', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('parking_sector_id')->index();
            $table->string('code');
            $table->string('vehicle_type', 20)->default('carro');
            $table->string('status', 20)->default('available');
            $table->unsignedBigInteger('current_car_id')->nullable()->index();
            $table->unsignedBigInteger('current_reservation_id')->nullable()->index();
            $table->dateTime('occupied_since')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['parking_sector_id', 'code']);
        });

        Schema::create('parking_reservations', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->string('customer_name');
            $table->string('customer_email')->nullable();
            $table->string('customer_phone')->nullable();
            $table->string('vehicle_plate')->index();
            $table->string('vehicle_model')->nullable();
            $table->string('vehicle_type', 20)->default('carro');
            $table->unsignedBigInteger('parking_sector_id')->nullable()->index();
            $table->unsignedBigInteger('parking_spot_id')->nullable()->index();
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');
            $table->string('status', 20)->default('pending');
            $table->integer('estimated_amount_cents')->default(0);
            $table->integer('prepaid_amount_cents')->default(0);
            $table->string('payment_status', 20)->default('pending');
            $table->string('payment_provider', 32)->nullable();
            $table->string('external_payment_reference', 120)->nullable();
            $table->text('notes')->nullable();
            $table->dateTime('checked_in_at')->nullable();
            $table->dateTime('checked_out_at')->nullable();
            $table->timestamps();
        });

        Schema::create('dynamic_pricing_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('vehicle_type', 20)->nullable();
            $table->unsignedTinyInteger('day_of_week')->nullable();
            $table->time('starts_at')->nullable();
            $table->time('ends_at')->nullable();
            $table->unsignedTinyInteger('occupancy_from')->default(0);
            $table->unsignedTinyInteger('occupancy_to')->default(100);
            $table->decimal('multiplier', 8, 4)->default(1);
            $table->integer('flat_addition_cents')->default(0);
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('priority')->default(100);
            $table->timestamps();
        });

        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->string('provider', 32)->default('manual');
            $table->string('method', 32)->default('dinheiro');
            $table->string('status', 20)->default('pending');
            $table->string('type', 20)->default('one_time');
            $table->integer('amount_cents')->default(0);
            $table->integer('fee_cents')->default(0);
            $table->integer('net_amount_cents')->nullable();
            $table->string('currency', 8)->default('BRL');
            $table->unsignedBigInteger('car_id')->nullable()->index();
            $table->unsignedBigInteger('monthly_subscriber_id')->nullable()->index();
            $table->unsignedBigInteger('parking_reservation_id')->nullable()->index();
            $table->unsignedBigInteger('monthly_billing_cycle_id')->nullable()->index();
            $table->string('external_id', 120)->nullable()->index();
            $table->text('payment_url')->nullable();
            $table->string('barcode', 180)->nullable();
            $table->string('digitable_line', 220)->nullable();
            $table->text('pix_payload')->nullable();
            $table->longText('gateway_payload')->nullable();
            $table->date('due_date')->nullable();
            $table->dateTime('paid_at')->nullable();
            $table->dateTime('reconciled_at')->nullable();
            $table->timestamps();
        });

        Schema::create('payment_webhooks', function (Blueprint $table) {
            $table->id();
            $table->string('provider', 32);
            $table->string('event_type', 80)->nullable();
            $table->string('external_id', 120)->nullable()->index();
            $table->longText('payload')->nullable();
            $table->string('signature', 255)->nullable();
            $table->string('status', 20)->default('pending');
            $table->dateTime('processed_at')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();
        });

        Schema::create('monthly_billing_cycles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('monthly_subscriber_id')->index();
            $table->string('reference')->unique();
            $table->string('competency', 7);
            $table->date('due_date');
            $table->integer('amount_cents')->default(0);
            $table->integer('fine_cents')->default(0);
            $table->integer('interest_cents')->default(0);
            $table->integer('total_amount_cents')->default(0);
            $table->string('status', 20)->default('pending');
            $table->unsignedBigInteger('payment_transaction_id')->nullable()->index();
            $table->dateTime('notified_at')->nullable();
            $table->dateTime('blocked_at')->nullable();
            $table->dateTime('paid_at')->nullable();
            $table->timestamps();
        });

        Schema::create('cash_shifts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('code')->unique();
            $table->dateTime('opened_at');
            $table->dateTime('closed_at')->nullable();
            $table->integer('opening_amount_cents')->default(0);
            $table->integer('expected_amount_cents')->default(0);
            $table->integer('counted_amount_cents')->nullable();
            $table->integer('difference_amount_cents')->default(0);
            $table->string('status', 20)->default('open');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('cash_shift_movements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cash_shift_id')->index();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('type', 20);
            $table->string('method', 32)->nullable();
            $table->integer('amount_cents')->default(0);
            $table->string('description', 255)->nullable();
            $table->dateTime('occurred_at');
            $table->timestamps();
        });

        Schema::create('fiscal_documents', function (Blueprint $table) {
            $table->id();
            $table->string('type', 20);
            $table->string('number', 40)->nullable();
            $table->string('series', 20)->nullable();
            $table->string('status', 20)->default('pending');
            $table->string('source_type', 120)->nullable();
            $table->unsignedBigInteger('source_id')->nullable()->index();
            $table->string('customer_name')->nullable();
            $table->string('customer_tax_id', 20)->nullable();
            $table->integer('total_cents')->default(0);
            $table->dateTime('issued_at')->nullable();
            $table->text('pdf_url')->nullable();
            $table->text('xml_url')->nullable();
            $table->string('access_key', 64)->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();
        });

        Schema::create('notification_logs', function (Blueprint $table) {
            $table->id();
            $table->string('channel', 20);
            $table->string('recipient', 255);
            $table->string('title', 255)->nullable();
            $table->text('message');
            $table->string('status', 20)->default('queued');
            $table->string('notifiable_type', 120)->nullable();
            $table->unsignedBigInteger('notifiable_id')->nullable()->index();
            $table->dateTime('scheduled_at')->nullable();
            $table->dateTime('sent_at')->nullable();
            $table->text('provider_response')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();
        });

        Schema::create('integration_endpoints', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type', 32);
            $table->string('base_url')->nullable();
            $table->string('auth_token', 255)->nullable();
            $table->string('auth_secret', 255)->nullable();
            $table->json('settings')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('last_health_status', 20)->nullable();
            $table->text('last_health_message')->nullable();
            $table->dateTime('last_checked_at')->nullable();
            $table->timestamps();
        });

        Schema::create('system_health_checks', function (Blueprint $table) {
            $table->id();
            $table->string('check_key')->unique();
            $table->string('status', 20);
            $table->string('message', 255)->nullable();
            $table->longText('details')->nullable();
            $table->dateTime('checked_at');
            $table->timestamps();
        });

        Schema::create('system_backups', function (Blueprint $table) {
            $table->id();
            $table->string('backup_type', 20)->default('app');
            $table->string('storage_disk', 50)->default('local');
            $table->string('path');
            $table->string('status', 20)->default('started');
            $table->unsignedBigInteger('size_bytes')->nullable();
            $table->dateTime('started_at');
            $table->dateTime('finished_at')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();
        });

        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (!Schema::hasColumn('users', 'role')) {
                    $table->string('role', 20)->default('admin')->after('password');
                }
                if (!Schema::hasColumn('users', 'is_active')) {
                    $table->boolean('is_active')->default(true)->after('role');
                }
            });
        }

        if (Schema::hasTable('monthly_subscribers')) {
            Schema::table('monthly_subscribers', function (Blueprint $table) {
                if (!Schema::hasColumn('monthly_subscribers', 'auto_renew_enabled')) {
                    $table->boolean('auto_renew_enabled')->default(true)->after('access_enabled');
                }
                if (!Schema::hasColumn('monthly_subscribers', 'recurring_payment_method')) {
                    $table->string('recurring_payment_method', 32)->nullable()->after('auto_renew_enabled');
                }
                if (!Schema::hasColumn('monthly_subscribers', 'delinquent_since')) {
                    $table->date('delinquent_since')->nullable()->after('recurring_payment_method');
                }
                if (!Schema::hasColumn('monthly_subscribers', 'blocked_at')) {
                    $table->dateTime('blocked_at')->nullable()->after('delinquent_since');
                }
                if (!Schema::hasColumn('monthly_subscribers', 'late_fee_percent')) {
                    $table->decimal('late_fee_percent', 5, 2)->default(2.00)->after('blocked_at');
                }
                if (!Schema::hasColumn('monthly_subscribers', 'daily_interest_percent')) {
                    $table->decimal('daily_interest_percent', 5, 3)->default(0.033)->after('late_fee_percent');
                }
            });
        }

        if (Schema::hasTable('cars')) {
            Schema::table('cars', function (Blueprint $table) {
                if (!Schema::hasColumn('cars', 'parking_sector_id')) {
                    $table->unsignedBigInteger('parking_sector_id')->nullable()->after('tipo_car')->index();
                }
                if (!Schema::hasColumn('cars', 'parking_spot_id')) {
                    $table->unsignedBigInteger('parking_spot_id')->nullable()->after('parking_sector_id')->index();
                }
                if (!Schema::hasColumn('cars', 'parking_reservation_id')) {
                    $table->unsignedBigInteger('parking_reservation_id')->nullable()->after('parking_spot_id')->index();
                }
                if (!Schema::hasColumn('cars', 'entry_source')) {
                    $table->string('entry_source', 20)->default('manual')->after('parking_reservation_id');
                }
                if (!Schema::hasColumn('cars', 'anpr_confidence')) {
                    $table->decimal('anpr_confidence', 5, 2)->nullable()->after('entry_source');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('cars')) {
            Schema::table('cars', function (Blueprint $table) {
                foreach (['parking_sector_id', 'parking_spot_id', 'parking_reservation_id', 'entry_source', 'anpr_confidence'] as $column) {
                    if (Schema::hasColumn('cars', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }

        if (Schema::hasTable('monthly_subscribers')) {
            Schema::table('monthly_subscribers', function (Blueprint $table) {
                foreach (['auto_renew_enabled', 'recurring_payment_method', 'delinquent_since', 'blocked_at', 'late_fee_percent', 'daily_interest_percent'] as $column) {
                    if (Schema::hasColumn('monthly_subscribers', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }

        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                foreach (['role', 'is_active'] as $column) {
                    if (Schema::hasColumn('users', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }

        Schema::dropIfExists('system_backups');
        Schema::dropIfExists('system_health_checks');
        Schema::dropIfExists('integration_endpoints');
        Schema::dropIfExists('notification_logs');
        Schema::dropIfExists('fiscal_documents');
        Schema::dropIfExists('cash_shift_movements');
        Schema::dropIfExists('cash_shifts');
        Schema::dropIfExists('monthly_billing_cycles');
        Schema::dropIfExists('payment_webhooks');
        Schema::dropIfExists('payment_transactions');
        Schema::dropIfExists('dynamic_pricing_rules');
        Schema::dropIfExists('parking_reservations');
        Schema::dropIfExists('parking_spots');
        Schema::dropIfExists('parking_sectors');
    }
};
