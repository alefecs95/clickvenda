<?php

// Teste das configurações
require_once 'vendor/autoload.php';

use App\Models\Setting;

// Simular dados do frontend
$settings = [
    'store.name' => 'Teste Loja Frontend',
    'store.phone' => '11999999999',
    'store.email' => 'teste@loja.com',
    'sales.default_payment_term' => 30,
    'system.require_customer_for_credit' => true
];

echo "Testando salvamento de configurações:\n";
echo "Dados: " . json_encode($settings, JSON_PRETTY_PRINT) . "\n\n";

try {
    Setting::setMultiple($settings);
    echo "✅ Configurações salvas com sucesso!\n";
    
    // Verificar se foram salvas
    foreach ($settings as $key => $value) {
        $saved = Setting::get($key);
        echo "  $key: $saved (esperado: $value)\n";
    }
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
