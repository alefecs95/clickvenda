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
    echo "Namespace: " . $xml->getNamespaces(true)[''] . "\n";
    
    // Registrar namespace
    $xml->registerXPathNamespace('nfe', 'http://www.portalfiscal.inf.br/nfe');
    
    // Tentar diferentes XPath
    echo "\n=== Testando XPath ===\n";
    
    $paths = [
        '//det/prod',
        '//nfe:det/nfe:prod',
        '/nfeProc/NFe/infNFe/det/prod',
        '//NFe//det//prod'
    ];
    
    foreach ($paths as $path) {
        $products = $xml->xpath($path);
        echo "XPath '$path': " . count($products) . " produtos encontrados\n";
        
        if (!empty($products)) {
            $firstProduct = $products[0];
            echo "  - Primeiro produto:\n";
            echo "    cProd: " . (string)$firstProduct->cProd . "\n";
            echo "    xProd: " . (string)$firstProduct->xProd . "\n";
            echo "    vUnCom: " . (string)$firstProduct->vUnCom . "\n";
            echo "    qCom: " . (string)$firstProduct->qCom . "\n";
            break;
        }
    }
    
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}

?>