<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('accounting_entries', function (Blueprint $table) {
            $table->id();
            $table->string('type', 20);
            $table->string('category', 120);
            $table->string('description')->nullable();
            $table->decimal('amount', 12, 2);
            $table->date('occurred_at');
            $table->string('payment_method', 40)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['type', 'occurred_at']);
            $table->index('occurred_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('accounting_entries');
    }
};

