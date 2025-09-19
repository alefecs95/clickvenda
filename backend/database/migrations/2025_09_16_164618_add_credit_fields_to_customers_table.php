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
        Schema::table('customers', function (Blueprint $table) {
            // Limite de crédito aprovado para o cliente
            $table->decimal('credit_limit', 10, 2)->default(0.00)->after('active');
            
            // Crédito atualmente utilizado (saldo devedor)
            $table->decimal('credit_used', 10, 2)->default(0.00)->after('credit_limit');
            
            // Data da última atualização do limite
            $table->timestamp('credit_limit_updated_at')->nullable()->after('credit_used');
            
            // Observações sobre o crédito
            $table->text('credit_notes')->nullable()->after('credit_limit_updated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn([
                'credit_limit', 
                'credit_used', 
                'credit_limit_updated_at', 
                'credit_notes'
            ]);
        });
    }
};