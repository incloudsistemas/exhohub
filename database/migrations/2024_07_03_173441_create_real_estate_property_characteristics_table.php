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
        Schema::create('real_estate_property_characteristics', function (Blueprint $table) {
            $table->id();
            // Categoria
            // 1 - 'Lazer', 2 - 'Segurança', 3 - 'Infraestrutura', 4 - 'Pisos', 5 - 'Armários'...
            $table->char('role', 1);
            // Nome
            $table->string('name');
            $table->string('slug')->unique();
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
        Schema::dropIfExists('real_estate_property_characteristics');
    }
};
