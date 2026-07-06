<?php

namespace Tests\Feature;

use App\Enums\EstadoAlquiler;
use App\Livewire\Clientes\Show as ClienteShow;
use App\Livewire\Devoluciones\Index;
use App\Models\Alquiler;
use App\Models\Cliente;
use App\Models\Configuracion;
use App\Models\Devolucion;
use App\Models\StockAlquiler;
use App\Models\TalleStock;
use App\Services\AlquilerService;
use App\Services\StockAlquilerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DevolucionModuleTest extends TestCase
{
    use RefreshDatabase;

    /** @return array{0: Alquiler, 1: TalleStock} */
    private function alquilerActivo(int $garantia = 200000, ?string $fechaFin = null, ?Cliente $cliente = null): array
    {
        $item = app(StockAlquilerService::class)->guardar(
            new StockAlquiler,
            ['codigo' => 'P'.uniqid(), 'nombre' => 'Prenda', 'precio_alquiler' => 50000],
            [['id' => null, 'talle' => 'M', 'cantidad' => 5]],
        );
        $talle = $item->talles()->first();

        $alquiler = app(AlquilerService::class)->crear([
            'cliente_id' => ($cliente ?? Cliente::create(['nombre' => 'C']))->id,
            'fecha_inicio' => now()->subDays(5),
            'fecha_fin' => $fechaFin ?? now()->addDays(2),
            'costo_total' => 100000,
            'garantia' => $garantia,
        ], [['stock_id' => $item->id, 'talle_id' => $talle->id, 'cantidad' => 2]]);

        return [$alquiler, $talle->refresh()];
    }

    public function test_invitado_es_redirigido_al_login(): void
    {
        $this->get(route('devoluciones.index'))->assertRedirect(route('login'));
    }

    public function test_sin_permiso_recibe_403(): void
    {
        $this->actingAs($this->usuarioCon([]))->get(route('devoluciones.index'))->assertForbidden();
    }

    public function test_procesar_registra_devolucion_y_restaura_stock(): void
    {
        [$alquiler, $talle] = $this->alquilerActivo(garantia: 200000);

        Livewire::actingAs($this->usuarioCon(['crear-devolucion']))
            ->test(Index::class)
            ->call('abrirProcesar', $alquiler->id)
            ->assertSet('modalProcesar', true)
            ->call('procesar')
            ->assertSet('modalProcesar', false);

        $this->assertSame(EstadoAlquiler::Completado, $alquiler->refresh()->estado);
        $this->assertSame(5, $talle->refresh()->cantidad_disponible);

        $dev = Devolucion::first();
        $this->assertSame(200000, $dev->monto_devuelto); // sin retraso: devuelve toda la garantía
    }

    public function test_multa_por_retraso_se_calcula(): void
    {
        Configuracion::create(['nombre' => 'multa', 'valor' => 10000]);
        [$alquiler] = $this->alquilerActivo(garantia: 200000, fechaFin: now()->subDays(3));

        Livewire::actingAs($this->usuarioCon(['crear-devolucion']))
            ->test(Index::class)
            ->call('abrirProcesar', $alquiler->id)
            ->assertSet('multaCalculada', 30000) // 3 × 10000
            ->call('procesar');

        $dev = Devolucion::first();
        $this->assertSame(30000, $dev->multa_aplicada);
        $this->assertSame(170000, $dev->monto_devuelto); // 200000 - 30000
    }

    public function test_ajuste_de_multa_exige_motivo(): void
    {
        Configuracion::create(['nombre' => 'multa', 'valor' => 10000]);
        [$alquiler] = $this->alquilerActivo(garantia: 200000, fechaFin: now()->subDays(3));

        Livewire::actingAs($this->usuarioCon(['crear-devolucion']))
            ->test(Index::class)
            ->call('abrirProcesar', $alquiler->id)
            ->set('multaAplicada', '0') // perdona la multa → distinto de la calculada
            ->call('procesar')
            ->assertHasErrors('motivo');

        $this->assertDatabaseCount('devoluciones', 0);
    }

    public function test_faltante_cuando_multa_supera_garantia(): void
    {
        Configuracion::create(['nombre' => 'multa', 'valor' => 10000]);
        [$alquiler] = $this->alquilerActivo(garantia: 20000, fechaFin: now()->subDays(10)); // multa 100000 > garantía 20000

        Livewire::actingAs($this->usuarioCon(['crear-devolucion']))
            ->test(Index::class)
            ->call('abrirProcesar', $alquiler->id)
            ->assertSet('multaCalculada', 100000)
            ->call('procesar');

        $dev = Devolucion::first();
        $this->assertSame(0, $dev->monto_devuelto); // no puede devolver negativo
        $this->assertSame(100000, $dev->multa_aplicada);
    }

    public function test_no_reabre_si_ya_devuelto(): void
    {
        [$alquiler] = $this->alquilerActivo();
        $user = $this->usuarioCon(['crear-devolucion']);

        Livewire::actingAs($user)->test(Index::class)->call('abrirProcesar', $alquiler->id)->call('procesar');

        Livewire::actingAs($user)->test(Index::class)
            ->call('abrirProcesar', $alquiler->id)
            ->assertSet('modalProcesar', false); // no abre: ya devuelto

        $this->assertSame(1, Devolucion::count());
    }

    public function test_mora_aparece_en_historial_del_cliente(): void
    {
        Configuracion::create(['nombre' => 'multa', 'valor' => 10000]);
        $cliente = Cliente::create(['nombre' => 'Moroso']);
        [$alquiler] = $this->alquilerActivo(garantia: 200000, fechaFin: now()->subDays(3), cliente: $cliente);

        Livewire::actingAs($this->usuarioCon(['crear-devolucion']))
            ->test(Index::class)->call('abrirProcesar', $alquiler->id)->call('procesar');

        Livewire::actingAs($this->usuarioCon(['ver-cliente']))
            ->test(ClienteShow::class, ['cliente' => $cliente])
            ->assertSee('Mora');
    }
}
