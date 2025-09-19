<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Models\Product;

// Configurar o ambiente Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== ATUALIZANDO ESTOQUE PARA 20 UNIDADES ===\n\n";

// Buscar produto pai
$pai = Product::where('sku', 'SKOL-350')->first();

if ($pai) {
    echo "Produto Pai antes da atualização:\n";
    echo "- Estoque atual: {$pai->stock_quantity}\n\n";
    
    // Atualizar estoque para 20
    $pai->update(['stock_quantity' => 20]);
    $pai->refresh();
    
    echo "Produto Pai após atualização:\n";
    echo "- Novo estoque: {$pai->stock_quantity}\n\n";
    
    // Sincronizar produtos filhos
    $pai->syncChildrenStocks();
    
    // Verificar produto filho
    $filho = Product::where('sku', 'SKOL-CX24')->first();
    if ($filho) {
        $filho->refresh();
        echo "Produto Filho após sincronização:\n";
        echo "- stock_quantity: {$filho->stock_quantity}\n";
        echo "- getActualStock(): {$filho->getActualStock()}\n";
        echo "- Cálculo: 20 ÷ 24 = " . intval(20 / 24) . "\n\n";
    }
    
    echo "✓ Estoque atualizado com sucesso!\n";
} else {
    echo "❌ Produto pai não encontrado!\n";
}

echo "\n=== FIM ===\n";