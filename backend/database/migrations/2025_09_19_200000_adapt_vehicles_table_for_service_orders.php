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
        Schema::table('vehicles', function (Blueprint $table) {
            // Tornar customer_id opcional (apenas para faturamento)
            $table->foreignId('customer_id')->nullable()->change();
            
            // Adicionar campos para histórico do cliente no momento
            $table->string('customer_name_at_time')->nullable()->after('customer_id');
            $table->string('customer_phone_at_time')->nullable()->after('customer_name_at_time');
            $table->string('customer_email_at_time')->nullable()->after('customer_phone_at_time');
            
            // Adicionar campo notes para compatibilidade com frontend (se não existir)
            if (!Schema::hasColumn('vehicles', 'notes')) {
                $table->text('notes')->nullable()->after('observations');
            }
            
            // Tornar plate única (se não for)
            $table->unique('plate');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn([
                'customer_name_at_time',
                'customer_phone_at_time', 
                'customer_email_at_time',
                'make',
                'notes'
            ]);
            
            $table->dropUnique(['plate']);
            $table->foreignId('customer_id')->nullable(false)->change();
        });
    }
};
