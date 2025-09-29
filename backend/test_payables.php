<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\PayablePayment;

try {
    echo "=== TESTE DE PAYABLES ===\n";
    
    // Contar total de payables
    $totalPayables = PayablePayment::count();
    echo "Total de payables: $totalPayables\n";
    
    // Verificar se há payables
    if ($totalPayables > 0) {
        echo "\n=== PRIMEIROS 5 PAYABLES ===\n";
        $payables = PayablePayment::with('supplier')->take(5)->get();
        
        foreach ($payables as $payable) {
            echo "ID: {$payable->id}\n";
            echo "Fornecedor: " . ($payable->supplier ? $payable->supplier->name : 'Não informado') . "\n";
            echo "Valor: R$ " . number_format($payable->total_payable_amount, 2, ',', '.') . "\n";
            echo "Status: {$payable->status}\n";
            echo "Vencimento: {$payable->due_date}\n";
            echo "---\n";
        }
    }
    
    // Testar estatísticas
    echo "\n=== ESTATÍSTICAS ===\n";
    $totalPending = PayablePayment::whereIn('status', ['pending', 'partial'])->sum('remaining_amount');
    $totalOverdue = PayablePayment::where('due_date', '<', now())->whereIn('status', ['pending', 'partial'])->sum('remaining_amount');
    $totalPaid = PayablePayment::where('status', 'paid')->sum('total_payable_amount');
    
    echo "Total pendente: R$ " . number_format($totalPending, 2, ',', '.') . "\n";
    echo "Total vencido: R$ " . number_format($totalOverdue, 2, ',', '.') . "\n";
    echo "Total pago: R$ " . number_format($totalPaid, 2, ',', '.') . "\n";
    
} catch (Exception $e) {
    echo "ERRO: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}