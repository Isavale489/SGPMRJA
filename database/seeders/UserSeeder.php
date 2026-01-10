<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear usuario administrador
        \App\Models\User::create([
            'name' => 'Administrador',
            'email' => 'admin@gmail.com',
            'password' => bcrypt('sistemas'),
            'role' => 'Administrador',
            'avatar' => null,
        ]);

        // Crear usuario supervisor
        \App\Models\User::create([
            'name' => 'Supervisor',
            'email' => 'supervisor@gmail.com',
            'password' => bcrypt('sistemas'),
            'role' => 'Supervisor',
        ]);
    }
}
