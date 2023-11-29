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
        Schema::table('settings', function (Blueprint $table) {
            $table->string('nome_da_empresa')->nullable();
            $table->string('endereco')->nullable();
            $table->string('cidade')->nullable();
            $table->string('estado')->nullable();
            $table->string('cep')->nullable();
            $table->string('telefone_da_empresa')->nullable();
            $table->string('email_da_empresa')->nullable();
            $table->string('numero_de_registro_da_empresa')->nullable();
            $table->string('cnpj_cpf_da_empresa')->nullable();
            $table->text('descricao_da_empresa')->nullable();
            $table->string('coordenadas_gps')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn('nome_da_empresa');
            $table->dropColumn('endereco');
            $table->dropColumn('cidade');
            $table->dropColumn('estado');
            $table->dropColumn('cep');
            $table->dropColumn('telefone_da_empresa');
            $table->dropColumn('email_da_empresa');
            $table->dropColumn('numero_de_registro_da_empresa');
            $table->dropColumn('cnpj_cpf_da_empresa');
            $table->dropColumn('descricao_da_empresa');
            $table->dropColumn('coordenadas_gps');
        });
    }
};
