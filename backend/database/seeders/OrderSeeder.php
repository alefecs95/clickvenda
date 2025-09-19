<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Customer;
use App\Models\Product;
use App\Models\User;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customers = Customer::all();
        $products = Product::all();
        $users = User::all();

        if ($customers->isEmpty() || $products->isEmpty() || $users->isEmpty()) {
            return;
        }

        $orders = [
            [
                'customer_id' => $customers->first()->id,
                'user_id' => $users->first()->id,
                'order_number' => '202501150001',
                'total_amount' => 150.00,
                'discount_amount' => 10.00,
                'final_amount' => 140.00,
                'payment_method' => 'money',
                'status' => 'completed',
                'notes' => 'Pedido entregue com sucesso',
                'created_at' => now()->subDays(5),
            ],
            [
                'customer_id' => $customers->skip(1)->first()->id,
                'user_id' => $users->first()->id,
                'order_number' => '202501150002',
                'total_amount' => 89.50,
                'discount_amount' => 0.00,
                'final_amount' => 89.50,
                'payment_method' => 'card',
                'status' => 'pending',
                'notes' => 'Aguardando pagamento',
                'created_at' => now()->subDays(3),
            ],
            [
                'customer_id' => $customers->skip(2)->first()->id,
                'user_id' => $users->first()->id,
                'order_number' => '202501150003',
                'total_amount' => 250.00,
                'discount_amount' => 25.00,
                'final_amount' => 225.00,
                'payment_method' => 'pix',
                'status' => 'completed',
                'notes' => 'Pagamento via PIX',
                'created_at' => now()->subDays(1),
            ],
            [
                'customer_id' => $customers->skip(3)->first()->id,
                'user_id' => $users->first()->id,
                'order_number' => '202501150004',
                'total_amount' => 75.00,
                'discount_amount' => 0.00,
                'final_amount' => 75.00,
                'payment_method' => 'money',
                'status' => 'cancelled',
                'notes' => 'Cliente cancelou o pedido',
                'created_at' => now()->subDays(2),
            ],
        ];

        foreach ($orders as $orderData) {
            $order = Order::create($orderData);

            // Criar itens do pedido
            $product = $products->random();
            $quantity = rand(1, 3);
            
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'quantity' => $quantity,
                'unit_price' => $product->price,
                'total_price' => $product->price * $quantity,
            ]);

            // Atualizar estoque se o pedido foi concluído
            if ($order->status === 'completed') {
                $product->decrement('stock_quantity', $quantity);
            }
        }
    }
}
