<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE monthly_subscribers MODIFY vehicle_type ENUM('carro','moto','caminhonete','caminhao') NOT NULL");
            DB::statement("UPDATE monthly_subscribers SET vehicle_type = 'caminhonete' WHERE vehicle_type = 'caminhao'");
            return;
        }

        if ($driver === 'sqlite') {
            DB::statement("UPDATE monthly_subscribers SET vehicle_type = 'caminhonete' WHERE vehicle_type = 'caminhao'");
        }
    }

    public function down()
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE monthly_subscribers MODIFY vehicle_type ENUM('carro','moto','caminhao') NOT NULL");
        }
    }
};
