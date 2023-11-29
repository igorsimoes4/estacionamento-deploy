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
        Schema::create('price_cars', function (Blueprint $table) {
            $table->id();
            $table->decimal('valorHora', 8, 2)->default(5.00);
            $table->decimal('valorMinimo', 8, 2)->default(10.00);
            $table->decimal('valorDiaria', 8, 2)->default(50.00);
            $table->decimal('taxaAdicional', 8, 2)->default(17.00);
            $table->decimal('taxaMensal', 8, 2)->default(400.00);
            $table->timestamp('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('price_cars');
    }
};
