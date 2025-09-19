<?php
// Teste de CORS
$url = 'http://localhost:8000/api/roles/public';

echo "Testando CORS...\n";
echo "URL: $url\n\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Accept: application/json',
    'Content-Type: application/json',
    'Origin: http://localhost:5173'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$headers = curl_getinfo($ch, CURLINFO_HEADER_OUT);
$error = curl_error($ch);
curl_close($ch);

echo "HTTP Code: $httpCode\n";
echo "Headers enviados:\n$headers\n";

if ($error) {
    echo "Erro cURL: $error\n";
} else {
    echo "Resposta:\n$response\n";
}

// Testar se o servidor está rodando
echo "\nTestando se o servidor está rodando...\n";
$ch2 = curl_init();
curl_setopt($ch2, CURLOPT_URL, 'http://localhost:8000');
curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch2, CURLOPT_TIMEOUT, 5);

$response2 = curl_exec($ch2);
$httpCode2 = curl_getinfo($ch2, CURLINFO_HTTP_CODE);
curl_close($ch2);

echo "Servidor Laravel (HTTP Code): $httpCode2\n";
if ($httpCode2 === 200) {
    echo "✅ Servidor Laravel está rodando\n";
} else {
    echo "❌ Servidor Laravel não está rodando\n";
}
?>
