<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try {
    $order = App\Models\Order::create([
        'customer_id' => null,
        'user_id' => 1,
        'order_number' => 'TEST' . time(),
        'total_amount' => 10.00,
        'final_amount' => 10.00,
        'payment_method' => 'money'
    ]);
    
    echo "Pedido criado com sucesso: " . $order->id . PHP_EOL;
    
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . PHP_EOL;
    echo "Trace: " . $e->getTraceAsString() . PHP_EOL;
}