<?php
require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Role;

try {
    echo "Testando conexão com banco de dados...\n";
    
    $roles = Role::all();
    echo "✅ Roles encontradas: " . $roles->count() . "\n";
    
    if ($roles->count() > 0) {
        echo "Primeira role: " . $roles->first()->name . "\n";
    }
    
    echo "✅ Banco de dados funcionando!\n";
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
    echo "Arquivo: " . $e->getFile() . "\n";
    echo "Linha: " . $e->getLine() . "\n";
}
?>
