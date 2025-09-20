<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('service_orders', function (Blueprint $table) {
            // 1. MANTER os_number (não renomear para order_number)
            // 2. Alterar payment_method de VARCHAR para ENUM (igual orders)
            $table->enum('payment_method', ['money', 'card', 'pix', 'credit', 'multiple'])
                  ->nullable()
                  ->default('money')
                  ->change();
            
            // 3. Adicionar payment_methods JSON (igual orders)
            $table->json('payment_methods')->nullable()->after('payment_method');
            
            // 4. Remover billing_type (redundante - payment_method já indica isso)
            $table->dropColumn('billing_type');
        });
        
        // 5. Atualizar dados existentes
        DB::table('service_orders')->update([
            'payment_method' => 'money' // Default para OS existentes
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_orders', function (Blueprint $table) {
            // Reverter mudanças financeiras
            $table->dropColumn(['payment_method', 'payment_methods']);
            $table->string('payment_method')->nullable()->after('final_amount');
            
            $table->enum('billing_type', ['avista', 'aprazo', 'orcamento'])
                  ->default('orcamento')
                  ->after('final_amount');
        });
    }
};