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

        if (!Schema::hasColumn('settings', 'pix_key')) {
            Schema::table('settings', function (Blueprint $table) {
                $table->string('pix_key')->nullable()->after('coordenadas_gps');
            });
        }

        if (!Schema::hasColumn('settings', 'pix_beneficiary_name')) {
            Schema::table('settings', function (Blueprint $table) {
                $table->string('pix_beneficiary_name')->nullable()->after('pix_key');
            });
        }

        if (!Schema::hasColumn('settings', 'pix_city')) {
            Schema::table('settings', function (Blueprint $table) {
                $table->string('pix_city')->nullable()->after('pix_beneficiary_name');
            });
        }

        if (!Schema::hasColumn('settings', 'pix_description')) {
            Schema::table('settings', function (Blueprint $table) {
                $table->string('pix_description')->nullable()->after('pix_city');
            });
        }

        if (!Schema::hasColumn('settings', 'card_machine_instructions')) {
            Schema::table('settings', function (Blueprint $table) {
                $table->text('card_machine_instructions')->nullable()->after('pix_description');
            });
        }
    }

    public function down()
    {
        // Migration de compatibilidade: sem rollback destrutivo.
    }
};

