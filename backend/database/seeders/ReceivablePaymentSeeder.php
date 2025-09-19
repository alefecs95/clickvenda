<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Order;
use App\Models\ReceivablePayment;
use Carbon\Carbon;

class ReceivablePaymentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Buscar todos os pedidos que têm valor no crédito
        $creditOrders = Order::where(function($query) {
            $query->where('payment_method', 'credit')
                  ->orWhere('payment_method', 'multiple');
        })->with('customer')->get();

        foreach ($creditOrders as $order) {
            // Calcular valor no crédito
            $creditAmount = 0;
            
            if ($order->payment_method === 'credit') {
                $creditAmount = $order->final_amount;
            } elseif ($order->payment_method === 'multiple' && $order->payment_methods) {
                $paymentMethods = is_string($order->payment_methods) 
                    ? json_decode($order->payment_methods, true) 
                    : $order->payment_methods;
                
                if (is_array($paymentMethods)) {
                    foreach ($paymentMethods as $payment) {
                        if ($payment['method'] === 'credit') {
                            $creditAmount += $payment['amount'];
                        }
                    }
                }
            }

            // Se há valor no crédito e tem cliente, criar conta a receber
            if ($creditAmount > 0 && $order->customer_id) {
                $dueDate = Carbon::parse($order->created_at)->addDays(30);
                
                // Verificar se já existe para evitar duplicatas
                $existing = ReceivablePayment::where('order_id', $order->id)->first();
                
                if (!$existing) {
                    ReceivablePayment::create([
                        'order_id' => $order->id,
                        'customer_id' => $order->customer_id,
                        'total_receivable_amount' => $creditAmount,
                        'paid_amount' => 0,
                        'remaining_amount' => $creditAmount,
                        'due_date' => $dueDate,
                        'status' => 'pending',
                        'created_at' => $order->created_at,
                        'updated_at' => $order->updated_at
                    ]);
                    
                    echo "Conta a receber criada para pedido #{$order->order_number} - R$ {$creditAmount}\n";
                }
            }
        }
        
        echo "Seeder concluído! Total de contas a receber: " . ReceivablePayment::count() . "\n";
    }
}