<?php

require_once 'vendor/autoload.php';

$host = 'localhost';
$dbname = 'click_venda';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Corrigindo ordem das migrações de vasilhame...\n";
    echo "==============================================\n\n";
    
    $pdo->beginTransaction();
    
    try {
        // Mover migrações de vasilhame para depois das tabelas principais
        $vasilhameMigrations = [
            '2025_01_20_000000_create_vasilhame_movimentacoes_table',
            '2025_01_21_000000_add_vasilhame_model_id_to_vasilhame_movimentacoes_table'
        ];
        
        foreach ($vasilhameMigrations as $migration) {
            echo "Movendo $migration para batch 7...\n";
            $stmt = $pdo->prepare("UPDATE migrations SET batch = 7 WHERE migration = ?");
            $stmt->execute([$migration]);
        }
        
        // Mover outras migrações de vasilhame para batch 8
        $otherVasilhameMigrations = [
            '2025_09_17_103450_remove_valor_unitario_from_vasilhame_movimentacoes_table',
            '2025_09_17_130000_create_vasilhame_models_table',
            '2025_09_18_000000_create_pendencia_vasilhames_table',
            '2025_09_18_015633_make_produto_id_nullable_in_vasilhame_movimentacoes_table'
        ];
        
        foreach ($otherVasilhameMigrations as $migration) {
            echo "Movendo $migration para batch 8...\n";
            $stmt = $pdo->prepare("UPDATE migrations SET batch = 8 WHERE migration = ?");
            $stmt->execute([$migration]);
        }
        
        // Mover migrações de produtos para batch 6
        $productMigrations = [
            '2025_09_17_120001_add_is_vasilhame_model_to_products_table'
        ];
        
        foreach ($productMigrations as $migration) {
            echo "Movendo $migration para batch 6...\n";
            $stmt = $pdo->prepare("UPDATE migrations SET batch = 6 WHERE migration = ?");
            $stmt->execute([$migration]);
        }
        
        $pdo->commit();
        echo "\n✅ Ordem das migrações corrigida!\n";
        
        // Mostrar nova ordem
        echo "\nNova ordem das migrações:\n";
        echo "========================\n";
        
        $stmt = $pdo->query("SELECT migration, batch FROM migrations ORDER BY batch, migration");
        $migrations = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $currentBatch = null;
        foreach ($migrations as $migration) {
            if ($currentBatch !== $migration['batch']) {
                $currentBatch = $migration['batch'];
                echo "\n--- Batch $currentBatch ---\n";
            }
            echo "- {$migration['migration']}\n";
        }
        
    } catch (Exception $e) {
        $pdo->rollBack();
        echo "\n❌ Erro ao corrigir ordem: " . $e->getMessage() . "\n";
    }
    
} catch (PDOException $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}
