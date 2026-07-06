<?php

namespace Tests\Feature;

use App\Livewire\Configuraciones\Index as ConfigIndex;
use App\Livewire\Gastos\Form as GastoForm;
use App\Livewire\Gastos\Index as GastoIndex;
use App\Livewire\Roles\Form as RoleForm;
use App\Livewire\Usuarios\Form as UsuarioForm;
use App\Models\Configuracion;
use App\Models\GastoVario;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminModulesTest extends TestCase
{
    use RefreshDatabase;

    public function test_configuracion_guarda_valor(): void
    {
        $config = Configuracion::create(['nombre' => 'multa', 'valor' => 10000]);

        Livewire::actingAs(User::factory()->create())
            ->test(ConfigIndex::class)
            ->set("valores.{$config->id}", '25000')
            ->call('save');

        $this->assertSame(25000, (int) $config->refresh()->valor);
    }

    public function test_gasto_crear_y_eliminar(): void
    {
        Livewire::actingAs(User::factory()->create())
            ->test(GastoForm::class)
            ->set('nombre_gasto', 'Luz')
            ->set('monto', '30000')
            ->call('save')
            ->assertRedirect(route('gastos-varios.index'));

        $gasto = GastoVario::firstWhere('nombre_gasto', 'Luz');
        $this->assertSame(30000, (int) $gasto->monto);

        Livewire::actingAs(User::factory()->create())
            ->test(GastoIndex::class)->call('eliminar', $gasto->id);

        $this->assertDatabaseMissing('gastos_varios', ['id' => $gasto->id]);
    }

    public function test_usuario_creado_sin_doble_hash(): void
    {
        Role::findOrCreate('cajero', 'web');

        Livewire::actingAs($this->usuarioCon(['crear-user']))
            ->test(UsuarioForm::class)
            ->set('name', 'Nuevo')
            ->set('email', 'nuevo@x.com')
            ->set('password', 'secret123')
            ->set('role', 'cajero')
            ->call('save')
            ->assertRedirect(route('users.index'));

        $creado = User::firstWhere('email', 'nuevo@x.com');
        $this->assertTrue($creado->hasRole('cajero'));
        // Si estuviera doble-hasheado, este login fallaría.
        $this->assertTrue(Auth::validate(['email' => 'nuevo@x.com', 'password' => 'secret123']));
    }

    public function test_rol_creado_con_permisos(): void
    {
        Permission::findOrCreate('ver-cliente', 'web');

        Livewire::actingAs($this->usuarioCon(['crear-role']))
            ->test(RoleForm::class)
            ->set('name', 'vendedor')
            ->set('permisos', ['ver-cliente'])
            ->call('save')
            ->assertRedirect(route('roles.index'));

        $rol = Role::findByName('vendedor');
        $this->assertTrue($rol->hasPermissionTo('ver-cliente'));
    }
}
