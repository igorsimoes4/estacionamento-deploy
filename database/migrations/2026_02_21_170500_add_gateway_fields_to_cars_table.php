<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('cars')) {
            return;
        }

        if (!Schema::hasColumn('cars', 'payment_provider')) {
            Schema::table('cars', function (Blueprint $table) {
                $table->string('payment_provider', 40)->nullable()->after('payment_method');
            });
        }

        if (!Schema::hasColumn('cars', 'payment_status')) {
            Schema::table('cars', function (Blueprint $table) {
                $table->string('payment_status', 30)->nullable()->after('payment_provider');
            });
        }

        if (!Schema::hasColumn('cars', 'external_payment_id')) {
            Schema::table('cars', function (Blueprint $table) {
                $table->string('external_payment_id', 120)->nullable()->after('payment_status');
            });
        }

        if (!Schema::hasColumn('cars', 'payment_url')) {
            Schema::table('cars', function (Blueprint $table) {
                $table->text('payment_url')->nullable()->after('external_payment_id');
            });
        }
    }

    public function down()
    {
        // Migration de compatibilidade: sem rollback destrutivo.
    }
};

