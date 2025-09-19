<?php

require_once 'vendor/autoload.php';

use App\Models\Setting;

// Carregar configurações do banco
echo "=== CONFIGURAÇÕES NO BANCO DE DADOS ===\n\n";

$storeSettings = Setting::getByPrefix('store');
echo "Configurações da Loja:\n";
foreach ($storeSettings as $key => $value) {
    echo "- $key: " . (is_array($value) ? json_encode($value) : $value) . "\n";
}

echo "\n";

$salesSettings = Setting::getByPrefix('sales');
echo "Configurações de Vendas:\n";
foreach ($salesSettings as $key => $value) {
    echo "- $key: " . (is_array($value) ? json_encode($value) : $value) . "\n";
}

echo "\n";

$systemSettings = Setting::getByPrefix('system');
echo "Configurações do Sistema:\n";
foreach ($systemSettings as $key => $value) {
    echo "- $key: " . (is_array($value) ? json_encode($value) : $value) . "\n";
}

echo "\n=== TESTE DE API ===\n";

// Testar API diretamente
$token = '5|X5s58bLNYerN0jem5Q0aTYk6UwfGcN6myZeQeTQdf25883a7';
$url = 'http://localhost:8000/api/settings/category/store';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
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
echo "Resposta da API:\n";
echo $response . "\n";

?>
