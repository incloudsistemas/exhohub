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
        Schema::create('real_estate_property_types', function (Blueprint $table) {
            $table->id();
            // Tipo para imóveis
            // 1 - Residencial, 2 - Comercial, 3 - Residencial e Comercial
            $table->char('usage', 1)->default(1);
            // Nome
            $table->string('name');
            $table->string('slug')->unique();
            // Código / Abreviatura
            $table->string('abbr')->unique()->nullable();
            // Elemento VRSync para integração com canal pro
            $table->string('canal_pro_vrsync')->nullable();
            // Status
            // 0- Inativo, 1 - Ativo
            $table->char('status', 1)->default(1);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('real_estate_property_types');
    }
};
