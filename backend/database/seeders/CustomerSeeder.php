<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Customer;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customers = [
            [
                'name' => 'João Silva',
                'email' => 'joao.silva@email.com',
                'phone' => '(11) 99999-9999',
                'cpf_cnpj' => '123.456.789-00',
                'address' => 'Rua das Flores, 123 - São Paulo, SP',
                'active' => true,
                'credit_limit' => 1000.00,
                'credit_used' => 250.00,
                'credit_notes' => 'Cliente VIP - Limite aprovado pela gerência',
                'credit_limit_updated_at' => now(),
            ],
            [
                'name' => 'Maria Santos',
                'email' => 'maria.santos@email.com',
                'phone' => '(11) 88888-8888',
                'cpf_cnpj' => '987.654.321-00',
                'address' => 'Av. Principal, 456 - São Paulo, SP',
                'active' => true,
                'credit_limit' => 500.00,
                'credit_used' => 150.00,
                'credit_notes' => 'Cliente regular - Limite padrão',
                'credit_limit_updated_at' => now(),
            ],
            [
                'name' => 'Pedro Oliveira',
                'email' => 'pedro.oliveira@email.com',
                'phone' => '(11) 77777-7777',
                'cpf_cnpj' => '456.789.123-00',
                'address' => 'Rua do Comércio, 789 - São Paulo, SP',
                'active' => true,
                'credit_limit' => 2000.00,
                'credit_used' => 1800.00,
                'credit_notes' => 'Empresa parceira - Limite alto',
                'credit_limit_updated_at' => now(),
            ],
            [
                'name' => 'Ana Costa',
                'email' => 'ana.costa@email.com',
                'phone' => '(11) 66666-6666',
                'cpf_cnpj' => '789.123.456-00',
                'address' => 'Av. Paulista, 1000 - São Paulo, SP',
                'active' => true,
                'credit_limit' => 0.00,
                'credit_used' => 0.00,
                'credit_notes' => 'Pagamento apenas à vista',
                'credit_limit_updated_at' => null,
            ],
            [
                'name' => 'Carlos Ferreira',
                'email' => 'carlos.ferreira@email.com',
                'phone' => '(11) 55555-5555',
                'cpf_cnpj' => '321.654.987-00',
                'address' => 'Rua Augusta, 500 - São Paulo, SP',
                'active' => false,
                'credit_limit' => 300.00,
                'credit_used' => 350.00,
                'credit_notes' => 'Cliente bloqueado - Limite excedido',
                'credit_limit_updated_at' => now()->subDays(30),
            ],
            [
                'name' => 'Supermercado Central LTDA',
                'email' => 'compras@centralmarket.com.br',
                'phone' => '(11) 3000-0000',
                'cpf_cnpj' => '12.345.678/0001-90',
                'address' => 'Av. Central, 2000 - São Paulo, SP',
                'active' => true,
                'credit_limit' => 5000.00,
                'credit_used' => 2500.00,
                'credit_notes' => 'Cliente corporativo - Limite especial',
                'credit_limit_updated_at' => now(),
            ],
        ];

        foreach ($customers as $customer) {
            Customer::create($customer);
        }
    }
}
