<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->string('event', 120)->index();
            $table->string('level', 20)->default('info')->index();
            $table->text('description')->nullable();

            $table->string('actor_type', 180)->nullable()->index();
            $table->unsignedBigInteger('actor_id')->nullable()->index();

            $table->string('request_method', 10)->nullable()->index();
            $table->string('request_path', 255)->nullable()->index();
            $table->string('route_name', 180)->nullable()->index();
            $table->text('url')->nullable();
            $table->unsignedSmallInteger('status_code')->nullable()->index();
            $table->string('ip_address', 45)->nullable()->index();
            $table->string('user_agent', 1024)->nullable();

            $table->string('subject_type', 180)->nullable()->index();
            $table->string('subject_id', 80)->nullable()->index();

            $table->longText('old_values')->nullable();
            $table->longText('new_values')->nullable();
            $table->longText('metadata')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};

