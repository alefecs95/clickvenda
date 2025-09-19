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
            // Campo para controlar se o produto está disponível para venda
            $table->boolean('available')->default(true)->after('active');
            
            // Campo para indicar se o produto é retornável (vassilhame)
            $table->boolean('returnable')->default(false)->after('available');
            
            // Preço do vassilhame (cobrado separadamente)
            $table->decimal('returnable_price', 10, 2)->nullable()->after('returnable');
            
            // Quantidade de vassilhames por unidade do produto
            $table->integer('returnable_quantity')->default(1)->after('returnable_price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['available', 'returnable', 'returnable_price', 'returnable_quantity']);
        });
    }
};