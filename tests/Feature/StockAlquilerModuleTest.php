<?php

namespace Tests\Feature;

use App\Enums\EstadoUnidad;
use App\Livewire\StockAlquiler\Form;
use App\Livewire\StockAlquiler\Index;
use App\Models\Cliente;
use App\Models\StockAlquiler;
use App\Models\TalleStock;
use App\Services\AlquilerService;
use App\Services\StockAlquilerService;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class StockAlquilerModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_invitado_es_redirigido_al_login(): void
    {
        $this->get(route('stock.alquiler.index'))->assertRedirect(route('login'));
    }

    public function test_sin_permiso_recibe_403(): void
    {
        $this->actingAs($this->usuarioCon([]))->get(route('stock.alquiler.index'))->assertForbidden();
    }

    public function test_store_crea_prenda_con_talles(): void
    {
        Livewire::actingAs($this->usuarioCon(['crear-stock-alquiler']))
            ->test(Form::class)
            ->set('codigo', 'SACO-001')
            ->set('nombre', 'Saco negro')
            ->set('precio_alquiler', 80000)
            ->set('talles', [
                ['id' => null, 'talle' => 'M', 'cantidad' => 3],
                ['id' => null, 'talle' => 'L', 'cantidad' => 2],
            ])
            ->call('save')
            ->assertRedirect(route('stock.alquiler.index'));

        $item = StockAlquiler::firstWhere('codigo', 'SACO-001');
        $this->assertSame(80000, $item->precio_alquiler);
        $this->assertCount(2, $item->talles);
        $this->assertSame(3, $item->talles->firstWhere('talle', 'M')->cantidad_disponible);
    }

    public function test_codigo_vacio_se_autogenera(): void
    {
        Livewire::actingAs($this->usuarioCon(['crear-stock-alquiler']))
            ->test(Form::class)
            ->set('nombre', 'Saco sin código')
            ->set('precio_alquiler', 50000)
            ->set('talles', [['id' => null, 'talle' => 'M', 'cantidad' => 1]])
            ->call('save')
            ->assertRedirect(route('stock.alquiler.index'));

        $item = StockAlquiler::firstWhere('nombre', 'Saco sin código');
        $this->assertMatchesRegularExpression('/^PRENDA-\d{7}$/', $item->codigo);
    }

    public function test_codigo_duplicado_rechazado(): void
    {
        StockAlquiler::create(['codigo' => 'DUP', 'nombre' => 'X', 'precio_alquiler' => 1000]);

        Livewire::actingAs($this->usuarioCon(['crear-stock-alquiler']))
            ->test(Form::class)
            ->set('codigo', 'DUP')
            ->set('nombre', 'Y')
            ->set('precio_alquiler', 1000)
            ->set('talles', [['id' => null, 'talle' => 'M', 'cantidad' => 1]])
            ->call('save')
            ->assertHasErrors('codigo');
    }

    public function test_update_sincroniza_talles(): void
    {
        $item = StockAlquiler::create(['codigo' => 'C1', 'nombre' => 'N', 'precio_alquiler' => 1000]);
        $tM = $item->talles()->create(['talle' => 'M', 'cantidad_total' => 5, 'cantidad_disponible' => 5]);
        $tL = $item->talles()->create(['talle' => 'L', 'cantidad_total' => 2, 'cantidad_disponible' => 2]);

        Livewire::actingAs($this->usuarioCon(['editar-stock-alquiler']))
            ->test(Form::class, ['item' => $item])
            ->set('talles', [
                ['id' => $tM->id, 'talle' => 'M', 'cantidad' => 8],
                ['id' => null, 'talle' => 'XL', 'cantidad' => 1],
            ])
            ->call('save')
            ->assertRedirect(route('stock.alquiler.index'));

        $item->refresh()->load('talles');
        $this->assertCount(2, $item->talles);
        $this->assertSame(8, $item->talles->firstWhere('talle', 'M')->cantidad_disponible);
        $this->assertModelMissing($tL);
    }

    public function test_eliminar_soft_delete(): void
    {
        $item = StockAlquiler::create(['codigo' => 'DEL', 'nombre' => 'N', 'precio_alquiler' => 1000]);

        Livewire::actingAs($this->usuarioCon(['eliminar-stock-alquiler']))
            ->test(Index::class)->call('eliminar', $item->id);

        $this->assertSoftDeleted('stock_alquiler', ['id' => $item->id]);
    }

    public function test_eliminar_bloqueado_si_alquiler_activo(): void
    {
        $item = app(StockAlquilerService::class)->guardar(
            new StockAlquiler,
            ['codigo' => 'USE', 'nombre' => 'N', 'precio_alquiler' => 1000],
            [['id' => null, 'talle' => 'M', 'cantidad' => 1]],
        );
        $talle = $item->talles()->first();

        app(AlquilerService::class)->crear([
            'cliente_id' => Cliente::create(['nombre' => 'C'])->id, 'fecha_inicio' => now(), 'fecha_fin' => now()->addDays(2),
            'costo_total' => 5000, 'garantia' => 10000,
        ], [['stock_id' => $item->id, 'talle_id' => $talle->id, 'cantidad' => 1]]);

        Livewire::actingAs($this->usuarioCon(['eliminar-stock-alquiler']))
            ->test(Index::class)->call('eliminar', $item->id);

        $this->assertDatabaseHas('stock_alquiler', ['id' => $item->id, 'deleted_at' => null]);
    }

    public function test_crear_talle_genera_unidades_disponibles(): void
    {
        $item = app(StockAlquilerService::class)->guardar(
            new StockAlquiler,
            ['codigo' => 'UNI1', 'nombre' => 'N', 'precio_alquiler' => 1000],
            [['id' => null, 'talle' => 'M', 'cantidad' => 3]],
        );
        $talle = $item->talles()->first();

        $this->assertCount(3, $talle->unidades);
        $this->assertTrue($talle->unidades->every(fn ($u) => $u->estado === EstadoUnidad::Disponible));
        $this->assertCount(3, $talle->unidades->pluck('codigo')->unique());
        $talle->unidades->each(fn ($u) => $this->assertMatchesRegularExpression('/^ALQ-\d{7}$/', $u->codigo));
    }

    public function test_subir_cantidad_crea_unidades_nuevas(): void
    {
        $item = app(StockAlquilerService::class)->guardar(
            new StockAlquiler,
            ['codigo' => 'UNI2', 'nombre' => 'N', 'precio_alquiler' => 1000],
            [['id' => null, 'talle' => 'M', 'cantidad' => 2]],
        );
        $talle = $item->talles()->first();

        app(StockAlquilerService::class)->guardar(
            $item,
            ['codigo' => 'UNI2', 'nombre' => 'N', 'precio_alquiler' => 1000],
            [['id' => $talle->id, 'talle' => 'M', 'cantidad' => 5]],
        );

        $talle->refresh();
        $this->assertCount(5, $talle->unidades);
        $this->assertSame(5, $talle->unidades()->disponibles()->count());
    }

    public function test_bajar_cantidad_da_de_baja_unidades_disponibles(): void
    {
        $item = app(StockAlquilerService::class)->guardar(
            new StockAlquiler,
            ['codigo' => 'UNI3', 'nombre' => 'N', 'precio_alquiler' => 1000],
            [['id' => null, 'talle' => 'M', 'cantidad' => 5]],
        );
        $talle = $item->talles()->first();

        app(StockAlquilerService::class)->guardar(
            $item,
            ['codigo' => 'UNI3', 'nombre' => 'N', 'precio_alquiler' => 1000],
            [['id' => $talle->id, 'talle' => 'M', 'cantidad' => 2]],
        );

        $talle->refresh();
        $this->assertSame(2, $talle->unidades()->disponibles()->count());
        $this->assertSame(3, $talle->unidades()->where('estado', EstadoUnidad::Baja->value)->count());
    }

    public function test_bajar_cantidad_nunca_da_de_baja_unidad_alquilada(): void
    {
        $item = app(StockAlquilerService::class)->guardar(
            new StockAlquiler,
            ['codigo' => 'UNI4', 'nombre' => 'N', 'precio_alquiler' => 1000],
            [['id' => null, 'talle' => 'M', 'cantidad' => 2]],
        );
        $talle = $item->talles()->first();

        // Alquila 1 de las 2 unidades: queda 1 disponible + 1 alquilada.
        app(AlquilerService::class)->crear([
            'cliente_id' => Cliente::create(['nombre' => 'C'])->id, 'fecha_inicio' => now(), 'fecha_fin' => now()->addDay(),
            'costo_total' => 1000, 'garantia' => 0,
        ], [['stock_id' => $item->id, 'talle_id' => $talle->id, 'cantidad' => 1]]);

        // Bajar a 0 requeriría dar de baja 2, pero solo hay 1 disponible (la otra está alquilada).
        $this->expectException(ValidationException::class);
        app(StockAlquilerService::class)->guardar(
            $item,
            ['codigo' => 'UNI4', 'nombre' => 'N', 'precio_alquiler' => 1000],
            [['id' => $talle->id, 'talle' => 'M', 'cantidad' => 0]],
        );
    }

    public function test_ruta_etiquetas_responde_pdf(): void
    {
        $item = app(StockAlquilerService::class)->guardar(
            new StockAlquiler,
            ['codigo' => 'UNI5', 'nombre' => 'N', 'precio_alquiler' => 1000],
            [['id' => null, 'talle' => 'M', 'cantidad' => 2]],
        );

        $this->actingAs($this->usuarioCon(['ver-stock-alquiler']))
            ->get(route('stock.alquiler.etiquetas', $item))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_check_constraint_impide_stock_negativo(): void
    {
        $item = StockAlquiler::create(['codigo' => 'NEG', 'nombre' => 'N', 'precio_alquiler' => 1000]);
        $talle = $item->talles()->create(['talle' => 'M', 'cantidad_total' => 1, 'cantidad_disponible' => 1]);

        $this->expectException(QueryException::class);
        TalleStock::whereKey($talle->id)->update(['cantidad_disponible' => -1]);
    }
}
