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
        Schema::table('payable_payments', function (Blueprint $table) {
            // Remove a constraint existente com customers (nome gerado automaticamente)
            $table->dropForeign('payable_payments_supplier_id_foreign');
            
            // Adiciona nova constraint com suppliers
            $table->foreign('supplier_id')->references('id')->on('suppliers')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payable_payments', function (Blueprint $table) {
            // Remove a constraint com suppliers
            $table->dropForeign('payable_payments_supplier_id_foreign');
            
            // Restaura a constraint com customers
            $table->foreign('supplier_id')->references('id')->on('customers')->onDelete('cascade');
        });
    }
};
