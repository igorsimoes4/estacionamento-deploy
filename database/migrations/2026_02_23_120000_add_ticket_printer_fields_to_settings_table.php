<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('settings')) {
            return;
        }

        $columns = [
            'ticket_print_enabled' => fn (Blueprint $table) => $table->boolean('ticket_print_enabled')->default(false)->after('boleto_due_days'),
            'ticket_printer_driver' => fn (Blueprint $table) => $table->string('ticket_printer_driver', 20)->default('windows')->after('ticket_print_enabled'),
            'ticket_printer_target' => fn (Blueprint $table) => $table->string('ticket_printer_target')->nullable()->after('ticket_printer_driver'),
            'ticket_printer_port' => fn (Blueprint $table) => $table->unsignedInteger('ticket_printer_port')->nullable()->after('ticket_printer_target'),
            'ticket_printer_timeout' => fn (Blueprint $table) => $table->unsignedInteger('ticket_printer_timeout')->default(10)->after('ticket_printer_port'),
            'ticket_print_copies' => fn (Blueprint $table) => $table->unsignedInteger('ticket_print_copies')->default(1)->after('ticket_printer_timeout'),
            'ticket_line_width' => fn (Blueprint $table) => $table->unsignedInteger('ticket_line_width')->default(42)->after('ticket_print_copies'),
        ];

        foreach ($columns as $column => $definition) {
            if (!Schema::hasColumn('settings', $column)) {
                Schema::table('settings', $definition);
            }
        }
    }

    public function down(): void
    {
        // Migration de compatibilidade: sem rollback destrutivo.
    }
};

