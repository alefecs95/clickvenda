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
        // Primeiro alterar o ENUM para incluir ambos os valores
        DB::statement("ALTER TABLE service_order_payments MODIFY COLUMN payment_method ENUM('dinheiro','cartao_debito','cartao_credito','pix','transferencia','aprazo','money','card','credit') NOT NULL");
        
        // Depois atualizar os dados existentes para os novos valores
        DB::table('service_order_payments')->where('payment_method', 'dinheiro')->update(['payment_method' => 'money']);
        DB::table('service_order_payments')->where('payment_method', 'cartao_debito')->update(['payment_method' => 'card']);
        DB::table('service_order_payments')->where('payment_method', 'cartao_credito')->update(['payment_method' => 'card']);
        DB::table('service_order_payments')->where('payment_method', 'pix')->update(['payment_method' => 'pix']);
        DB::table('service_order_payments')->where('payment_method', 'transferencia')->update(['payment_method' => 'credit']);
        DB::table('service_order_payments')->where('payment_method', 'aprazo')->update(['payment_method' => 'credit']);
        
        // Por fim, alterar o ENUM para apenas os novos valores
        DB::statement("ALTER TABLE service_order_payments MODIFY COLUMN payment_method ENUM('money', 'card', 'pix', 'credit') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Alterar o ENUM para os valores antigos
        DB::statement("ALTER TABLE service_order_payments MODIFY COLUMN payment_method ENUM('dinheiro', 'cartao_debito', 'cartao_credito', 'pix', 'transferencia', 'aprazo') NOT NULL");
        
        // Reverter dados para os valores antigos
        DB::table('service_order_payments')->where('payment_method', 'money')->update(['payment_method' => 'dinheiro']);
        DB::table('service_order_payments')->where('payment_method', 'card')->update(['payment_method' => 'cartao_debito']);
        DB::table('service_order_payments')->where('payment_method', 'pix')->update(['payment_method' => 'pix']);
        DB::table('service_order_payments')->where('payment_method', 'credit')->update(['payment_method' => 'aprazo']);
    }
};