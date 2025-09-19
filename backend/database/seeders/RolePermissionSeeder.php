<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Criar permissões
        $permissions = [
            // Usuários
            'users.view' => 'Visualizar usuários',
            'users.create' => 'Criar usuários',
            'users.edit' => 'Editar usuários',
            'users.delete' => 'Deletar usuários',
            
            // Produtos
            'products.view' => 'Visualizar produtos',
            'products.create' => 'Criar produtos',
            'products.edit' => 'Editar produtos',
            'products.delete' => 'Deletar produtos',
            
            // Clientes
            'customers.view' => 'Visualizar clientes',
            'customers.create' => 'Criar clientes',
            'customers.edit' => 'Editar clientes',
            'customers.delete' => 'Deletar clientes',
            
            // Pedidos
            'orders.view' => 'Visualizar pedidos',
            'orders.create' => 'Criar pedidos',
            'orders.edit' => 'Editar pedidos',
            'orders.delete' => 'Deletar pedidos',
            
            // Vendas
            'sales.view' => 'Visualizar vendas',
            'sales.create' => 'Criar vendas',
            'sales.edit' => 'Editar vendas',
            'sales.delete' => 'Deletar vendas',
            
            // Contas a receber
            'receivables.view' => 'Visualizar contas a receber',
            'receivables.create' => 'Criar contas a receber',
            'receivables.edit' => 'Editar contas a receber',
            'receivables.delete' => 'Deletar contas a receber',
            
            // Configurações
            'settings.view' => 'Visualizar configurações',
            'settings.edit' => 'Editar configurações',
            
            // Relatórios
            'reports.view' => 'Visualizar relatórios',
            'reports.export' => 'Exportar relatórios',
            
            // Vasilhames
            'vasilhames.view' => 'Visualizar vasilhames',
            'vasilhames.manage' => 'Gerenciar vasilhames',
        ];

        foreach ($permissions as $name => $description) {
            Permission::firstOrCreate([
                'name' => $name,
                'description' => $description,
                'display_name' => $description
            ]);
        }

        // Criar roles
        $adminRole = Role::firstOrCreate([
            'name' => 'admin',
            'description' => 'Administrador do sistema',
            'display_name' => 'Administrador'
        ]);

        $managerRole = Role::firstOrCreate([
            'name' => 'manager',
            'description' => 'Gerente',
            'display_name' => 'Gerente'
        ]);

        $sellerRole = Role::firstOrCreate([
            'name' => 'seller',
            'description' => 'Vendedor',
            'display_name' => 'Vendedor'
        ]);

        $operatorRole = Role::firstOrCreate([
            'name' => 'operator',
            'description' => 'Operador',
            'display_name' => 'Operador'
        ]);

        // Atribuir todas as permissões ao admin
        $allPermissions = Permission::all();
        $adminRole->permissions()->sync($allPermissions->pluck('id'));

        // Permissões do gerente (tudo exceto gerenciar usuários)
        $managerPermissions = $allPermissions->whereNotIn('name', [
            'users.create',
            'users.delete'
        ]);
        $managerRole->permissions()->sync($managerPermissions->pluck('id'));

        // Permissões do vendedor
        $sellerPermissions = $allPermissions->whereIn('name', [
            'products.view',
            'customers.view',
            'customers.create',
            'customers.edit',
            'orders.view',
            'orders.create',
            'orders.edit',
            'sales.view',
            'sales.create',
            'sales.edit',
            'receivables.view',
            'vasilhames.view',
            'vasilhames.manage',
            'reports.view'
        ]);
        $sellerRole->permissions()->sync($sellerPermissions->pluck('id'));

        // Permissões do operador
        $operatorPermissions = $allPermissions->whereIn('name', [
            'products.view',
            'customers.view',
            'orders.view',
            'sales.view',
            'receivables.view',
            'vasilhames.view',
            'reports.view'
        ]);
        $operatorRole->permissions()->sync($operatorPermissions->pluck('id'));

        // Criar usuário admin padrão se não existir
        $adminUser = User::where('username', 'admin')->orWhere('email', 'admin@clickvenda.com')->first();
        if (!$adminUser) {
            $adminUser = User::create([
                'name' => 'Administrador',
                'username' => 'admin',
                'email' => 'admin@clickvenda.com',
                'password' => Hash::make('admin123'),
                'email_verified_at' => now(),
            ]);
        } else {
            // Atualizar usuário existente com username se não tiver
            if (!$adminUser->username) {
                $adminUser->update(['username' => 'admin']);
            }
        }

        // Atribuir role admin ao usuário
        $adminUser->roles()->sync([$adminRole->id]);

        $this->command->info('Roles e permissões criadas com sucesso!');
        $this->command->info('Usuário admin criado: admin / admin123');
    }
}
