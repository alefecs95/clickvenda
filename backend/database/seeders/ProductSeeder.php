<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        // Primeiro, criar o produto pai (gerencia estoque)
        $skolUnit = Product::create([
            'name' => 'Cerveja Skol 350ml',
            'description' => 'Cerveja Skol latão 350ml (unidade)',
            'price' => 4.50,
            'stock_quantity' => 240, // 10 caixas = 240 unidades
            'sku' => 'SKOL-350',
            'barcode' => '7891234567890',
            'active' => true,
            'available' => true,
            'returnable' => true,
            'returnable_price' => 0.50,
            'returnable_quantity' => 1,
            'parent_product_id' => null,
            'stock_multiplier' => 1,
            'manages_stock' => true,
        ]);
        
        // Depois, criar o produto filho (caixa)
        Product::create([
            'name' => 'Caixa Skol 350ml (24un)',
            'description' => 'Caixa com 24 unidades de Cerveja Skol 350ml',
            'price' => 89.90,
            'stock_quantity' => 10, // Calculado: 240 / 24 = 10 caixas
            'sku' => 'SKOL-CX24',
            'barcode' => '7891234567891',
            'active' => true,
            'available' => true,
            'returnable' => true,
            'returnable_price' => 0.50,
            'returnable_quantity' => 24,
            'parent_product_id' => $skolUnit->id,
            'stock_multiplier' => 24, // 1 caixa = 24 unidades
            'manages_stock' => false,
        ]);

        $products = [
            [
                'name' => 'Pepsi 2L',
                'description' => 'Refrigerante Pepsi garrafa 2L',
                'price' => 8.90,
                'stock_quantity' => 50,
                'sku' => 'PEPSI-2L',
                'barcode' => '7891234567892',
                'active' => true,
                'available' => true,
                'returnable' => true,
                'returnable_price' => 2.00,
                'returnable_quantity' => 1,
                'parent_product_id' => null,
                'stock_multiplier' => 1,
                'manages_stock' => true,
            ],
            [
                'name' => 'Pão Francês',
                'description' => 'Pão francês fresco',
                'price' => 0.50,
                'stock_quantity' => 200,
                'sku' => 'PAO-FRANCES',
                'barcode' => '7891234567893',
                'active' => true,
                'available' => true,
                'returnable' => false,
                'returnable_price' => null,
                'returnable_quantity' => 1,
                'parent_product_id' => null,
                'stock_multiplier' => 1,
                'manages_stock' => true,
            ],
            [
                'name' => 'Leite Integral 1L',
                'description' => 'Leite integral 1L',
                'price' => 5.90,
                'stock_quantity' => 30,
                'sku' => 'LEITE-1L',
                'barcode' => '7891234567894',
                'active' => true,
                'available' => true,
                'returnable' => false,
                'returnable_price' => null,
                'returnable_quantity' => 1,
                'parent_product_id' => null,
                'stock_multiplier' => 1,
                'manages_stock' => true,
            ],
            [
                'name' => 'Arroz Branco 5kg',
                'description' => 'Arroz branco tipo 1, pacote 5kg',
                'price' => 22.90,
                'stock_quantity' => 25,
                'sku' => 'ARROZ-5KG',
                'barcode' => '7891234567895',
                'active' => true,
                'available' => true,
                'returnable' => false,
                'returnable_price' => null,
                'returnable_quantity' => 1,
                'parent_product_id' => null,
                'stock_multiplier' => 1,
                'manages_stock' => true,
            ],
            [
                'name' => 'Agua Mineral 510ml',
                'description' => 'Água mineral natural 510ml',
                'price' => 2.50,
                'stock_quantity' => 0,
                'sku' => 'AGUA-510',
                'barcode' => '7891234567896',
                'active' => true,
                'available' => false, // Produto indisponível temporariamente
                'returnable' => false,
                'returnable_price' => null,
                'returnable_quantity' => 1,
                'parent_product_id' => null,
                'stock_multiplier' => 1,
                'manages_stock' => true,
            ],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
