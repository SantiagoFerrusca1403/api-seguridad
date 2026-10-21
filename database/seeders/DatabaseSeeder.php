<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Creamos un usuario de prueba
        User::factory()->create([
            'name' => 'Admin Seguridad',
            'email' => 'admin@seguridad.com',
            'password' => Hash::make('password123'),
        ]);
    }
}