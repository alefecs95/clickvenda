<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Vamos verificar especificamente a OS000020 e sua conta a receber
$receivable = App\Models\ReceivablePayment::with(['serviceOrder.items.product', 'serviceOrder.items.service'])
    ->where('customer_id', 3)
    ->whereHas('serviceOrder', function($query) {
        $query->where('order_number', 'OS000020');
    })
    ->first();

if ($receivable) {
    echo 'CONTA A RECEBER ENCONTRADA:' . PHP_EOL;
    echo 'Receivable ID: ' . $receivable->id . PHP_EOL;
    echo 'Customer ID: ' . $receivable->customer_id . PHP_EOL;
    echo 'Service Order ID: ' . ($receivable->service_order_id ?? 'NULL') . PHP_EOL;
    echo PHP_EOL;
    
    if ($receivable->serviceOrder) {
        echo 'SERVICE ORDER:' . PHP_EOL;
        echo 'OS ID: ' . $receivable->serviceOrder->id . PHP_EOL;
        echo 'OS Number: ' . $receivable->serviceOrder->order_number . PHP_EOL;
        echo 'Items count: ' . $receivable->serviceOrder->items->count() . PHP_EOL;
        echo PHP_EOL;
        
        if ($receivable->serviceOrder->items->count() > 0) {
            echo 'ITEMS:' . PHP_EOL;
            foreach ($receivable->serviceOrder->items as $item) {
                echo '- Item ID: ' . $item->id . PHP_EOL;
                echo '  Description: ' . $item->description . PHP_EOL;
                echo '  Type: ' . $item->item_type . PHP_EOL;
                echo '  Quantity: ' . $item->quantity . PHP_EOL;
                echo '  Unit Price: ' . $item->unit_price . PHP_EOL;
                echo '  Total Price: ' . $item->total_price . PHP_EOL;
                
                if ($item->product) {
                    echo '  Product: ' . $item->product->name . PHP_EOL;
                    echo '  Category: ' . ($item->product->category ?? 'N/A') . PHP_EOL;
                }
                if ($item->service) {
                    echo '  Service: ' . $item->service->name . PHP_EOL;
                }
                if ($item->observations) {
                    echo '  Observations: ' . $item->observations . PHP_EOL;
                }
                echo PHP_EOL;
            }
        } else {
            echo 'NO ITEMS FOUND!' . PHP_EOL;
        }
    } else {
        echo 'NO SERVICE ORDER LINKED!' . PHP_EOL;
    }
} else {
    echo 'NO RECEIVABLE FOUND FOR CUSTOMER 3 WITH OS000020!' . PHP_EOL;
}