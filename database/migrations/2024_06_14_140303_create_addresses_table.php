<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            // addressable_id e addressable_type.
            $table->morphs('addressable');
            // Nome de identificação do endereço
            // 1 - 'Casa', 2 - 'Trabalho', 3 - 'Outros'...
            $table->string('name')->nullable();
            // Principal 1 - sim, 0 - não
            $table->boolean('is_main')->default(0);
            // Cep
            $table->string('zipcode')->nullable();
            // Estado
            $table->string('state')->nullable();
            // Uf/Estado
            $table->char('uf', 2)->nullable();
            // Cidade
            $table->string('city')->nullable();
            // País
            $table->string('country')->nullable();
            // Bairro
            $table->string('district')->nullable();
            // Endereço completo (Rua, Quadra, Lote...)
            $table->string('address_line')->nullable();
            // Número
            $table->string('number')->nullable();
            // Complemento
            $table->string('complement')->nullable();
            // Rua/Logradouro
            $table->string('custom_street')->nullable();
            // Quadra
            $table->string('custom_block')->nullable();
            // Lote
            $table->string('custom_lot')->nullable();
            // Ponto de referência
            $table->string('reference')->nullable();
            // Coordenadas
            $table->string('gmap_coordinates')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('addresses');
    }
};
