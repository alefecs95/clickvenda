<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ProductXmlImportController extends Controller
{
    /**
     * Importa produtos de um arquivo XML
     */
    public function import(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'xml_file' => 'required|file|mimes:xml|max:10240', // Max 10MB
            'update_existing' => 'boolean',
            'use_default_margin' => 'boolean',
            'default_margin' => 'nullable|numeric|min:0',
            'custom_margin' => 'nullable|numeric|min:0',
            'price_factor' => 'nullable|numeric|min:1'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $xmlFile = $request->file('xml_file');
            $updateExisting = $request->boolean('update_existing', true);
            
            // Configurações de margem
            $marginConfig = [
                'use_default_margin' => $request->boolean('use_default_margin', true),
                'default_margin' => $request->input('default_margin'),
                'custom_margin' => $request->input('custom_margin'),
                'price_factor' => $request->input('price_factor')
            ];
            
            // Ler e processar o XML
            $xmlContent = file_get_contents($xmlFile->getPathname());
            $xml = simplexml_load_string($xmlContent);
            
            if (!$xml) {
                return response()->json([
                    'success' => false,
                    'message' => 'Arquivo XML inválido'
                ], 422);
            }

            $results = $this->processXmlProducts($xml, $updateExisting, $marginConfig);

            return response()->json([
                'success' => true,
                'message' => 'Importação concluída com sucesso',
                'results' => $results
            ]);

        } catch (\Exception $e) {
            Log::error('Erro na importação XML: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Processa os produtos do XML
     */
    private function processXmlProducts($xml, $updateExisting = true, $marginConfig = [])
    {
        $created = 0;
        $updated = 0;
        $errors = [];

        DB::beginTransaction();

        try {
            // Detectar se é NFe ou XML genérico
            $products = [];
            
            // Registrar namespace para NFe
            $xml->registerXPathNamespace('nfe', 'http://www.portalfiscal.inf.br/nfe');
            
            // Tentar estrutura de NFe primeiro com namespace
            $nfeProducts = $xml->xpath('//nfe:det/nfe:prod');
            if (!empty($nfeProducts)) {
                $products = $nfeProducts;
            } else {
                // Fallback para estruturas genéricas
                $products = $xml->xpath('//produto') ?: $xml->xpath('//product') ?: $xml->children();
            }

            foreach ($products as $xmlProduct) {
                try {
                    $productData = $this->extractProductData($xmlProduct);
                    
                    if (empty($productData['name'])) {
                        $errors[] = 'Produto sem nome encontrado no XML';
                        continue;
                    }

                    // Aplicar configurações de margem
                    $productData = $this->applyMarginConfiguration($productData, $marginConfig);

                    // Buscar produto existente por código XML, SKU ou código de barras
                    $existingProduct = $this->findExistingProduct($productData);

                    if ($existingProduct && $updateExisting) {
                        // Atualizar produto existente (somar estoque)
                        $this->updateProduct($existingProduct, $productData);
                        $updated++;
                    } elseif (!$existingProduct) {
                        // Criar novo produto
                        $this->createProduct($productData);
                        $created++;
                    }

                } catch (\Exception $e) {
                    $errors[] = 'Erro ao processar produto: ' . $e->getMessage();
                    Log::error('Erro ao processar produto XML: ' . $e->getMessage());
                }
            }

            DB::commit();

            return [
                'created' => $created,
                'updated' => $updated,
                'errors' => $errors,
                'total_processed' => $created + $updated
            ];

        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * Extrai dados do produto do XML
     */
    private function extractProductData($xmlProduct)
    {
        // Detectar se é estrutura de NFe ou genérica
        $isNFe = isset($xmlProduct->cProd) || isset($xmlProduct->xProd);
        
        if ($isNFe) {
            // Mapear campos específicos da NFe
            $data = [
                'name' => trim((string) $xmlProduct->xProd), // Nome do produto na NFe
                'description' => trim((string) $xmlProduct->xProd), // Usar nome como descrição inicial
                'price' => $this->parseDecimal((string) $xmlProduct->vUnCom), // Valor unitário comercial
                'purchase_price' => $this->parseDecimal((string) $xmlProduct->vUnCom), // Usar como preço de compra
                'stock_quantity' => $this->parseDecimal((string) $xmlProduct->qCom), // Quantidade comercial
                'sku' => trim((string) $xmlProduct->cProd), // Código do produto
                'barcode' => $this->getNFeBarcode($xmlProduct), // Código de barras (cEAN ou cEANTrib)
                'xml_code' => trim((string) $xmlProduct->cProd), // Código XML para identificação
                'unit' => trim((string) $xmlProduct->uCom), // Unidade comercial
                'ncm' => trim((string) $xmlProduct->NCM), // Código NCM
                'cfop' => trim((string) $xmlProduct->CFOP), // CFOP
                'active' => true,
                'available' => true,
                'manages_stock' => true
            ];
        } else {
            // Mapear campos genéricos do XML
            $data = [
                'name' => $this->getXmlValue($xmlProduct, ['nome', 'name', 'descricao', 'description']),
                'description' => $this->getXmlValue($xmlProduct, ['descricao_completa', 'full_description', 'observacoes']),
                'price' => $this->parseDecimal($this->getXmlValue($xmlProduct, ['preco_venda', 'price', 'valor_venda'])),
                'purchase_price' => $this->parseDecimal($this->getXmlValue($xmlProduct, ['preco_compra', 'purchase_price', 'valor_compra', 'custo'])),
                'profit_margin' => $this->parseDecimal($this->getXmlValue($xmlProduct, ['margem_lucro', 'profit_margin', 'margem'])),
                'stock_quantity' => $this->parseInt($this->getXmlValue($xmlProduct, ['estoque', 'stock', 'quantidade'])),
                'minimum_stock' => $this->parseInt($this->getXmlValue($xmlProduct, ['estoque_minimo', 'minimum_stock', 'min_stock'])),
                'sku' => $this->getXmlValue($xmlProduct, ['sku', 'codigo', 'code']),
                'barcode' => $this->getXmlValue($xmlProduct, ['codigo_barras', 'barcode', 'ean']),
                'xml_code' => $this->getXmlValue($xmlProduct, ['codigo_xml', 'xml_code', 'id', 'codigo']),
                'active' => true,
                'available' => true,
                'manages_stock' => true
            ];
        }

        // Calcular preço de venda se temos valor de compra e margem
        if ($data['purchase_price'] && isset($data['profit_margin']) && $data['profit_margin'] && !$data['price']) {
            $data['price'] = $data['purchase_price'] * (1 + ($data['profit_margin'] / 100));
        }

        // Calcular margem se temos preço de venda e compra
        if ($data['price'] && $data['purchase_price'] && (!isset($data['profit_margin']) || !$data['profit_margin'])) {
            $data['profit_margin'] = (($data['price'] - $data['purchase_price']) / $data['purchase_price']) * 100;
        }

        return array_filter($data, function($value) {
            return $value !== null && $value !== '';
        });
    }

    /**
     * Obtém código de barras da NFe (cEAN ou cEANTrib)
     */
    private function getNFeBarcode($xmlProduct)
    {
        $cEAN = trim((string) $xmlProduct->cEAN);
        $cEANTrib = trim((string) $xmlProduct->cEANTrib);
        
        // Retornar o primeiro código válido (não "SEM GTIN")
        if ($cEAN && $cEAN !== 'SEM GTIN') {
            return $cEAN;
        }
        
        if ($cEANTrib && $cEANTrib !== 'SEM GTIN') {
            return $cEANTrib;
        }
        
        return null;
    }

    /**
     * Busca produto existente
     */
    private function findExistingProduct($productData)
    {
        $query = Product::query();

        // Buscar por código XML primeiro
        if (!empty($productData['xml_code'])) {
            $product = $query->where('xml_code', $productData['xml_code'])->first();
            if ($product) return $product;
        }

        // Buscar por SKU
        if (!empty($productData['sku'])) {
            $product = Product::where('sku', $productData['sku'])->first();
            if ($product) return $product;
        }

        // Buscar por código de barras
        if (!empty($productData['barcode'])) {
            $product = Product::where('barcode', $productData['barcode'])->first();
            if ($product) return $product;
        }

        // Buscar por nome exato
        if (!empty($productData['name'])) {
            $product = Product::where('name', $productData['name'])->first();
            if ($product) return $product;
        }

        return null;
    }

    /**
     * Cria novo produto
     */
    private function createProduct($productData)
    {
        return Product::create($productData);
    }

    /**
     * Atualiza produto existente
     */
    private function updateProduct($product, $productData)
    {
        // Não sobrescrever estoque se não foi fornecido no XML
        if (!isset($productData['stock_quantity'])) {
            unset($productData['stock_quantity']);
        } else {
            // Se foi fornecido, somar ao estoque atual
            $productData['stock_quantity'] += $product->stock_quantity;
        }

        return $product->update($productData);
    }

    /**
     * Obtém valor do XML tentando múltiplos campos
     */
    private function getXmlValue($xmlElement, $fields)
    {
        foreach ($fields as $field) {
            if (isset($xmlElement->$field)) {
                return trim((string) $xmlElement->$field);
            }
        }
        return null;
    }

    /**
     * Converte string para decimal
     */
    private function parseDecimal($value)
    {
        if (empty($value)) return null;
        
        // Remover caracteres não numéricos exceto vírgula e ponto
        $value = preg_replace('/[^\d,.-]/', '', $value);
        
        // Converter vírgula para ponto
        $value = str_replace(',', '.', $value);
        
        return is_numeric($value) ? (float) $value : null;
    }

    /**
     * Converte string para inteiro
     */
    private function parseInt($value)
    {
        if (empty($value)) return null;
        
        // Remover caracteres não numéricos
        $value = preg_replace('/[^\d]/', '', $value);
        
        return is_numeric($value) ? (int) $value : null;
    }

    /**
     * Valida estrutura do XML
     */
    public function validateXml(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'xml_file' => 'required|file|mimes:xml|max:10240'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'valid' => false,
                'message' => 'Arquivo inválido',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $xmlFile = $request->file('xml_file');
            $xmlContent = file_get_contents($xmlFile->getPathname());
            $xml = simplexml_load_string($xmlContent);
            
            if (!$xml) {
                return response()->json([
                    'valid' => false,
                    'message' => 'Arquivo XML inválido ou corrompido'
                ], 422);
            }

            // Registrar namespace para NFe
            $xml->registerXPathNamespace('nfe', 'http://www.portalfiscal.inf.br/nfe');
            
            // Detectar tipo de XML e analisar estrutura
            $nfeProducts = $xml->xpath('//nfe:det/nfe:prod');
            $isNFe = !empty($nfeProducts);
            
            if ($isNFe) {
                // Processar NFe
                $products = $nfeProducts;
                $xmlType = 'NFe';
                $preview = $this->generateNFePreview($products);
            } else {
                // Processar XML genérico
                $products = $xml->xpath('//produto') ?: $xml->xpath('//product') ?: $xml->children();
                $xmlType = 'Genérico';
                $preview = $this->generateGenericPreview($products);
            }

            return response()->json([
                'valid' => true,
                'message' => 'XML válido',
                'xml_type' => $xmlType,
                'products_count' => count($products),
                'preview' => $preview
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'valid' => false,
                'message' => 'Erro ao processar XML: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Gera preview dos produtos da NFe
     */
    private function generateNFePreview($products)
    {
        $preview = [];
        $maxPreview = 10; // Limitar preview a 10 produtos
        
        foreach (array_slice($products, 0, $maxPreview) as $xmlProduct) {
            $productData = $this->extractProductData($xmlProduct);
            $existingProduct = $this->findExistingProduct($productData);
            
            $preview[] = [
                'codigo' => $productData['sku'] ?? 'N/A',
                'nome' => $productData['name'] ?? 'N/A',
                'preco_unitario' => $productData['price'] ?? 0,
                'quantidade' => $productData['stock_quantity'] ?? 0,
                'codigo_barras' => $productData['barcode'] ?? 'N/A',
                'unidade' => $productData['unit'] ?? 'N/A',
                'ncm' => $productData['ncm'] ?? 'N/A',
                'status' => $existingProduct ? 'Atualizar Estoque' : 'Criar Novo',
                'produto_existente' => $existingProduct ? [
                    'id' => $existingProduct->id,
                    'nome' => $existingProduct->name,
                    'estoque_atual' => $existingProduct->stock_quantity,
                    'novo_estoque' => $existingProduct->stock_quantity + ($productData['stock_quantity'] ?? 0)
                ] : null
            ];
        }
        
        return $preview;
    }

    /**
     * Gera preview dos produtos genéricos
     */
    private function generateGenericPreview($products)
    {
        $preview = [];
        $maxPreview = 10;
        
        foreach (array_slice($products, 0, $maxPreview) as $xmlProduct) {
            $productData = $this->extractProductData($xmlProduct);
            $existingProduct = $this->findExistingProduct($productData);
            
            $preview[] = [
                'codigo' => $productData['sku'] ?? $productData['xml_code'] ?? 'N/A',
                'nome' => $productData['name'] ?? 'N/A',
                'preco_venda' => $productData['price'] ?? 0,
                'preco_compra' => $productData['purchase_price'] ?? 0,
                'estoque' => $productData['stock_quantity'] ?? 0,
                'codigo_barras' => $productData['barcode'] ?? 'N/A',
                'status' => $existingProduct ? 'Atualizar' : 'Criar Novo',
                'produto_existente' => $existingProduct ? [
                    'id' => $existingProduct->id,
                    'nome' => $existingProduct->name,
                    'estoque_atual' => $existingProduct->stock_quantity
                ] : null
            ];
        }
        
        return $preview;
    }

    /**
     * Aplica configurações de margem aos dados do produto
     */
    private function applyMarginConfiguration($productData, $marginConfig)
    {
        // Se não há configuração de margem, retorna os dados originais
        if (empty($marginConfig)) {
            return $productData;
        }

        $purchasePrice = $productData['purchase_price'] ?? 0;
        
        // Se não há preço de compra, não pode calcular margem
        if (!$purchasePrice || $purchasePrice <= 0) {
            return $productData;
        }

        $salePrice = null;

        if ($marginConfig['use_default_margin'] && !empty($marginConfig['default_margin'])) {
            // Usar margem padrão
            $margin = floatval($marginConfig['default_margin']);
            $salePrice = $purchasePrice * (1 + ($margin / 100));
            
        } elseif (!empty($marginConfig['custom_margin'])) {
            // Usar margem personalizada
            $margin = floatval($marginConfig['custom_margin']);
            $salePrice = $purchasePrice * (1 + ($margin / 100));
            
        } elseif (!empty($marginConfig['price_factor'])) {
            // Usar fator de multiplicação
            $factor = floatval($marginConfig['price_factor']);
            $salePrice = $purchasePrice * $factor;
        }

        // Se calculou um preço de venda, atualiza os dados do produto
        if ($salePrice && $salePrice > 0) {
            $productData['price'] = round($salePrice, 2);
            
            // Calcular margem de lucro para armazenar
            $profitMargin = (($salePrice - $purchasePrice) / $purchasePrice) * 100;
            $productData['profit_margin'] = round($profitMargin, 2);
        }

        return $productData;
    }
}