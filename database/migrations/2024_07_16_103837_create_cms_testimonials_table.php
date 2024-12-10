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
        Schema::create('cms_testimonials', function (Blueprint $table) {
            $table->id();
            // Tipo
            // 1 - Texto, 2 - Imagem, 3 - Vídeo
            $table->char('role', 1)->default(1);
            // Nome do cliente
            // (nos depoimentos o título sempre será o nome do cliente que deu o depoimento)
            $table->string('customer_name');
            // Cargo
            $table->string('occupation')->nullable();
            // Empresa
            $table->string('company')->nullable();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cms_testimonials');
    }
};
