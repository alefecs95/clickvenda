<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Models\Order;
use Illuminate\Database\Capsule\Manager as Capsule;

// Carregar configuração do Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    $order = Order::with(['customer', 'items.product', 'user'])->find(25);
    
    if (!$order) {
        echo "Pedido 25 não encontrado.\n";
        exit(1);
    }
    
    echo "=== FORMAS DE PAGAMENTO DO PEDIDO 25 ===\n\n";
    echo "Pedido: {$order->order_number}\n";
    echo "Status: {$order->status}\n";
    echo "Total: R$ " . number_format($order->final_amount, 2, ',', '.') . "\n\n";
    
    // Verificar se tem múltiplas formas de pagamento
    if ($order->payment_method === 'multiple' && $order->payment_methods) {
        echo "MÚLTIPLAS FORMAS DE PAGAMENTO:\n";
        echo str_repeat("-", 40) . "\n";
        
        $total_pagamentos = 0;
        foreach ($order->payment_methods as $index => $payment) {
            $metodo = match($payment['method']) {
                'money' => 'Dinheiro',
                'card' => 'Cartão',
                'pix' => 'PIX',
                'credit' => 'Crediário',
                default => $payment['method']
            };
            
            $valor = number_format($payment['amount'], 2, ',', '.');
            echo ($index + 1) . ". {$metodo}: R$ {$valor}\n";
            $total_pagamentos += $payment['amount'];
        }
        
        echo str_repeat("-", 40) . "\n";
        echo "Total dos pagamentos: R$ " . number_format($total_pagamentos, 2, ',', '.') . "\n";
        
    } else {
        echo "FORMA DE PAGAMENTO ÚNICA:\n";
        echo str_repeat("-", 40) . "\n";
        
        $metodo = match($order->payment_method) {
            'money' => 'Dinheiro',
            'card' => 'Cartão',
            'pix' => 'PIX',
            'credit' => 'Crediário',
            'multiple' => 'Múltiplas formas',
            default => $order->payment_method
        };
        
        echo "Método: {$metodo}\n";
        echo "Valor: R$ " . number_format($order->final_amount, 2, ',', '.') . "\n";
    }
    
    // Mostrar informações do cliente se houver
    if ($order->customer) {
        echo "\nCliente: {$order->customer->name}\n";
    }
    
    // Mostrar data do pedido
    echo "Data: " . $order->created_at->format('d/m/Y H:i:s') . "\n";
    
} catch (Exception $e) {
    echo "Erro ao consultar pedido: " . $e->getMessage() . "\n";
    exit(1);
}