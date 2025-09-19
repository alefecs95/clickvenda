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
        Schema::create('vasilhame_models', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('returnable_price', 10, 2);
            $table->integer('returnable_quantity')->default(1);
            $table->integer('stock_quantity')->default(0);
            $table->integer('minimum_stock')->default(0);
            $table->boolean('available')->default(true);
            $table->timestamps();
            
            // Índices para performance
            $table->index('name');
            $table->index('available');
        });
        
        // Adicionar campo de vínculo na tabela products
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('vasilhame_model_id')->nullable()->constrained('vasilhame_models')->onDelete('set null');
            $table->index('vasilhame_model_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['vasilhame_model_id']);
            $table->dropColumn('vasilhame_model_id');
        });
        
        Schema::dropIfExists('vasilhame_models');
    }
};