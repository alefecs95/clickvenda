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
        Schema::create('vasilhame_movimentacoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('customers')->onDelete('cascade');
            $table->foreignId('produto_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('pedido_id')->nullable()->constrained('orders')->onDelete('set null');
            $table->foreignId('usuario_id')->constrained('users')->onDelete('cascade');
            $table->enum('tipo', ['entrada', 'saida']);
            $table->integer('quantidade');
            $table->decimal('valor_unitario', 10, 2);
            $table->text('observacoes')->nullable();
            $table->timestamp('data_movimentacao');
            $table->timestamps();
            
            // Índices para performance
            $table->index(['cliente_id', 'tipo']);
            $table->index(['data_movimentacao', 'cliente_id']);
            $table->index('pedido_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vasilhame_movimentacoes');
    }
};