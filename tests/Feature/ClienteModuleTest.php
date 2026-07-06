<?php

namespace Tests\Feature;

use App\Enums\TipoMedida;
use App\Livewire\Clientes\Form;
use App\Livewire\Clientes\Index;
use App\Models\Cliente;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ClienteModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_invitado_es_redirigido_al_login(): void
    {
        $this->get(route('clientes.index'))->assertRedirect(route('login'));
    }

    public function test_usuario_sin_permiso_recibe_403(): void
    {
        $this->actingAs($this->usuarioCon([]))->get(route('clientes.index'))->assertForbidden();
    }

    public function test_ruta_crear_exige_permiso(): void
    {
        $this->actingAs($this->usuarioCon(['ver-cliente']))->get(route('clientes.create'))->assertForbidden();
        $this->actingAs($this->usuarioCon(['crear-cliente']))->get(route('clientes.create'))->assertOk();
    }

    public function test_store_crea_cliente_y_registra_medidas(): void
    {
        Livewire::actingAs($this->usuarioCon(['crear-cliente']))
            ->test(Form::class)
            ->set('nombre', 'Juan Pérez')
            ->set('documento', '1234567')
            ->set('correo', 'juan@x.com')
            ->set('medidas.saco.largo', 98.5)
            ->set('medidas.saco.pecho', 110)
            ->set('medidas.pantalon.cintura', 88)
            ->call('save')
            ->assertRedirect(route('clientes.index'));

        $cliente = Cliente::firstWhere('documento', '1234567');
        $this->assertNotNull($cliente);
        $this->assertCount(2, $cliente->medidasVigentes); // saco + pantalon
        $this->assertEquals(98.5, $cliente->medidaVigente(TipoMedida::Saco)->first()->medidas['largo']);
    }

    public function test_documento_duplicado_es_rechazado(): void
    {
        Cliente::create(['nombre' => 'Existente', 'documento' => '999']);

        Livewire::actingAs($this->usuarioCon(['crear-cliente']))
            ->test(Form::class)
            ->set('nombre', 'Otro')
            ->set('documento', '999')
            ->call('save')
            ->assertHasErrors('documento');

        $this->assertSame(1, Cliente::where('documento', '999')->count());
    }

    public function test_update_versiona_medidas_solo_cuando_cambian(): void
    {
        $user = $this->usuarioCon(['editar-cliente']);
        $cliente = Cliente::create(['nombre' => 'Ana', 'documento' => 'A1']);
        $cliente->medidas()->create(['tipo' => 'saco', 'medidas' => ['largo' => 90], 'vigente' => true]);

        // Sin cambio → no nueva versión
        Livewire::actingAs($user)->test(Form::class, ['cliente' => $cliente])
            ->set('nombre', 'Ana María')
            ->call('save');
        $this->assertSame(1, $cliente->medidas()->where('tipo', 'saco')->count());

        // Con cambio → archiva y crea nueva
        Livewire::actingAs($user)->test(Form::class, ['cliente' => $cliente->fresh()])
            ->set('medidas.saco.largo', 95)
            ->call('save');

        $this->assertSame(2, $cliente->medidas()->where('tipo', 'saco')->count());
        $this->assertSame(1, $cliente->medidas()->where('tipo', 'saco')->where('vigente', true)->count());
        $this->assertEquals(95, $cliente->medidaVigente(TipoMedida::Saco)->first()->medidas['largo']);
    }

    public function test_eliminar_hace_soft_delete(): void
    {
        $user = $this->usuarioCon(['eliminar-cliente']);
        $cliente = Cliente::create(['nombre' => 'Borrar', 'documento' => 'B1']);

        Livewire::actingAs($user)->test(Index::class)
            ->call('eliminar', $cliente->id);

        $this->assertSoftDeleted('clientes', ['id' => $cliente->id]);
    }
}
