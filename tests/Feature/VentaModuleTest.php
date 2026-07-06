<?php

namespace Tests\Feature;

use App\Livewire\Ventas\Form;
use App\Livewire\Ventas\Index;
use App\Models\Cliente;
use App\Models\Producto;
use App\Models\ProductoTalle;
use App\Models\Venta;
use App\Services\VentaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class VentaModuleTest extends TestCase
{
    use RefreshDatabase;

    private function productoConStock(int $precio = 100000, int $stock = 10): ProductoTalle
    {
        $producto = Producto::create([
            'nombre' => 'Producto', 'tipo' => 'fabricado',
            'precio_venta' => $precio, 'activo_para_venta' => true,
        ]);

        return $producto->talles()->create([
            'talle' => 'M', 'cantidad_total' => $stock, 'cantidad_disponible' => $stock,
        ]);
    }

    public function test_invitado_es_redirigido_al_login(): void
    {
        $this->get(route('ventas.index'))->assertRedirect(route('login'));
    }

    public function test_sin_permiso_recibe_403(): void
    {
        $this->actingAs($this->usuarioCon([]))->get(route('ventas.index'))->assertForbidden();
    }

    public function test_agregar_item_y_registrar_venta(): void
    {
        $cliente = Cliente::create(['nombre' => 'C']);
        $talle = $this->productoConStock(precio: 100000, stock: 10);

        Livewire::actingAs($this->usuarioCon(['crear-venta']))
            ->test(Form::class)
            ->set('cliente_id', $cliente->id)
            ->set('productoSel', $talle->producto_id)
            ->set('talleSel', $talle->id)
            ->set('cantidadSel', 3)
            ->call('agregarItem')
            ->assertCount('items', 1)
            ->call('save')
            ->assertRedirect(route('ventas.index'));

        $venta = Venta::first();
        $this->assertSame(300000, $venta->precio_total);         // 100000 * 3 (precio del servidor)
        $this->assertSame('Producto', $venta->detalles->first()->nombre_producto); // snapshot

        $talle->refresh();
        $this->assertSame(7, $talle->cantidad_disponible);
        $this->assertSame(3, $talle->cantidad_vendida);
    }

    public function test_agregar_item_valida_stock(): void
    {
        $talle = $this->productoConStock(stock: 2);

        Livewire::actingAs($this->usuarioCon(['crear-venta']))
            ->test(Form::class)
            ->set('productoSel', $talle->producto_id)
            ->set('talleSel', $talle->id)
            ->set('cantidadSel', 5)
            ->call('agregarItem')
            ->assertHasErrors('cantidadSel')
            ->assertCount('items', 0);
    }

    public function test_stock_insuficiente_en_save_revierte(): void
    {
        $cliente = Cliente::create(['nombre' => 'C']);
        $talle = $this->productoConStock(stock: 2);

        // Ítem manipulado con cantidad > stock: el servicio debe rechazar y revertir.
        Livewire::actingAs($this->usuarioCon(['crear-venta']))
            ->test(Form::class)
            ->set('cliente_id', $cliente->id)
            ->set('items', [[
                'producto_talle_id' => $talle->id, 'nombre' => 'x', 'talle' => 'M', 'precio' => 100000, 'cantidad' => 99,
            ]])
            ->call('save')
            ->assertHasErrors('items');

        $this->assertDatabaseCount('ventas', 0);
        $this->assertSame(2, $talle->refresh()->cantidad_disponible);
    }

    public function test_cliente_creado_queda_seleccionado(): void
    {
        $cliente = Cliente::create(['nombre' => 'Nuevo']);

        Livewire::actingAs($this->usuarioCon(['crear-venta']))
            ->test(Form::class)
            ->call('seleccionarCliente', $cliente->id)
            ->assertSet('cliente_id', (string) $cliente->id);
    }

    public function test_anular_restaura_stock_y_soft_delete(): void
    {
        $cliente = Cliente::create(['nombre' => 'C']);
        $talle = $this->productoConStock(stock: 10);

        $venta = app(VentaService::class)->crear(
            ['cliente_id' => $cliente->id, 'fecha_venta' => now()],
            [['producto_talle_id' => $talle->id, 'cantidad' => 4]],
        );
        $this->assertSame(6, $talle->refresh()->cantidad_disponible);

        Livewire::actingAs($this->usuarioCon(['eliminar-venta']))
            ->test(Index::class)->call('anular', $venta->id);

        $this->assertSoftDeleted('ventas', ['id' => $venta->id]);
        $this->assertSame(10, $talle->refresh()->cantidad_disponible);
        $this->assertSame(0, $talle->cantidad_vendida);
    }
}
