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
        Schema::create('financial_bank_institutions', function (Blueprint $table) {
            $table->id();
            // Código COMPE
            // Atribuído pelo Banco Central do Brasil (BACEN) e usado em transações financeiras, como TEDs, DOCs e PIX.
            $table->integer('code')->unique();
            // Nome
            $table->string('name');
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
        Schema::dropIfExists('financial_bank_institutions');
    }
};
