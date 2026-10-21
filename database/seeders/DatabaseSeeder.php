<?php

namespace Database\Seeders;

use App\Models\Documento;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::updateOrCreate(['email' => 'admin@seguridad.com'], [
            'name' => 'Admin Seguridad',
            'password' => Hash::make('password123'),
            'permissions' => ['catalogo:read'],
        ]);

        $otherUser = User::updateOrCreate(['email' => 'usuario@seguridad.com'], [
            'name' => 'Otro usuario',
            'password' => Hash::make('password123'),
            'permissions' => [],
        ]);

        Documento::updateOrCreate(['id' => 1], [
            'user_id' => $admin->id,
            'titulo' => 'Documento propio de prueba',
            'contenido' => 'Contenido visible para Admin Seguridad.',
        ]);

        Documento::updateOrCreate(['id' => 2], [
            'user_id' => $otherUser->id,
            'titulo' => 'Documento de otro usuario',
            'contenido' => 'Este contenido no debe ser accesible para Admin Seguridad.',
        ]);
    }
}
