<?php

namespace Tests\Feature;

use App\Enums\EstadoAlquiler;
use App\Enums\EstadoReserva;
use App\Livewire\Reservas\Form;
use App\Livewire\Reservas\Index;
use App\Livewire\Reservas\Show;
use App\Models\Alquiler;
use App\Models\Cliente;
use App\Models\Reserva;
use App\Models\StockAlquiler;
use App\Models\TalleStock;
use App\Services\ReservaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ReservaModuleTest extends TestCase
{
    use RefreshDatabase;

    private function prendaConStock(int $disp = 5): TalleStock
    {
        $item = StockAlquiler::create(['codigo' => 'P' . uniqid(), 'nombre' => 'Prenda', 'precio_alquiler' => 50000]);

        return $item->talles()->create([
            'talle' => 'M', 'cantidad_total' => $disp, 'cantidad_disponible' => $disp, 'cantidad_reservada' => 0,
        ]);
    }

    private function reservaConfirmada(TalleStock $talle, int $cantidad = 2): Reserva
    {
        return app(ReservaService::class)->crear([
            'cliente_id' => Cliente::create(['nombre' => 'C'])->id,
            'fecha_reserva' => now(), 'fecha_entrega_programada' => now()->addDays(5), 'fecha_devolucion_programada' => now()->addDays(8),
            'monto_total' => 100000, 'garantia_total' => 60000, 'senia_garantia' => 30000, 'senia_alquiler' => 20000,
        ], [['stock_id' => $talle->stock_id, 'talle_id' => $talle->id, 'cantidad' => $cantidad]]);
    }

    public function test_invitado_es_redirigido_al_login(): void
    {
        $this->get(route('reservas.index'))->assertRedirect(route('login'));
    }

    public function test_sin_permiso_recibe_403(): void
    {
        $this->actingAs($this->usuarioCon([]))->get(route('reservas.index'))->assertForbidden();
    }

    public function test_crear_reserva_mueve_stock_a_reservado(): void
    {
        $cliente = Cliente::create(['nombre' => 'C']);
        $talle = $this->prendaConStock(5);

        Livewire::actingAs($this->usuarioCon(['crear-reserva']))
            ->test(Form::class)
            ->set('cliente_id', $cliente->id)
            ->set('garantia_total', 60000)
            ->set('senia_garantia', 30000)
            ->set('senia_alquiler', 20000)
            ->set('stockSel', $talle->stock_id)
            ->set('talleSel', $talle->id)
            ->set('cantidadSel', 2)
            ->call('agregarPrenda')
            ->assertSet('monto_total', '100000') // auto: 50000 × 2
            ->call('save')
            ->assertRedirect(route('reservas.index'));

        $reserva = Reserva::first();
        $this->assertSame(EstadoReserva::Confirmada, $reserva->estado);

        $talle->refresh();
        $this->assertSame(3, $talle->cantidad_disponible);
        $this->assertSame(2, $talle->cantidad_reservada);
    }

    public function test_senia_garantia_mayor_que_garantia_falla(): void
    {
        $cliente = Cliente::create(['nombre' => 'C']);
        $talle = $this->prendaConStock(5);

        Livewire::actingAs($this->usuarioCon(['crear-reserva']))
            ->test(Form::class)
            ->set('cliente_id', $cliente->id)
            ->set('garantia_total', 60000)
            ->set('senia_garantia', 999999)
            ->set('stockSel', $talle->stock_id)->set('talleSel', $talle->id)->set('cantidadSel', 1)
            ->call('agregarPrenda')
            ->call('save')
            ->assertHasErrors('senia_garantia');

        $this->assertDatabaseCount('reservas', 0);
    }

    public function test_convertir_a_alquiler(): void
    {
        $talle = $this->prendaConStock(5);
        $reserva = $this->reservaConfirmada($talle, 2);

        Livewire::actingAs($this->usuarioCon(['editar-reserva']))
            ->test(Show::class, ['reserva' => $reserva])
            ->call('abrirConvertir')
            ->set('fechaEntrega', now()->toDateString())
            ->set('fechaDevolucion', now()->addDays(3)->toDateString())
            ->call('convertir')
            ->assertRedirect(route('reservas.index'));

        $reserva->refresh();
        $this->assertSame(EstadoReserva::Entregada, $reserva->estado);
        $this->assertNotNull($reserva->alquiler_id);
        $this->assertSame(EstadoAlquiler::Activo, Alquiler::find($reserva->alquiler_id)->estado);

        $talle->refresh();
        $this->assertSame(0, $talle->cantidad_reservada);
        $this->assertSame(2, $talle->cantidad_alquilada);
    }

    public function test_convertir_desde_el_listado(): void
    {
        $talle = $this->prendaConStock(5);
        $reserva = $this->reservaConfirmada($talle, 2);

        Livewire::actingAs($this->usuarioCon(['editar-reserva']))
            ->test(Index::class)
            ->call('abrirConvertir', $reserva->id)
            ->assertSet('modalConvertir', true)
            ->set('fechaEntrega', now()->toDateString())
            ->set('fechaDevolucion', now()->addDays(3)->toDateString())
            ->call('convertir')
            ->assertSet('modalConvertir', false);

        $reserva->refresh();
        $this->assertSame(EstadoReserva::Entregada, $reserva->estado);
        $this->assertNotNull($reserva->alquiler_id);
        $this->assertSame(2, $talle->refresh()->cantidad_alquilada);
    }

    public function test_cancelar_libera_stock_y_valida_devolucion(): void
    {
        $talle = $this->prendaConStock(5);
        $reserva = $this->reservaConfirmada($talle, 2);
        $user = $this->usuarioCon(['editar-reserva']);

        // Devuelve más de lo recibido (50000) -> error
        Livewire::actingAs($user)->test(Show::class, ['reserva' => $reserva])
            ->call('abrirCancelar')->set('seniaDevuelta', 999999)->set('motivo', 'x')->call('cancelar')
            ->assertHasErrors('seniaDevuelta');
        $this->assertSame(EstadoReserva::Confirmada, $reserva->refresh()->estado);

        // Cancelación válida
        Livewire::actingAs($user)->test(Show::class, ['reserva' => $reserva])
            ->call('abrirCancelar')->set('seniaDevuelta', 10000)->set('motivo', 'cliente desistió')->call('cancelar')
            ->assertRedirect(route('reservas.index'));

        $this->assertSame(EstadoReserva::Cancelada, $reserva->refresh()->estado);
        $talle->refresh();
        $this->assertSame(5, $talle->cantidad_disponible);
        $this->assertSame(0, $talle->cantidad_reservada);
    }

    public function test_eliminar_libera_stock_y_soft_delete(): void
    {
        $talle = $this->prendaConStock(5);
        $reserva = $this->reservaConfirmada($talle, 2);

        Livewire::actingAs($this->usuarioCon(['eliminar-reserva']))
            ->test(Index::class)->call('eliminar', $reserva->id);

        $this->assertSoftDeleted('reservas', ['id' => $reserva->id]);
        $this->assertSame(5, $talle->refresh()->cantidad_disponible);
    }
}
