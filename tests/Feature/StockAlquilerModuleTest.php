<?php

namespace Tests\Feature;

use App\Livewire\StockAlquiler\Form;
use App\Livewire\StockAlquiler\Index;
use App\Models\Alquiler;
use App\Models\Cliente;
use App\Models\StockAlquiler;
use App\Models\TalleStock;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
        $item = StockAlquiler::create(['codigo' => 'USE', 'nombre' => 'N', 'precio_alquiler' => 1000]);
        $talle = $item->talles()->create(['talle' => 'M', 'cantidad_total' => 1, 'cantidad_disponible' => 0, 'cantidad_alquilada' => 1]);
        $alquiler = Alquiler::create([
            'cliente_id' => Cliente::create(['nombre' => 'C'])->id, 'fecha_inicio' => now(), 'fecha_fin' => now()->addDays(2),
            'costo_total' => 5000, 'garantia' => 10000, 'estado' => 'activo',
        ]);
        $alquiler->stockItems()->attach($item->id, ['talle_id' => $talle->id, 'cantidad' => 1]);

        Livewire::actingAs($this->usuarioCon(['eliminar-stock-alquiler']))
            ->test(Index::class)->call('eliminar', $item->id);

        $this->assertDatabaseHas('stock_alquiler', ['id' => $item->id, 'deleted_at' => null]);
    }

    public function test_check_constraint_impide_stock_negativo(): void
    {
        $item = StockAlquiler::create(['codigo' => 'NEG', 'nombre' => 'N', 'precio_alquiler' => 1000]);
        $talle = $item->talles()->create(['talle' => 'M', 'cantidad_total' => 1, 'cantidad_disponible' => 1]);

        $this->expectException(QueryException::class);
        TalleStock::whereKey($talle->id)->update(['cantidad_disponible' => -1]);
    }
}
