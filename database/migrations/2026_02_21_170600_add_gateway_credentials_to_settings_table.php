<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('settings')) {
            return;
        }

        $columns = [
            'payment_provider_default' => fn (Blueprint $table) => $table->string('payment_provider_default', 40)->nullable()->after('card_machine_instructions'),
            'payment_environment' => fn (Blueprint $table) => $table->string('payment_environment', 20)->nullable()->after('payment_provider_default'),
            'pagbank_token' => fn (Blueprint $table) => $table->string('pagbank_token')->nullable()->after('payment_environment'),
            'pagbank_api_base_url' => fn (Blueprint $table) => $table->string('pagbank_api_base_url')->nullable()->after('pagbank_token'),
            'cielo_merchant_id' => fn (Blueprint $table) => $table->string('cielo_merchant_id')->nullable()->after('pagbank_api_base_url'),
            'cielo_merchant_key' => fn (Blueprint $table) => $table->string('cielo_merchant_key')->nullable()->after('cielo_merchant_id'),
            'cielo_api_base_url' => fn (Blueprint $table) => $table->string('cielo_api_base_url')->nullable()->after('cielo_merchant_key'),
            'stone_api_token' => fn (Blueprint $table) => $table->string('stone_api_token')->nullable()->after('cielo_api_base_url'),
            'stone_api_base_url' => fn (Blueprint $table) => $table->string('stone_api_base_url')->nullable()->after('stone_api_token'),
            'rede_api_token' => fn (Blueprint $table) => $table->string('rede_api_token')->nullable()->after('stone_api_base_url'),
            'rede_api_base_url' => fn (Blueprint $table) => $table->string('rede_api_base_url')->nullable()->after('rede_api_token'),
            'getnet_client_id' => fn (Blueprint $table) => $table->string('getnet_client_id')->nullable()->after('rede_api_base_url'),
            'getnet_client_secret' => fn (Blueprint $table) => $table->string('getnet_client_secret')->nullable()->after('getnet_client_id'),
            'getnet_seller_id' => fn (Blueprint $table) => $table->string('getnet_seller_id')->nullable()->after('getnet_client_secret'),
            'getnet_api_base_url' => fn (Blueprint $table) => $table->string('getnet_api_base_url')->nullable()->after('getnet_seller_id'),
            'boleto_due_days' => fn (Blueprint $table) => $table->unsignedInteger('boleto_due_days')->nullable()->after('getnet_api_base_url'),
        ];

        foreach ($columns as $column => $definition) {
            if (!Schema::hasColumn('settings', $column)) {
                Schema::table('settings', $definition);
            }
        }
    }

    public function down()
    {
        // Migration de compatibilidade: sem rollback destrutivo.
    }
};

