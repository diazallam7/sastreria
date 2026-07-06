<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Roles y su matriz de permisos.
     * - Administrador: todos.
     * - Caja (cajero): operativa diaria (clientes, productos, ventas, alquileres,
     *   reservas, devoluciones, stock, ver cierre). SIN administración de usuarios/
     *   roles/configuración, sin eliminar catálogos, sin reportes semanal/mensual.
     *
     * Requiere PermissionSeeder ejecutado antes.
     */
    public function run(): void
    {
        $admin = Role::findOrCreate('administrador', 'web');
        $admin->syncPermissions(Permission::all());

        $caja = Role::findOrCreate('Caja', 'web');
        $caja->syncPermissions([
            'ver-cliente', 'crear-cliente', 'editar-cliente',
            'ver-producto', 'crear-producto', 'editar-producto',
            'ver-stock-alquiler', 'crear-stock-alquiler', 'editar-stock-alquiler',
            'ver-venta', 'crear-venta', 'editar-venta', 'eliminar-venta',
            'ver-alquiler', 'crear-alquiler', 'editar-alquiler', 'eliminar-alquiler',
            'ver-reserva', 'crear-reserva', 'editar-reserva', 'eliminar-reserva',
            'ver-devolucion', 'crear-devolucion',
            'ver-cierre-caja',
        ]);
    }
}
