<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('monthly_subscribers', function (Blueprint $table) {
            if (!Schema::hasColumn('monthly_subscribers', 'access_enabled')) {
                $table->boolean('access_enabled')->default(true)->after('is_active');
            }

            if (!Schema::hasColumn('monthly_subscribers', 'access_password')) {
                $table->string('access_password')->nullable()->after('access_enabled');
            }

            if (!Schema::hasColumn('monthly_subscribers', 'access_last_login_at')) {
                $table->timestamp('access_last_login_at')->nullable()->after('access_password');
            }
        });
    }

    public function down(): void
    {
        Schema::table('monthly_subscribers', function (Blueprint $table) {
            $columns = [];

            if (Schema::hasColumn('monthly_subscribers', 'access_last_login_at')) {
                $columns[] = 'access_last_login_at';
            }

            if (Schema::hasColumn('monthly_subscribers', 'access_password')) {
                $columns[] = 'access_password';
            }

            if (Schema::hasColumn('monthly_subscribers', 'access_enabled')) {
                $columns[] = 'access_enabled';
            }

            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};

