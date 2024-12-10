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
        Schema::create('creci_control_stages', function (Blueprint $table) {
            $table->id();
            // 1 - 'Trainee', 2 - 'Estagiário', 3 - 'Corretor'.
            $table->char('role', 1);
            // Etapa
            $table->string('name');
            $table->string('slug')->unique();
            // Complemento
            $table->text('complement')->nullable();
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
        Schema::dropIfExists('creci_control_stages');
    }
};
