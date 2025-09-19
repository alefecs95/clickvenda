<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'Administrador',
                'username' => 'admin',
                'password' => Hash::make('123456'),
                'cpf' => '123.456.789-00',
                'is_admin' => true,
                'status' => 'ok',
                'terms_accepted' => true,
            ],
            [
                'name' => 'Vendedor',
                'username' => 'vendedor',
                'password' => Hash::make('123456'),
                'cpf' => '987.654.321-00',
                'is_admin' => false,
                'status' => 'ok',
                'terms_accepted' => true,
            ],
        ];

        foreach ($users as $user) {
            User::create($user);
        }
    }
}
