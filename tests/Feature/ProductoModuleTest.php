<?php

namespace Tests\Feature;

use App\Enums\TipoProducto;
use App\Livewire\Productos\Form;
use App\Livewire\Productos\Index;
use App\Models\Producto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ProductoModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_invitado_es_redirigido_al_login(): void
    {
        $this->get(route('productos.index'))->assertRedirect(route('login'));
    }

    public function test_sin_permiso_recibe_403(): void
    {
        $this->actingAs($this->usuarioCon([]))->get(route('productos.index'))->assertForbidden();
    }

    public function test_ruta_crear_exige_permiso(): void
    {
        $this->actingAs($this->usuarioCon(['ver-producto']))->get(route('productos.create'))->assertForbidden();
        $this->actingAs($this->usuarioCon(['crear-producto']))->get(route('productos.create'))->assertOk();
    }

    public function test_store_comprado_con_costo_y_talles(): void
    {
        Livewire::actingAs($this->usuarioCon(['crear-producto']))
            ->test(Form::class)
            ->set('nombre', 'Camisa')
            ->set('tipo', 'comprado')
            ->set('precio_venta', 150000)
            ->set('precio_compra', 90000)
            ->set('fecha_compra', '2026-07-01')
            ->set('talles', [
                ['id' => null, 'talle' => 'M', 'cantidad' => 10],
                ['id' => null, 'talle' => 'L', 'cantidad' => 5],
            ])
            ->call('save')
            ->assertRedirect(route('productos.index'));

        $p = Producto::firstWhere('nombre', 'Camisa');
        $this->assertSame(TipoProducto::Comprado, $p->tipo);
        $this->assertSame(90000, $p->precio_compra);
        $this->assertCount(2, $p->talles);
        $this->assertSame(10, $p->talles->firstWhere('talle', 'M')->cantidad_disponible);
    }

    public function test_crear_producto_asigna_codigo_barra_generado(): void
    {
        Livewire::actingAs($this->usuarioCon(['crear-producto']))
            ->test(Form::class)
            ->set('nombre', 'Remera')
            ->set('tipo', 'fabricado')
            ->set('precio_venta', 20000)
            ->set('talles', [
                ['id' => null, 'talle' => 'M', 'cantidad' => 5],
                ['id' => null, 'talle' => 'L', 'cantidad' => 5],
            ])
            ->call('save')
            ->assertRedirect(route('productos.index'));

        $p = Producto::firstWhere('nombre', 'Remera');
        $codigos = $p->talles->pluck('codigo_barra');

        $this->assertCount(2, $codigos->unique());
        $codigos->each(fn ($c) => $this->assertMatchesRegularExpression('/^PRD-\d{7}$/', $c));
    }

    public function test_crear_producto_comprado_con_ean_manual_respeta_el_codigo(): void
    {
        Livewire::actingAs($this->usuarioCon(['crear-producto']))
            ->test(Form::class)
            ->set('nombre', 'Pantalón')
            ->set('tipo', 'comprado')
            ->set('precio_venta', 50000)
            ->set('precio_compra', 30000)
            ->set('fecha_compra', '2026-07-01')
            ->set('talles', [
                ['id' => null, 'talle' => 'M', 'cantidad' => 3, 'codigo_barra' => '5901234123457'],
            ])
            ->call('save')
            ->assertRedirect(route('productos.index'));

        $p = Producto::firstWhere('nombre', 'Pantalón');
        $this->assertSame('5901234123457', $p->talles->first()->codigo_barra);
    }

    public function test_codigo_barra_duplicado_falla_validacion(): void
    {
        $existente = Producto::create(['nombre' => 'Otro', 'tipo' => 'fabricado', 'precio_venta' => 1000]);
        $existente->talles()->create(['talle' => 'M', 'cantidad_total' => 1, 'cantidad_disponible' => 1, 'codigo_barra' => '5901234123457']);

        Livewire::actingAs($this->usuarioCon(['crear-producto']))
            ->test(Form::class)
            ->set('nombre', 'Nuevo')
            ->set('tipo', 'fabricado')
            ->set('precio_venta', 1000)
            ->set('talles', [
                ['id' => null, 'talle' => 'M', 'cantidad' => 1, 'codigo_barra' => '5901234123457'],
            ])
            ->call('save')
            ->assertHasErrors(['talles.0.codigo_barra' => 'unique']);
    }

    public function test_ruta_etiquetas_responde_pdf(): void
    {
        $producto = Producto::create(['nombre' => 'Con etiquetas', 'tipo' => 'fabricado', 'precio_venta' => 1000]);
        $producto->talles()->create(['talle' => 'M', 'cantidad_total' => 1, 'cantidad_disponible' => 1, 'codigo_barra' => 'PRD-0000099']);

        $this->actingAs($this->usuarioCon(['ver-producto']))
            ->get(route('productos.etiquetas', $producto))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_fabricado_ignora_costo(): void
    {
        Livewire::actingAs($this->usuarioCon(['crear-producto']))
            ->test(Form::class)
            ->set('nombre', 'Traje a medida')
            ->set('tipo', 'fabricado')
            ->set('precio_venta', 500000)
            ->set('precio_compra', 99999)
            ->set('talles', [['id' => null, 'talle' => 'Único', 'cantidad' => 1]])
            ->call('save')
            ->assertRedirect(route('productos.index'));

        $p = Producto::firstWhere('nombre', 'Traje a medida');
        $this->assertNull($p->precio_compra);
        $this->assertNull($p->fecha_compra);
    }

    public function test_comprado_sin_precio_compra_falla(): void
    {
        Livewire::actingAs($this->usuarioCon(['crear-producto']))
            ->test(Form::class)
            ->set('nombre', 'X')
            ->set('tipo', 'comprado')
            ->set('precio_venta', 1000)
            ->set('talles', [['id' => null, 'talle' => 'M', 'cantidad' => 1]])
            ->call('save')
            ->assertHasErrors(['precio_compra', 'fecha_compra']);

        $this->assertDatabaseCount('productos', 0);
    }

    public function test_update_sincroniza_talles(): void
    {
        $producto = Producto::create(['nombre' => 'P', 'tipo' => 'fabricado', 'precio_venta' => 1000]);
        $tM = $producto->talles()->create(['talle' => 'M', 'cantidad_total' => 5, 'cantidad_disponible' => 5]);
        $tL = $producto->talles()->create(['talle' => 'L', 'cantidad_total' => 3, 'cantidad_disponible' => 3]);

        Livewire::actingAs($this->usuarioCon(['editar-producto']))
            ->test(Form::class, ['producto' => $producto])
            ->set('talles', [
                ['id' => $tM->id, 'talle' => 'M', 'cantidad' => 8],
                ['id' => null, 'talle' => 'XL', 'cantidad' => 2],
            ])
            ->call('save')
            ->assertRedirect(route('productos.index'));

        $producto->refresh()->load('talles');
        $this->assertCount(2, $producto->talles);
        $this->assertSame(8, $producto->talles->firstWhere('talle', 'M')->cantidad_disponible);
        $this->assertModelMissing($tL);
    }

    public function test_toggle_activo_y_eliminar(): void
    {
        $producto = Producto::create(['nombre' => 'P', 'tipo' => 'fabricado', 'precio_venta' => 1000, 'activo_para_venta' => true]);

        Livewire::actingAs($this->usuarioCon(['editar-producto']))
            ->test(Index::class)->call('toggleActivo', $producto->id);
        $this->assertFalse($producto->refresh()->activo_para_venta);

        Livewire::actingAs($this->usuarioCon(['eliminar-producto']))
            ->test(Index::class)->call('eliminar', $producto->id);
        $this->assertSoftDeleted('productos', ['id' => $producto->id]);
    }
}
