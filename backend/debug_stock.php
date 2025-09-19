<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Models\Product;

// Configurar o ambiente Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== DEBUG DE PRODUTOS ===\n\n";

// Buscar produtos
$skolPai = Product::where('sku', 'SKOL-350')->first();
$skolCaixa = Product::where('sku', 'SKOL-CX24')->first();

if ($skolPai) {
    echo "Produto Pai (Skol 350ml):\n";
    echo "- ID: {$skolPai->id}\n";
    echo "- SKU: {$skolPai->sku}\n";
    echo "- manages_stock: " . ($skolPai->manages_stock ? 'SIM' : 'NÃO') . "\n";
    echo "- stock_quantity: {$skolPai->stock_quantity}\n";
    echo "- minimum_stock: {$skolPai->minimum_stock}\n";
    echo "- active: " . ($skolPai->active ? 'SIM' : 'NÃO') . "\n";
    echo "- available: " . ($skolPai->available ? 'SIM' : 'NÃO') . "\n\n";
}

if ($skolCaixa) {
    echo "Produto Filho (Caixa Skol 24un):\n";
    echo "- ID: {$skolCaixa->id}\n";
    echo "- SKU: {$skolCaixa->sku}\n";
    echo "- manages_stock: " . ($skolCaixa->manages_stock ? 'SIM' : 'NÃO') . "\n";
    echo "- parent_product_id: {$skolCaixa->parent_product_id}\n";
    echo "- stock_multiplier: {$skolCaixa->stock_multiplier}\n";
    echo "- stock_quantity: {$skolCaixa->stock_quantity}\n";
    echo "- minimum_stock: {$skolCaixa->minimum_stock}\n";
    echo "- active: " . ($skolCaixa->active ? 'SIM' : 'NÃO') . "\n";
    echo "- available: " . ($skolCaixa->available ? 'SIM' : 'NÃO') . "\n";
    
    if ($skolCaixa->parentProduct) {
        echo "- Pai encontrado: SIM (ID: {$skolCaixa->parentProduct->id})\n";
    } else {
        echo "- Pai encontrado: NÃO\n";
    }
    echo "\n";
}

// Testar com estoque 50
if ($skolPai) {
    echo "=== TESTE COM ESTOQUE 50 ===\n";
    $skolPai->update(['stock_quantity' => 50]);
    $skolPai->refresh();
    
    echo "Pai após update:\n";
    echo "- stock_quantity: {$skolPai->stock_quantity}\n";
    
    if ($skolCaixa) {
        $skolCaixa->refresh();
        echo "Filho após refresh:\n";
        echo "- getActualStock(): {$skolCaixa->getActualStock()}\n";
        echo "- Cálculo manual: " . intval($skolPai->stock_quantity / $skolCaixa->stock_multiplier) . "\n";
    }
}

echo "\n=== FIM DEBUG ===\n";