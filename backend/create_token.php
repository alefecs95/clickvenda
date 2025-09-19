<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;

// Buscar o primeiro usuário
$user = User::first();

if (!$user) {
    echo "Nenhum usuário encontrado. Execute os seeders primeiro.\n";
    exit(1);
}

// Criar token
$token = $user->createToken('test-token')->plainTextToken;

echo "Token gerado para usuário: {$user->name} ({$user->email})\n";
echo "Token: {$token}\n";
echo "\nCole este comando no console do navegador:\n";
echo "localStorage.setItem('token', '{$token}');\n";