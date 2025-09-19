<?php
// routes/web.php

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;

Route::get('/gerar-senha/{senha}', function ($senha) {
    $hashed = Hash::make($senha);
    return "Senha: $senha <br>Hash: $hashed";
});
