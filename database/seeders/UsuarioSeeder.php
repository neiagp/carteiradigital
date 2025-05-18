<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UsuarioSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Sistema',
            'email' => 'admin@admin.com',
            'password' => Hash::make('admin123'),
            'is_admin' => true,
        ]);

        User::create([
            'name' => 'João',
            'email' => 'neiceny@gmail.com',
            'password' => Hash::make('senha123'),
        ]);

        User::create([
            'name' => 'Maria',
            'email' => 'maria@email.com',
            'password' => Hash::make('senha123'),
        ]);
    }
}
