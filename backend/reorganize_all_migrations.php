<?php

require_once 'vendor/autoload.php';

$host = 'localhost';
$dbname = 'click_venda';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Reorganizando TODAS as migrações na ordem correta...\n";
    echo "===================================================\n\n";
    
    // Buscar todas as migrações do banco
    $stmt = $pdo->query("SELECT migration FROM migrations ORDER BY migration");
    $existingMigrations = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "Migrações encontradas no banco: " . count($existingMigrations) . "\n\n";
    
    // Buscar todos os arquivos de migração
    $migrationFiles = glob('database/migrations/*.php');
    $migrations = [];
    
    foreach ($migrationFiles as $file) {
        $filename = basename($file);
        if (preg_match('/^(\d{4}_\d{2}_\d{2}_\d{6})_(.+)\.php$/', $filename, $matches)) {
            $dateStr = str_replace('_', '', $matches[1]);
            $date = DateTime::createFromFormat('YmdHis', $dateStr);
            
            if ($date) {
                $migrations[] = [
                    'file' => $filename,
                    'migration' => str_replace('.php', '', $filename),
                    'date' => $date,
                    'timestamp' => $date->getTimestamp()
                ];
            }
        }
    }
    
    // Ordenar por timestamp
    usort($migrations, function($a, $b) {
        return $a['timestamp'] <=> $b['timestamp'];
    });
    
    echo "Migrações encontradas nos arquivos: " . count($migrations) . "\n\n";
    
    echo "Nova ordem cronológica:\n";
    echo "=======================\n";
    
    $pdo->beginTransaction();
    
    try {
        // Limpar tabela de migrações
        $pdo->exec("DELETE FROM migrations");
        
        // Inserir migrações na ordem cronológica correta
        $batch = 1;
        $currentDate = null;
        
        foreach ($migrations as $index => $migration) {
            // Se a data mudou significativamente (mais de 1 dia), incrementar batch
            if ($currentDate && $migration['date']->diff($currentDate)->days > 1) {
                $batch++;
            }
            
            // Se é uma migração que adiciona coluna, colocar no mesmo batch da tabela base
            if (strpos($migration['migration'], 'add_') === 0) {
                // Manter no mesmo batch se for próxima da migração da tabela
                if ($index > 0) {
                    $prevMigration = $migrations[$index - 1];
                    if (strpos($prevMigration['migration'], 'create_') === 0) {
                        // Manter no mesmo batch
                    } else {
                        $batch++;
                    }
                }
            }
            
            $stmt = $pdo->prepare("INSERT INTO migrations (migration, batch) VALUES (?, ?)");
            $stmt->execute([$migration['migration'], $batch]);
            
            echo sprintf("Batch %2d: %s (%s)\n", $batch, $migration['migration'], $migration['date']->format('Y-m-d H:i:s'));
            
            $currentDate = $migration['date'];
        }
        
        $pdo->commit();
        echo "\n✅ Todas as migrações reorganizadas com sucesso!\n";
        echo "Total de batches: $batch\n";
        
    } catch (Exception $e) {
        $pdo->rollBack();
        echo "\n❌ Erro ao reorganizar migrações: " . $e->getMessage() . "\n";
    }
    
} catch (PDOException $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}
