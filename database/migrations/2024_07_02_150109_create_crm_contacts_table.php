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
        Schema::create('crm_contacts', function (Blueprint $table) {
            $table->id();
            // contactable_id e contactable_type
            $table->morphs('contactable');
            // Criador/Captador "id_owner"
            $table->foreignId('user_id')->nullable()->default(null);
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onUpdate('cascade')
                ->onDelete('set null');
            // Origem do contato
            $table->foreignId('source_id')->nullable()->default(null);
            $table->foreign('source_id')
                ->references('id')
                ->on('crm_sources')
                ->onUpdate('cascade')
                ->onDelete('set null');
            // Nome
            $table->string('name');
            // $table->string('slug')->unique();
            // Email
            $table->string('email')->nullable();
            // Email(s) adicionais
            $table->json('additional_emails')->nullable();
            // Telefone(s) de contato
            $table->json('phones')->nullable();
            // Complemento
            $table->text('complement')->nullable();
            // Status
            // 0- Inativo, 1 - Ativo
            $table->char('status', 1)->default(1);
            // Atributos personalizados
            $table->json('custom')->nullable();
            $table->timestamps();
            $table->softDeletes();
            // Email único por captador e tipo do contato.
            $table->unique(['email', 'user_id', 'contactable_type'], 'email_user_contactable_unique');
            // Permite apenas um contato por registro.
            $table->unique(['contactable_id', 'contactable_type'], 'contactable_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('crm_contacts');
    }
};
