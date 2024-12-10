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
        Schema::create('ticket_ticket_category', function (Blueprint $table) {
            // Chamado
            $table->foreignId('ticket_id');
            $table->foreign('ticket_id')
                ->references('id')
                ->on('tickets')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            // Categoria
            $table->foreignId('category_id');
            $table->foreign('category_id')
                ->references('id')
                ->on('ticket_categories')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            // Não permite categorias repetidas por tíquete.
            $table->unique(['ticket_id', 'category_id'], 'ticket_category_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('category_ticket');
    }
};
