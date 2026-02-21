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

        if (!Schema::hasColumn('cars', 'payment_method')) {
            Schema::table('cars', function (Blueprint $table) {
                $table->string('payment_method', 40)->nullable()->after('saida');
            });
        }

        if (!Schema::hasColumn('cars', 'payment_reference')) {
            Schema::table('cars', function (Blueprint $table) {
                $table->string('payment_reference', 120)->nullable()->after('payment_method');
            });
        }

        if (!Schema::hasColumn('cars', 'paid_at')) {
            Schema::table('cars', function (Blueprint $table) {
                $table->timestamp('paid_at')->nullable()->after('payment_reference');
            });
        }
    }

    public function down()
    {
        // Migration de compatibilidade: sem rollback destrutivo.
    }
};

