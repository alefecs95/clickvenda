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
        Schema::table('receivable_payments', function (Blueprint $table) {
            // Adicionar campo service_order_id para referenciar OS
            $table->unsignedBigInteger('service_order_id')->nullable()->after('order_id');
            
            // Adicionar índice e foreign key
            $table->index('service_order_id');
            $table->foreign('service_order_id')->references('id')->on('service_orders')->onDelete('cascade');
            
            // Tornar order_id nullable já que agora pode ser OS ou Order
            $table->unsignedBigInteger('order_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('receivable_payments', function (Blueprint $table) {
            // Remover foreign key e índice
            $table->dropForeign(['service_order_id']);
            $table->dropIndex(['service_order_id']);
            $table->dropColumn('service_order_id');
            
            // Tornar order_id obrigatório novamente
            $table->unsignedBigInteger('order_id')->nullable(false)->change();
        });
    }
};