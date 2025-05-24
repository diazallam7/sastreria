<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permisos = [
            //cliente
            'ver-cliente',
            'crear-cliente',
            'editar-cliente',
            'eliminar-cliente',
            //compra
            'ver-compra',
            'crear-compra',
            'mostrar-compra',
            'eliminar-compra',
            //producto
            'ver-producto',
            'crear-producto',
            'editar-producto',
            'eliminar-producto',
            //venta
            'ver-venta',
            'crear-venta',
            'mostrar-venta',
            'eliminar-venta',
            //roles
            'ver-role',
            'crear-role',
            'editar-role',
            'eliminar-role',
            //user
            'ver-user',
            'crear-user',
            'editar-user',
            'eliminar-user',
        ];

        foreach($permisos as $permiso){
            Permission::create(['name'=>$permiso]);
        }
    }
}
