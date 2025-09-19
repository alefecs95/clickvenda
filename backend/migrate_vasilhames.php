<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Foundation\Application;
use App\Models\Product;
use App\Models\VasilhameModel;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== MIGRAÇÃO DE PRODUTOS RETORNÁVEIS PARA VASILHAME_MODELS ===\n\n";

try {
    // Buscar todos os produtos retornáveis
    $produtosRetornaveis = Product::where('returnable', true)->get();
    
    echo "Encontrados " . $produtosRetornaveis->count() . " produtos retornáveis na tabela products.\n\n";
    
    $migrados = 0;
    $jaExistentes = 0;
    
    foreach ($produtosRetornaveis as $produto) {
        // Verificar se já existe um vasilhame com o mesmo nome
        $vasilhameExistente = VasilhameModel::where('name', $produto->name)->first();
        
        if ($vasilhameExistente) {
            echo "✓ Já existe: {$produto->name} - R$ " . number_format($produto->returnable_price, 2, ',', '.') . "\n";
            $jaExistentes++;
        } else {
            // Criar novo vasilhame
            $novoVasilhame = VasilhameModel::create([
                'name' => $produto->name,
                'description' => "Migrado automaticamente do produto: {$produto->name}",
                'returnable_price' => $produto->returnable_price,
                'available' => true,
                'stock' => 0 // Iniciar com estoque 0
            ]);
            
            echo "✓ Migrado: {$produto->name} - R$ " . number_format($produto->returnable_price, 2, ',', '.') . "\n";
            $migrados++;
        }
    }
    
    echo "\n=== RESUMO DA MIGRAÇÃO ===\n";
    echo "Produtos migrados: {$migrados}\n";
    echo "Produtos já existentes: {$jaExistentes}\n";
    echo "Total processados: " . ($migrados + $jaExistentes) . "\n\n";
    
    // Mostrar todos os vasilhames após a migração
    echo "=== VASILHAMES CADASTRADOS APÓS MIGRAÇÃO ===\n";
    $todosVasilhames = VasilhameModel::orderBy('name')->get();
    
    foreach ($todosVasilhames as $vasilhame) {
        echo "- {$vasilhame->name} - R$ " . number_format($vasilhame->returnable_price, 2, ',', '.') . 
             " (Estoque: {$vasilhame->stock})\n";
    }
    
    echo "\nMigração concluída com sucesso!\n";
    
} catch (Exception $e) {
    echo "ERRO durante a migração: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}