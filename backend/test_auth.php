<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;

try {
    echo "=== TESTE DE AUTENTICAÇÃO ===\n";
    
    // Buscar primeiro usuário
    $user = User::first();
    
    if (!$user) {
        echo "❌ Nenhum usuário encontrado no banco de dados\n";
        exit(1);
    }
    
    echo "✅ Usuário encontrado: {$user->name} ({$user->email})\n";
    
    // Criar token
    $token = $user->createToken('test-token')->plainTextToken;
    
    echo "✅ Token criado: $token\n";
    
    // Testar API com token
    echo "\n=== TESTANDO API COM TOKEN ===\n";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'http://localhost:8000/api/payables');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $token,
        'Accept: application/json',
        'Content-Type: application/json'
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "Status HTTP: $httpCode\n";
    echo "Resposta:\n";
    echo $response . "\n";
    
    // Testar estatísticas
    echo "\n=== TESTANDO ESTATÍSTICAS ===\n";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'http://localhost:8000/api/payables/statistics');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $token,
        'Accept: application/json',
        'Content-Type: application/json'
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "Status HTTP: $httpCode\n";
    echo "Resposta:\n";
    echo $response . "\n";
    
} catch (Exception $e) {
    echo "❌ ERRO: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}