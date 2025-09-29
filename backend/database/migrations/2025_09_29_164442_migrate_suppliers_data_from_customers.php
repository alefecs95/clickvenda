<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Migrar fornecedores da tabela customers para suppliers
        $suppliers = DB::table('customers')
            ->join('payable_payments', 'customers.id', '=', 'payable_payments.supplier_id')
            ->select('customers.*')
            ->distinct()
            ->get();

        foreach ($suppliers as $supplier) {
            // Inserir na tabela suppliers
            $supplierId = DB::table('suppliers')->insertGetId([
                'name' => $supplier->name,
                'email' => $supplier->email,
                'phone' => $supplier->phone,
                'cpf_cnpj' => $supplier->cpf_cnpj,
                'address' => $supplier->address,
                'city' => $supplier->city,
                'state' => $supplier->state,
                'zip_code' => $supplier->zip_code,
                'active' => $supplier->active,
                'created_at' => $supplier->created_at,
                'updated_at' => $supplier->updated_at,
            ]);

            // Atualizar payable_payments para referenciar o novo supplier
            DB::table('payable_payments')
                ->where('supplier_id', $supplier->id)
                ->update(['supplier_id' => $supplierId]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverter: migrar suppliers de volta para customers
        $suppliers = DB::table('suppliers')->get();

        foreach ($suppliers as $supplier) {
            // Verificar se já existe um customer com o mesmo nome/documento
            $existingCustomer = DB::table('customers')
                ->where('name', $supplier->name)
                ->where('cpf_cnpj', $supplier->cpf_cnpj)
                ->first();

            if (!$existingCustomer) {
                // Criar customer se não existir
                $customerId = DB::table('customers')->insertGetId([
                    'name' => $supplier->name,
                    'email' => $supplier->email,
                    'phone' => $supplier->phone,
                    'cpf_cnpj' => $supplier->cpf_cnpj,
                    'address' => $supplier->address,
                    'city' => $supplier->city,
                    'state' => $supplier->state,
                    'zip_code' => $supplier->zip_code,
                    'credit_limit' => 0,
                    'current_credit' => 0,
                    'active' => $supplier->active,
                    'created_at' => $supplier->created_at,
                    'updated_at' => $supplier->updated_at,
                ]);
            } else {
                $customerId = $existingCustomer->id;
            }

            // Atualizar payable_payments para referenciar o customer
            DB::table('payable_payments')
                ->where('supplier_id', $supplier->id)
                ->update(['supplier_id' => $customerId]);
        }

        // Limpar tabela suppliers
        DB::table('suppliers')->truncate();
    }
};
