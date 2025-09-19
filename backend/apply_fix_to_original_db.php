<?php

require_once 'vendor/autoload.php';

$host = 'localhost';
$dbname = 'click_venda';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Aplicando correção no banco original...\n";
    echo "======================================\n\n";
    
    $pdo->beginTransaction();
    
    try {
        // Limpar tabela de migrações
        $pdo->exec("DELETE FROM migrations");
        
        // Definir ordem correta manualmente
        $correctOrder = [
            // Batch 1 - Tabelas básicas do Laravel
            ['migration' => '2014_10_12_000000_create_users_table', 'batch' => 1],
            ['migration' => '2014_10_12_100000_create_password_reset_tokens_table', 'batch' => 1],
            
            // Batch 2 - Outras tabelas do Laravel
            ['migration' => '2019_08_19_000000_create_failed_jobs_table', 'batch' => 2],
            ['migration' => '2019_12_14_000001_create_personal_access_tokens_table', 'batch' => 3],
            
            // Batch 4 - Tabelas principais do sistema (devem vir antes das que dependem delas)
            ['migration' => '2025_08_07_195238_create_products_table', 'batch' => 4],
            ['migration' => '2025_08_07_195243_create_customers_table', 'batch' => 4],
            ['migration' => '2025_08_07_195248_create_orders_table', 'batch' => 4],
            ['migration' => '2025_08_07_195252_create_order_items_table', 'batch' => 4],
            
            // Batch 5 - Adições às tabelas principais
            ['migration' => '2025_01_17_000000_add_minimum_stock_to_products_table', 'batch' => 5],
            ['migration' => '2025_09_16_161832_add_returnable_and_available_fields_to_products_table', 'batch' => 5],
            ['migration' => '2025_09_16_162925_add_shared_stock_fields_to_products_table', 'batch' => 5],
            ['migration' => '2025_09_16_164618_add_credit_fields_to_customers_table', 'batch' => 5],
            ['migration' => '2025_09_16_180841_add_credit_payment_method_to_orders_table', 'batch' => 5],
            ['migration' => '2025_09_16_182957_add_payment_methods_to_orders_table', 'batch' => 5],
            ['migration' => '2025_09_16_184913_update_payment_method_enum_add_multiple', 'batch' => 5],
            ['migration' => '2025_09_16_191221_create_receivable_payments_table', 'batch' => 5],
            ['migration' => '2025_09_16_204715_create_settings_table', 'batch' => 5],
            ['migration' => '2025_09_17_120001_add_is_vasilhame_model_to_products_table', 'batch' => 5],
            ['migration' => '2025_09_18_174945_create_roles_table', 'batch' => 5],
            ['migration' => '2025_09_18_174953_create_permissions_table', 'batch' => 5],
            ['migration' => '2025_09_18_175034_create_role_user_table', 'batch' => 5],
            ['migration' => '2025_09_18_175045_create_permission_role_table', 'batch' => 5],
            ['migration' => '2025_09_18_180635_populate_username_field', 'batch' => 5],
            ['migration' => '2025_09_18_184112_make_email_nullable_in_users_table', 'batch' => 5],
            ['migration' => '2025_09_18_193304_remove_email_from_users_table', 'batch' => 5],
            
            // Batch 6 - Tabelas que dependem das principais (com nomes corrigidos)
            ['migration' => '2025_08_08_000000_create_vasilhame_movimentacoes_table', 'batch' => 6],
            ['migration' => '2025_09_17_103450_remove_valor_unitario_from_vasilhame_movimentacoes_table', 'batch' => 6],
            ['migration' => '2025_09_17_130000_create_vasilhame_models_table', 'batch' => 6],
            ['migration' => '2025_09_17_130001_add_vasilhame_model_id_to_vasilhame_movimentacoes_table', 'batch' => 6],
            ['migration' => '2025_09_18_000000_create_pendencia_vasilhames_table', 'batch' => 6],
            ['migration' => '2025_09_18_015633_make_produto_id_nullable_in_vasilhame_movimentacoes_table', 'batch' => 6],
        ];
        
        // Inserir migrações na ordem correta
        foreach ($correctOrder as $migration) {
            $stmt = $pdo->prepare("INSERT INTO migrations (migration, batch) VALUES (?, ?)");
            $stmt->execute([$migration['migration'], $migration['batch']]);
            echo "Batch {$migration['batch']}: {$migration['migration']}\n";
        }
        
        $pdo->commit();
        echo "\n✅ Migrações organizadas no banco original!\n";
        
    } catch (Exception $e) {
        $pdo->rollBack();
        echo "\n❌ Erro ao organizar migrações: " . $e->getMessage() . "\n";
    }
    
} catch (PDOException $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}
