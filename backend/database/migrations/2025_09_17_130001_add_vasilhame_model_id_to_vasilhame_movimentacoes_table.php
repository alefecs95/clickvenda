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
            // Adicionar referência ao modelo de vasilhame
            $table->foreignId('vasilhame_model_id')->nullable()->after('produto_id')->constrained('vasilhame_models')->onDelete('set null');
            
            // Índice para performance
            $table->index('vasilhame_model_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vasilhame_movimentacoes', function (Blueprint $table) {
            $table->dropForeign(['vasilhame_model_id']);
            $table->dropIndex(['vasilhame_model_id']);
            $table->dropColumn('vasilhame_model_id');
        });
    }
};