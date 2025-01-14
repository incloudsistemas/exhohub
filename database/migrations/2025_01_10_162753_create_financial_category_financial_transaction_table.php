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
        Schema::create('financial_category_financial_transaction', function (Blueprint $table) {
            $table->foreignId('category_id');
            $table->foreign('category_id')
                ->references('id')
                ->on('financial_categories')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->foreignId('transaction_id');
            $table->foreign('transaction_id')
                ->references('id')
                ->on('financial_transactions')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            // Não permite categorias repetidas por transação.
            $table->unique(['category_id', 'transaction_id'], 'transaction_category_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('financial_category_financial_transaction');
    }
};
