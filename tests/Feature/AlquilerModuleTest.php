<?php

namespace Tests\Feature;

use App\Enums\EstadoAlquiler;
use App\Livewire\Alquileres\Form;
use App\Livewire\Alquileres\Index;
use App\Livewire\Clientes\CrearRapido;
use App\Models\Alquiler;
use App\Models\Cliente;
use App\Models\Devolucion;
use App\Models\StockAlquiler;
use App\Models\TalleStock;
use App\Services\AlquilerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AlquilerModuleTest extends TestCase
{
    use RefreshDatabase;

    private function prendaConStock(int $disp = 5): TalleStock
    {
        $item = StockAlquiler::create(['codigo' => 'P' . uniqid(), 'nombre' => 'Prenda', 'precio_alquiler' => 50000]);

        return $item->talles()->create([
            'talle' => 'M', 'cantidad_total' => $disp, 'cantidad_disponible' => $disp, 'cantidad_alquilada' => 0,
        ]);
    }

    private function alquilerActivo(TalleStock $talle, int $cantidad = 2): Alquiler
    {
        return app(AlquilerService::class)->crear([
            'cliente_id' => Cliente::create(['nombre' => 'C'])->id,
            'fecha_inicio' => now(), 'fecha_fin' => now()->addDays(2),
            'costo_total' => 150000, 'garantia' => 200000,
        ], [['stock_id' => $talle->stock_id, 'talle_id' => $talle->id, 'cantidad' => $cantidad]]);
    }

    public function test_invitado_es_redirigido_al_login(): void
    {
        $this->get(route('alquileres.index'))->assertRedirect(route('login'));
    }

    public function test_sin_permiso_recibe_403(): void
    {
        $this->actingAs($this->usuarioCon([]))->get(route('alquileres.index'))->assertForbidden();
    }

    public function test_crear_alquiler_mueve_stock(): void
    {
        $cliente = Cliente::create(['nombre' => 'C']);
        $talle = $this->prendaConStock(5);

        Livewire::actingAs($this->usuarioCon(['crear-alquiler']))
            ->test(Form::class)
            ->set('cliente_id', $cliente->id)
            ->set('garantia', 200000)
            ->set('fecha_inicio', now()->toDateString())
            ->set('fecha_fin', now()->addDays(3)->toDateString())
            ->set('stockSel', $talle->stock_id)
            ->set('talleSel', $talle->id)
            ->set('cantidadSel', 2)
            ->call('agregarPrenda')
            ->assertCount('prendas', 1)
            ->assertSet('costo_total', '100000') // auto-suma: precio 50000 × 2
            ->call('save')
            ->assertRedirect(route('alquileres.index'));

        $alquiler = Alquiler::first();
        $this->assertSame(EstadoAlquiler::Activo, $alquiler->estado);
        $this->assertSame(100000, $alquiler->costo_total);

        $talle->refresh();
        $this->assertSame(3, $talle->cantidad_disponible);
        $this->assertSame(2, $talle->cantidad_alquilada);
    }

    public function test_agregar_prenda_valida_stock(): void
    {
        $talle = $this->prendaConStock(1);

        Livewire::actingAs($this->usuarioCon(['crear-alquiler']))
            ->test(Form::class)
            ->set('stockSel', $talle->stock_id)
            ->set('talleSel', $talle->id)
            ->set('cantidadSel', 5)
            ->call('agregarPrenda')
            ->assertHasErrors('cantidadSel')
            ->assertCount('prendas', 0);
    }

    public function test_devolver_completa_y_registra_devolucion(): void
    {
        $talle = $this->prendaConStock(5);
        $alquiler = $this->alquilerActivo($talle, 2);
        $this->assertSame(3, $talle->refresh()->cantidad_disponible);

        Livewire::actingAs($this->usuarioCon(['editar-alquiler']))
            ->test(Index::class)->call('devolver', $alquiler->id);

        $this->assertSame(EstadoAlquiler::Completado, $alquiler->refresh()->estado);
        $this->assertSame(5, $talle->refresh()->cantidad_disponible);
        $this->assertSame(1, Devolucion::where('alquiler_id', $alquiler->id)->count());
    }

    public function test_doble_devolucion_no_duplica_stock(): void
    {
        $talle = $this->prendaConStock(5);
        $alquiler = $this->alquilerActivo($talle, 2);

        $user = $this->usuarioCon(['editar-alquiler']);
        Livewire::actingAs($user)->test(Index::class)->call('devolver', $alquiler->id);
        Livewire::actingAs($user)->test(Index::class)->call('devolver', $alquiler->id);

        $this->assertSame(5, $talle->refresh()->cantidad_disponible); // no se duplica
        $this->assertSame(1, Devolucion::count());
    }

    public function test_no_se_edita_alquiler_completado(): void
    {
        $cliente = Cliente::create(['nombre' => 'C']);
        $alquiler = Alquiler::create([
            'cliente_id' => $cliente->id, 'fecha_inicio' => now(), 'fecha_fin' => now()->addDay(),
            'costo_total' => 1000, 'garantia' => 0, 'estado' => EstadoAlquiler::Completado,
        ]);

        Livewire::actingAs($this->usuarioCon(['editar-alquiler']))
            ->test(Form::class, ['alquiler' => $alquiler])
            ->assertRedirect(route('alquileres.index'));
    }

    public function test_cliente_rapido_se_crea_y_queda_seleccionable(): void
    {
        $user = $this->usuarioCon(['crear-alquiler', 'crear-cliente']);

        Livewire::actingAs($user)->test(CrearRapido::class)
            ->call('abrir')
            ->assertSet('abierto', true)
            ->set('nombre', 'Cliente Rápido')
            ->call('guardar')
            ->assertSet('abierto', false)
            ->assertDispatched('cliente-creado');

        $cliente = Cliente::firstWhere('nombre', 'Cliente Rápido');
        $this->assertNotNull($cliente);

        Livewire::actingAs($user)->test(Form::class)
            ->call('seleccionarCliente', $cliente->id)
            ->assertSet('cliente_id', (string) $cliente->id);
    }

    public function test_anular_restaura_stock_y_soft_delete(): void
    {
        $talle = $this->prendaConStock(5);
        $alquiler = $this->alquilerActivo($talle, 2);

        Livewire::actingAs($this->usuarioCon(['eliminar-alquiler']))
            ->test(Index::class)->call('anular', $alquiler->id);

        $this->assertSoftDeleted('alquileres', ['id' => $alquiler->id]);
        $this->assertSame(5, $talle->refresh()->cantidad_disponible);
    }
}
