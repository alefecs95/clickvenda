<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

// Popular campo username para usuários que não têm
$users = DB::table('users')->whereNull('username')->orWhere('username', '')->get();

foreach ($users as $user) {
    $username = 'user_' . $user->id;
    DB::table('users')->where('id', $user->id)->update(['username' => $username]);
    echo "Usuário ID {$user->id} atualizado com username: {$username}\n";
}

echo "Concluído!\n";