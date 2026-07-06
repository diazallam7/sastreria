<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Usuarios base. Requiere RoleSeeder ejecutado antes (roles administrador/Caja).
     * Contraseña por defecto: "password" (cambiar tras el primer login).
     */
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@medina.com'],
            ['name' => 'Administrador', 'password' => 'password'],
        );
        $admin->assignRole('administrador');

        $caja = User::firstOrCreate(
            ['email' => 'caja@medina.com'],
            ['name' => 'Caja', 'password' => 'password'],
        );
        $caja->assignRole('Caja');

        // Usuario oculto de soporte/dev: no se lista en la UI, pero puede iniciar sesión.
        $dev = User::firstOrCreate(
            ['email' => 'dev@medina.com'],
            ['name' => 'Soporte', 'password' => 'Admin1240', 'oculto' => true],
        );
        $dev->forceFill(['oculto' => true])->save();
        $dev->assignRole('administrador');
    }
}
