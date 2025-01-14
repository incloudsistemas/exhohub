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
        Schema::create('financial_bank_accounts', function (Blueprint $table) {
            $table->id();
            // Agência
            $table->foreignId('agency_id')->nullable()->default(null);
            $table->foreign('agency_id')
                ->references('id')
                ->on('agencies')
                ->onUpdate('cascade')
                ->onDelete('set null');
            // Instituição bancária
            $table->foreignId('bank_institution_id');
            $table->foreign('bank_institution_id')
                ->references('id')
                ->on('financial_bank_institutions')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            // Tipo de conta
            //  1 - Conta Corrente, 2 - Poupança, 3 - Cartão de Crédito, 4 - Carteira, 5 - Caixa, 6 - Investimentos, 7 - Outro...
            $table->char('role', 1)->default(1);
            // Modalidade
            //  1 - Conta Pessoa Jurídica (PJ), 2 - Conta Pessoa Física (PF)
            $table->char('type_person', 1);
            // Nome da conta
            $table->string('name');
            // Quero que esta seja minha conta padrão
            // Principal 1 - sim, 0 - não
            $table->boolean('is_main')->default(0);
            // Início dos lançamentos
            // Informe uma data até now()
            $table->timestamp('balance_date')->default(now());
            // Saldo
            // Valor existente na sua conta antes do primeiro lançamento no app financeiro. Cadastrá-lo ajuda a manter o saldo entre banco e nosso app financeiro correto.
            $table->bigInteger('balance')->default(0);
            // Descrição
            $table->text('description')->nullable();
            // Status
            // 0 - Inativo, 1 - Ativo
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
        Schema::dropIfExists('financial_bank_accounts');
    }
};
