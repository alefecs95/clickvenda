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
            // Valor de compra do produto
            $table->decimal('purchase_price', 10, 2)->nullable()->after('price');
            
            // Margem de lucro em percentual
            $table->decimal('profit_margin', 5, 2)->nullable()->after('purchase_price');
            
            // Código do produto no XML (para facilitar importação)
            $table->string('xml_code')->nullable()->after('barcode');
            
            // Índices para performance
            $table->index('xml_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['xml_code']);
            $table->dropColumn(['purchase_price', 'profit_margin', 'xml_code']);
        });
    }
};