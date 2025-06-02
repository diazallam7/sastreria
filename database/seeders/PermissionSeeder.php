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
            'editar-compra',
            'mostrar-compra',
            'eliminar-compra',
            //producto
            'ver-stock-alquiler',
            'crear-stock-alquiler',
            'editar-stock-alquiler',
            'eliminar-stock-alquiler',
            'mostrar-stock-alquiler',
            //venta
            'ver-venta',
            'crear-venta',
            'editar-venta',
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
            //alquiler 
            'ver-alquiler',
            'crear-alquiler',
            'editar-alquiler',
            'eliminar-alquiler',
            'mostrar-alquiler',
            //configuracion
            'ver-configuracion',
            'editar-configuracion',
            'ver-cierre-caja',
            'crear-cierre-caja',
            'editar-cierre-caja',
            'exportar-cierre-caja',
            'ver-cierre-caja-semanal',
            'ver-cierre-caja-mensual'
            
        ];

        foreach($permisos as $permiso){
            Permission::create(['name'=>$permiso]);
        }

    }
}
