<?php

$xmlPath = "../22250926537749000166550010000154711672134584.xml";

if (!file_exists($xmlPath)) {
    echo "Arquivo XML não encontrado: $xmlPath\n";
    exit(1);
}

try {
    $xmlContent = file_get_contents($xmlPath);
    $xml = simplexml_load_string($xmlContent);
    
    if (!$xml) {
        echo "Erro: XML inválido\n";
        exit(1);
    }
    
    echo "XML carregado com sucesso!\n";
    echo "Estrutura do XML:\n";
    
    // Verificar estrutura NFe
    $nfeProducts = $xml->xpath("//det/prod");
    echo "Produtos NFe encontrados: " . count($nfeProducts) . "\n";
    
    if (!empty($nfeProducts)) {
        echo "Primeiro produto NFe:\n";
        $firstProduct = $nfeProducts[0];
        echo "- cProd: " . (string)$firstProduct->cProd . "\n";
        echo "- xProd: " . (string)$firstProduct->xProd . "\n";
        echo "- vUnCom: " . (string)$firstProduct->vUnCom . "\n";
        echo "- qCom: " . (string)$firstProduct->qCom . "\n";
    }
    
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}

?>