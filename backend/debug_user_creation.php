<?php
// Teste detalhado para capturar dados enviados e resposta completa

// Primeiro, fazer login para obter token
$loginData = [
    'username' => 'user_1',
    'password' => 'admin123'
];

$loginCh = curl_init();
curl_setopt($loginCh, CURLOPT_URL, 'http://127.0.0.1:8000/api/login');
curl_setopt($loginCh, CURLOPT_POST, true);
curl_setopt($loginCh, CURLOPT_POSTFIELDS, json_encode($loginData));
curl_setopt($loginCh, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
]);
curl_setopt($loginCh, CURLOPT_RETURNTRANSFER, true);

$loginResponse = curl_exec($loginCh);
$loginData = json_decode($loginResponse, true);
$token = $loginData['token'] ?? $loginData['access_token'] ?? null;

echo "=== LOGIN ===\n";
echo "Token obtido: " . ($token ? "SIM" : "NÃO") . "\n\n";

if (!$token) {
    echo "Erro no login: $loginResponse\n";
    exit;
}

// Agora testar criação de usuário com dados exatos do frontend
$userData = [
    'name' => 'Francinalve',
    'email' => 'email@em5ail.com',
    'username' => 'fran',
    'cpf' => '07006858399',
    'password' => 'senha123',
    'password_confirmation' => 'senha123',
    'is_admin' => false,
    'status' => 'active',
    'terms_accepted' => true,
    'roles' => []
];

echo "=== DADOS ENVIADOS ===\n";
echo json_encode($userData, JSON_PRETTY_PRINT) . "\n\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://127.0.0.1:8000/api/users');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($userData));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json',
    'Authorization: Bearer ' . $token
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_VERBOSE, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

echo "=== RESPOSTA ===\n";
echo "Status HTTP: $httpCode\n";
echo "Resposta completa:\n";
echo $response . "\n\n";

if ($httpCode === 422) {
    $errorData = json_decode($response, true);
    echo "=== DETALHES DO ERRO 422 ===\n";
    if (isset($errorData['errors'])) {
        echo "Erros de validação:\n";
        foreach ($errorData['errors'] as $field => $messages) {
            echo "- " . $field . ": " . implode(', ', $messages) . "\n";
        }
    }
    if (isset($errorData['message'])) {
        echo "Mensagem: " . $errorData['message'] . "\n";
    }
}

curl_close($ch);
curl_close($loginCh);
?>