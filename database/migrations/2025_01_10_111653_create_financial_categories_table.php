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
        Schema::create('financial_categories', function (Blueprint $table) {
            $table->id();
            // Auto relacionamento - Sub categoria - Ref. categoria parental/pai
            $table->foreignId('category_id')->nullable()->default(null);
            $table->foreign('category_id')
                ->references('id')
                ->on('financial_categories')
                ->onUpdate('cascade')
                ->onDelete('set null');
            // Tipo
            // 1 - Categorias de receita, 2 - Categorias de despesa
            $table->char('role', 1);
            // Nome
            $table->string('name');
            $table->string('slug')->unique();
            // Ordem
            $table->integer('order')->unsigned()->default(1);
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
        Schema::dropIfExists('financial_categories');
    }
};
