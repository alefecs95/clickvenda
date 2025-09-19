<?php

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\Auth;
use App\Models\User;

// Simular ambiente Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Teste de Autenticação ===\n";

// Testar se há usuários no banco
$users = User::all();
echo "Usuários encontrados: " . $users->count() . "\n";

foreach ($users as $user) {
    echo "ID: {$user->id}, Username: {$user->username}, Name: {$user->name}\n";
}

// Testar autenticação
if ($users->count() > 0) {
    $user = $users->first();
    Auth::login($user);
    
    echo "\n=== Teste Auth::id() ===\n";
    echo "Auth::id(): " . Auth::id() . "\n";
    echo "Tipo: " . gettype(Auth::id()) . "\n";
    
    echo "\n=== Teste Auth::user() ===\n";
    $authUser = Auth::user();
    if ($authUser) {
        echo "Auth::user()->id: " . $authUser->id . "\n";
        echo "Tipo: " . gettype($authUser->id) . "\n";
    } else {
        echo "Auth::user() retornou null\n";
    }
}

echo "\n=== Teste concluído ===\n";
