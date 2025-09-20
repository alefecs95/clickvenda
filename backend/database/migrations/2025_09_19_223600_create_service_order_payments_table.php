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
        Schema::create('service_order_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_order_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 10, 2);
            $table->enum('payment_method', [
                'dinheiro',
                'cartao_debito', 
                'cartao_credito',
                'pix',
                'transferencia',
                'aprazo'
            ]);
            $table->string('payment_reference')->nullable(); // Número do cartão, PIX, transferência, etc.
            $table->text('notes')->nullable();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Quem registrou o pagamento
            $table->timestamp('paid_at');
            $table->timestamps();
            
            $table->index(['service_order_id', 'payment_method']);
            $table->index(['paid_at', 'payment_method']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_order_payments');
    }
};