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
        Schema::create('payable_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->nullable()->constrained('customers')->onDelete('cascade');
            $table->foreignId('purchase_order_id')->nullable()->constrained('orders')->onDelete('cascade');
            $table->foreignId('service_order_id')->nullable()->constrained('service_orders')->onDelete('cascade');
            $table->decimal('total_payable_amount', 10, 2);
            $table->decimal('paid_amount', 10, 2)->default(0);
            $table->decimal('remaining_amount', 10, 2);
            $table->date('due_date');
            $table->enum('status', ['pending', 'partial', 'paid', 'overdue'])->default('pending');
            $table->json('payment_history')->nullable();
            $table->text('notes')->nullable();
            $table->string('category')->nullable(); // Ex: 'fornecedor', 'servico', 'despesa'
            $table->string('description')->nullable();
            $table->timestamps();

            // Índices para melhor performance
            $table->index(['status', 'due_date']);
            $table->index(['supplier_id', 'status']);
            $table->index('due_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payable_payments');
    }
};