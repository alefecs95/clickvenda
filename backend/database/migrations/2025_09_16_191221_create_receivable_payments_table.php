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
        Schema::create('receivable_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->decimal('total_receivable_amount', 10, 2); // Valor total a receber (apenas crédito)
            $table->decimal('paid_amount', 10, 2)->default(0); // Valor já pago
            $table->decimal('remaining_amount', 10, 2); // Valor restante
            $table->date('due_date'); // Data de vencimento
            $table->enum('status', ['pending', 'partial', 'paid', 'overdue'])->default('pending');
            $table->json('payment_history')->nullable(); // Histórico de pagamentos parciais
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Índices para performance
            $table->index(['customer_id', 'status']);
            $table->index(['due_date', 'status']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('receivable_payments');
    }
};