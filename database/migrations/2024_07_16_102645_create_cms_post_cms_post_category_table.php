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
        Schema::create('cms_post_cms_post_category', function (Blueprint $table) {
            // Postagem
            $table->foreignId('post_id');
            $table->foreign('post_id')
                ->references('id')
                ->on('cms_posts')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            // Categoria
            $table->foreignId('category_id');
            $table->foreign('category_id')
                ->references('id')
                ->on('cms_post_categories')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            // Não permite categorias repetidas por postagem.
            $table->unique(['post_id', 'category_id'], 'post_category_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('cms_post_cms_post_category');
    }
};
