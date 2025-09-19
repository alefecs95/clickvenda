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
        Schema::table('products', function (Blueprint $table) {
            // ID do produto pai que gerencia o estoque
            $table->foreignId('parent_product_id')->nullable()->after('returnable_quantity')
                  ->constrained('products')->onDelete('set null');
            
            // Multiplicador de estoque (quantas unidades do pai este produto representa)
            $table->integer('stock_multiplier')->default(1)->after('parent_product_id');
            
            // Indica se é um produto que gerencia estoque (produto pai)
            $table->boolean('manages_stock')->default(true)->after('stock_multiplier');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['parent_product_id']);
            $table->dropColumn(['parent_product_id', 'stock_multiplier', 'manages_stock']);
        });
    }
};