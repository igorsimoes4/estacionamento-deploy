<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('monthly_subscribers', function (Blueprint $table) {
            if (!Schema::hasColumn('monthly_subscribers', 'boleto_reference')) {
                $table->string('boleto_reference', 80)->nullable()->after('access_last_login_at');
            }

            if (!Schema::hasColumn('monthly_subscribers', 'boleto_provider')) {
                $table->string('boleto_provider', 40)->nullable()->after('boleto_reference');
            }

            if (!Schema::hasColumn('monthly_subscribers', 'boleto_url')) {
                $table->text('boleto_url')->nullable()->after('boleto_provider');
            }

            if (!Schema::hasColumn('monthly_subscribers', 'boleto_barcode')) {
                $table->string('boleto_barcode', 255)->nullable()->after('boleto_url');
            }

            if (!Schema::hasColumn('monthly_subscribers', 'boleto_digitable_line')) {
                $table->string('boleto_digitable_line', 255)->nullable()->after('boleto_barcode');
            }

            if (!Schema::hasColumn('monthly_subscribers', 'boleto_due_date')) {
                $table->date('boleto_due_date')->nullable()->after('boleto_digitable_line');
            }

            if (!Schema::hasColumn('monthly_subscribers', 'boleto_amount_cents')) {
                $table->unsignedBigInteger('boleto_amount_cents')->nullable()->after('boleto_due_date');
            }

            if (!Schema::hasColumn('monthly_subscribers', 'boleto_status')) {
                $table->string('boleto_status', 30)->nullable()->after('boleto_amount_cents');
            }

            if (!Schema::hasColumn('monthly_subscribers', 'boleto_generated_at')) {
                $table->timestamp('boleto_generated_at')->nullable()->after('boleto_status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('monthly_subscribers', function (Blueprint $table) {
            $columns = [];

            foreach ([
                'boleto_generated_at',
                'boleto_status',
                'boleto_amount_cents',
                'boleto_due_date',
                'boleto_digitable_line',
                'boleto_barcode',
                'boleto_url',
                'boleto_provider',
                'boleto_reference',
            ] as $column) {
                if (Schema::hasColumn('monthly_subscribers', $column)) {
                    $columns[] = $column;
                }
            }

            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};

