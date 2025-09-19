<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Models\Product;

// Configurar o ambiente Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== VERIFICAÇÃO DO ESTOQUE EXIBIDO ===\n\n";

// Buscar produtos
$pai = Product::where('sku', 'SKOL-350')->first();
$filho = Product::where('sku', 'SKOL-CX24')->first();

if ($pai) {
    echo "Produto Pai (Cerveja Skol 350ml):\n";
    echo "- ID: {$pai->id}\n";
    echo "- stock_quantity (banco): {$pai->stock_quantity}\n";
    echo "- getActualStock(): {$pai->getActualStock()}\n";
    echo "- manages_stock: " . ($pai->manages_stock ? 'SIM' : 'NÃO') . "\n\n";
}

if ($filho) {
    echo "Produto Filho (Caixa Skol 24un):\n";
    echo "- ID: {$filho->id}\n";
    echo "- stock_quantity (banco): {$filho->stock_quantity}\n";
    echo "- getActualStock(): {$filho->getActualStock()}\n";
    echo "- stock_multiplier: {$filho->stock_multiplier}\n";
    echo "- manages_stock: " . ($filho->manages_stock ? 'SIM' : 'NÃO') . "\n";
    echo "- parent_product_id: {$filho->parent_product_id}\n\n";
    
    if ($pai) {
        echo "Cálculo manual do estoque do filho:\n";
        echo "- Estoque do pai: {$pai->stock_quantity}\n";
        echo "- Multiplicador: {$filho->stock_multiplier}\n";
        echo "- Resultado: " . intval($pai->stock_quantity / $filho->stock_multiplier) . "\n\n";
    }
}

echo "=== PROBLEMA IDENTIFICADO ===\n";
echo "O frontend está mostrando 'product.actual_stock || product.stock_quantity'\n";
echo "Se actual_stock não estiver sendo enviado pelo backend, ele usa stock_quantity do banco.\n";
echo "O stock_quantity do produto filho no banco é {$filho->stock_quantity}, que é o valor sendo exibido.\n\n";

echo "=== SOLUÇÃO ===\n";
echo "O backend já está calculando actual_stock corretamente: {$filho->getActualStock()}\n";
echo "Mas o produto filho tem stock_quantity = {$filho->stock_quantity} no banco.\n";
echo "Vamos sincronizar o stock_quantity do filho com o valor calculado.\n\n";

// Sincronizar o estoque do filho
if ($pai && $filho) {
    $novoEstoque = $filho->getActualStock();
    $filho->update(['stock_quantity' => $novoEstoque]);
    $filho->refresh();
    
    echo "Após sincronização:\n";
    echo "- Filho stock_quantity: {$filho->stock_quantity}\n";
    echo "- Filho getActualStock(): {$filho->getActualStock()}\n";
}

echo "\n=== FIM ===\n";