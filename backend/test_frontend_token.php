<?php
// Teste para verificar se o problema é com o token do frontend

// Simular o mesmo token que o frontend está usando
$frontendToken = '27|BY0o7QZurEMy4FM1LOnTWR2RD8gkB7q3OcrvKLphd6badb22'; // Token do primeiro teste

$userData = [
    'name' => 'Francinalve',
    'email' => 'email@em5ail.com',
    'username' => 'fran2',
    'cpf' => '07006858300',
    'password' => 'senha123',
    'password_confirmation' => 'senha123',
    'is_admin' => false,
    'status' => 'active',
    'terms_accepted' => true,
    'roles' => []
];

echo "=== TESTE COM TOKEN ESPECÍFICO ===\n";
echo "Token usado: $frontendToken\n\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://127.0.0.1:8000/api/users');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($userData));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json',
    'Authorization: Bearer ' . $frontendToken
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

echo "Status HTTP: $httpCode\n";
echo "Resposta: $response\n";

curl_close($ch);
?>
