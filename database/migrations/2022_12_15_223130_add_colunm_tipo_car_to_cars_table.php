<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('cars', 'tipo_car')) {
            Schema::table('cars', function (Blueprint $table) {
                $table->string('tipo_car')->default('carro')->after('preco');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('cars', 'tipo_car')) {
            Schema::table('cars', function (Blueprint $table) {
                $table->dropColumn('tipo_car');
            });
        }
    }
};
