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
        Schema::create('support_department_ticket_category', function (Blueprint $table) {
            // Chamado
            $table->foreignId('department_id');
            $table->foreign('department_id')
                ->references('id')
                ->on('support_departments')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            // Categoria
            $table->foreignId('category_id');
            $table->foreign('category_id')
                ->references('id')
                ->on('ticket_categories')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            // Não permite categorias repetidas por departamento.
            $table->unique(['department_id', 'category_id'], 'department_category_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('support_department_ticket_category');
    }
};
