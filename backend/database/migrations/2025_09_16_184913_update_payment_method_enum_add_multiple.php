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
        // Alterar o enum payment_method para incluir 'multiple'
        DB::statement("ALTER TABLE orders MODIFY COLUMN payment_method ENUM('money', 'card', 'pix', 'credit', 'multiple') DEFAULT 'money'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverter para o enum sem 'multiple'
        DB::statement("ALTER TABLE orders MODIFY COLUMN payment_method ENUM('money', 'card', 'pix', 'credit') DEFAULT 'money'");
    }
};