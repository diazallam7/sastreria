<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

abstract class TestCase extends BaseTestCase
{
    /**
     * Crea un usuario con los permisos dados (se crean si no existen).
     *
     * @param  array<int, string>  $permisos
     */
    protected function usuarioCon(array $permisos = []): User
    {
        $user = User::factory()->create();

        foreach ($permisos as $permiso) {
            Permission::findOrCreate($permiso, 'web');
            $user->givePermissionTo($permiso);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return $user;
    }
}
