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
        Schema::table('vasilhame_movimentacoes', function (Blueprint $table) {
            // Primeiro, remover a constraint de foreign key
            $table->dropForeign(['produto_id']);
            
            // Tornar a coluna nullable
            $table->foreignId('produto_id')->nullable()->change();
            
            // Recriar a foreign key constraint com nullable
            $table->foreign('produto_id')->references('id')->on('products')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vasilhame_movimentacoes', function (Blueprint $table) {
            // Remover a constraint nullable
            $table->dropForeign(['produto_id']);
            
            // Tornar a coluna obrigatória novamente
            $table->foreignId('produto_id')->nullable(false)->change();
            
            // Recriar a foreign key constraint sem nullable
            $table->foreign('produto_id')->references('id')->on('products')->onDelete('cascade');
        });
    }
};
