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
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('cpf_cnpj')->unique()->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('zip_code')->nullable();
            $table->string('contact_person')->nullable(); // Pessoa de contato
            $table->string('bank_name')->nullable(); // Banco
            $table->string('bank_agency')->nullable(); // Agência
            $table->string('bank_account')->nullable(); // Conta
            $table->string('pix_key')->nullable(); // Chave PIX
            $table->text('notes')->nullable(); // Observações
            $table->boolean('active')->default(true);
            $table->timestamps();
            
            // Índices
            $table->index('name');
            $table->index('cpf_cnpj');
            $table->index('active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('suppliers');
    }
};
