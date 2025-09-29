<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Models\Product;

// Configurar o ambiente Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== ADICIONANDO ESTOQUE AO PRODUTO ===\n\n";

// Buscar o produto
$product = Product::where('name', 'Caixa Skol 350ml (24un)')->first();

if ($product) {
    echo "Produto encontrado: {$product->name}\n";
    echo "Estoque atual: {$product->getActualStock()}\n";
    
    // Adicionar 50 unidades de estoque
    $product->addStock(50);
    
    echo "Estoque após adição: {$product->fresh()->getActualStock()}\n";
    echo "Estoque adicionado com sucesso!\n";
} else {
    echo "Produto 'Caixa Skol 350ml (24un)' não encontrado.\n";
    
    // Listar produtos similares
    $similarProducts = Product::where('name', 'LIKE', '%Skol%')->get();
    if ($similarProducts->count() > 0) {
        echo "\nProdutos similares encontrados:\n";
        foreach ($similarProducts as $p) {
            echo "- ID: {$p->id}, Nome: {$p->name}, Estoque: {$p->getActualStock()}\n";
        }
    }
}