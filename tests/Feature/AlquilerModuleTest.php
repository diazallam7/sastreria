<?php

namespace Tests\Feature;

use App\Enums\EstadoAlquiler;
use App\Enums\EstadoUnidad;
use App\Livewire\Alquileres\Form;
use App\Livewire\Alquileres\Index;
use App\Livewire\Clientes\CrearRapido;
use App\Models\Alquiler;
use App\Models\Cliente;
use App\Models\Devolucion;
use App\Models\StockAlquiler;
use App\Models\TalleStock;
use App\Services\AlquilerService;
use App\Services\DevolucionService;
use App\Services\StockAlquilerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class AlquilerModuleTest extends TestCase
{
    use RefreshDatabase;

    private function prendaConStock(int $disp = 5): TalleStock
    {
        $item = app(StockAlquilerService::class)->guardar(
            new StockAlquiler,
            ['codigo' => 'P'.uniqid(), 'nombre' => 'Prenda', 'precio_alquiler' => 50000],
            [['id' => null, 'talle' => 'M', 'cantidad' => $disp]],
        );

        return $item->talles()->first();
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

    /** Invariante central de la Fase 2: alquilar y devolver deja contadores y unidades exactamente como estaban. */
    public function test_invariante_alquilar_y_devolver_deja_todo_igual(): void
    {
        $talle = $this->prendaConStock(5);
        $disponiblesAntes = $talle->unidades()->disponibles()->count();

        $alquiler = $this->alquilerActivo($talle, 3);
        app(DevolucionService::class)->procesar($alquiler);

        $talle->refresh();
        $this->assertSame(5, $talle->cantidad_disponible);
        $this->assertSame(0, $talle->cantidad_alquilada);
        $this->assertSame($disponiblesAntes, $talle->unidades()->disponibles()->count());
        $this->assertSame(0, $talle->unidades()->where('estado', EstadoUnidad::Alquilada->value)->count());
    }

    /**
     * No hay forma de correr dos transacciones en paralelo dentro de un único test PHPUnit
     * (RefreshDatabase envuelve todo en una transacción sobre la misma conexión), así que esto
     * verifica el invariante en el caso secuencial: una vez que la única unidad disponible se
     * asigna, un segundo intento de alquilarla debe fallar (nunca se alquila la misma unidad dos veces).
     */
    public function test_no_se_puede_alquilar_dos_veces_la_misma_unidad(): void
    {
        $talle = $this->prendaConStock(1);
        $this->alquilerActivo($talle, 1);

        $this->expectException(ValidationException::class);
        $this->alquilerActivo($talle, 1);
    }

    public function test_query_param_scan_agrega_unidad_al_montar(): void
    {
        $talle = $this->prendaConStock(3);
        $unidad = $talle->unidades()->disponibles()->first();

        Livewire::withQueryParams(['scan' => $unidad->codigo])
            ->actingAs($this->usuarioCon(['crear-alquiler']))
            ->test(Form::class)
            ->assertHasNoErrors()
            ->assertCount('prendas', 1)
            ->assertSet('prendas.0.unidad_ids', [$unidad->id]);
    }

    public function test_escanear_unidad_la_agrega_al_carrito(): void
    {
        $talle = $this->prendaConStock(3);
        $unidad = $talle->unidades()->disponibles()->first();

        Livewire::actingAs($this->usuarioCon(['crear-alquiler']))
            ->test(Form::class)
            ->call('escanear', $unidad->codigo)
            ->assertHasNoErrors()
            ->assertCount('prendas', 1)
            ->assertSet('prendas.0.talle_id', $talle->id)
            ->assertSet('prendas.0.cantidad', 1)
            ->assertSet('prendas.0.unidad_ids', [$unidad->id]);
    }

    public function test_escanear_misma_unidad_dos_veces_agrega_error(): void
    {
        $talle = $this->prendaConStock(3);
        $unidad = $talle->unidades()->disponibles()->first();

        Livewire::actingAs($this->usuarioCon(['crear-alquiler']))
            ->test(Form::class)
            ->call('escanear', $unidad->codigo)
            ->call('escanear', $unidad->codigo)
            ->assertHasErrors('escaneo')
            ->assertCount('prendas', 1)
            ->assertSet('prendas.0.cantidad', 1);
    }

    public function test_escanear_codigo_no_alquiler_agrega_error(): void
    {
        Livewire::actingAs($this->usuarioCon(['crear-alquiler']))
            ->test(Form::class)
            ->call('escanear', 'PRD-0000001')
            ->assertHasErrors('escaneo')
            ->assertCount('prendas', 0);
    }

    public function test_guardar_con_unidad_escaneada_asigna_esa_unidad_exacta(): void
    {
        $talle = $this->prendaConStock(3);
        $unidad = $talle->unidades()->disponibles()->first();
        $cliente = Cliente::create(['nombre' => 'C']);

        Livewire::actingAs($this->usuarioCon(['crear-alquiler']))
            ->test(Form::class)
            ->set('cliente_id', $cliente->id)
            ->set('garantia', 100000)
            ->set('fecha_inicio', now()->toDateString())
            ->set('fecha_fin', now()->addDays(2)->toDateString())
            ->call('escanear', $unidad->codigo)
            ->call('save')
            ->assertRedirect(route('alquileres.index'));

        $unidad->refresh();
        $this->assertSame(EstadoUnidad::Alquilada, $unidad->estado);

        $alquiler = Alquiler::first();
        $this->assertTrue($alquiler->unidades->contains('id', $unidad->id));
    }
}
