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
        Schema::create('pendencia_vasilhames', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('customers')->onDelete('cascade');
            $table->foreignId('vasilhame_model_id')->constrained('vasilhame_models')->onDelete('cascade');
            $table->foreignId('venda_id')->nullable()->constrained('orders')->onDelete('set null');
            $table->integer('quantidade_pendente');
            $table->integer('quantidade_necessaria');
            $table->integer('quantidade_entregue');
            $table->text('observacoes')->nullable();
            $table->boolean('resolvida')->default(false);
            $table->timestamp('data_resolucao')->nullable();
            $table->timestamps();
            
            // Índices para performance
            $table->index(['cliente_id', 'resolvida']);
            $table->index('vasilhame_model_id');
            $table->index('venda_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pendencia_vasilhames');
    }
};