<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('monthly_subscribers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('cpf')->unique();
            $table->string('phone');
            $table->string('email')->nullable();
            $table->string('vehicle_plate')->unique();
            $table->string('vehicle_model')->nullable();
            $table->string('vehicle_color')->nullable();
            $table->enum('vehicle_type', ['carro', 'moto', 'caminhao']);
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('monthly_fee', 10, 2);
            $table->boolean('is_active')->default(true);
            $table->text('observations')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('monthly_subscribers');
    }
}; 