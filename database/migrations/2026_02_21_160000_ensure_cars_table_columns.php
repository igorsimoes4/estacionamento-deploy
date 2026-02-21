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

        if (!Schema::hasColumn('cars', 'entrada')) {
            Schema::table('cars', function (Blueprint $table) {
                $table->time('entrada')->nullable()->after('placa');
            });
        }

        if (!Schema::hasColumn('cars', 'preco')) {
            Schema::table('cars', function (Blueprint $table) {
                $table->decimal('preco', 10, 2)->default(0)->after('entrada');
            });
        }

        if (!Schema::hasColumn('cars', 'tipo_car')) {
            Schema::table('cars', function (Blueprint $table) {
                $table->string('tipo_car')->default('carro')->after('preco');
            });
        }

        if (!Schema::hasColumn('cars', 'status')) {
            Schema::table('cars', function (Blueprint $table) {
                $table->string('status')->nullable()->after('tipo_car');
            });
        }

        if (!Schema::hasColumn('cars', 'saida')) {
            Schema::table('cars', function (Blueprint $table) {
                $table->timestamp('saida')->nullable()->after('status');
            });
        }
    }

    public function down()
    {
        // Migration de compatibilidade: sem rollback destrutivo.
    }
};
