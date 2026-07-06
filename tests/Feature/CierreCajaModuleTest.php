<?php

namespace Tests\Feature;

use App\Enums\EstadoAlquiler;
use App\Enums\EstadoReserva;
use App\Models\Alquiler;
use App\Models\Cliente;
use App\Models\Devolucion;
use App\Models\GastoVario;
use App\Models\Producto;
use App\Models\Reserva;
use App\Models\Venta;
use App\Services\ReporteCajaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CierreCajaModuleTest extends TestCase
{
    use RefreshDatabase;

    private function sembrarMovimientosHoy(): void
    {
        $cliente = Cliente::create(['nombre' => 'C']);

        // Ingresos
        Venta::create(['cliente_id' => $cliente->id, 'fecha_venta' => now(), 'precio_total' => 100000]);

        Alquiler::create([
            'cliente_id' => $cliente->id, 'fecha_inicio' => now(), 'fecha_fin' => now()->addDays(2),
            'costo_total' => 50000, 'garantia' => 0, 'estado' => EstadoAlquiler::Activo,
        ]);

        $alq = Alquiler::create([
            'cliente_id' => $cliente->id, 'fecha_inicio' => now()->subDays(5), 'fecha_fin' => now()->subDay(),
            'costo_total' => 0, 'garantia' => 100000, 'estado' => EstadoAlquiler::Completado,
        ]);
        Devolucion::create([
            'alquiler_id' => $alq->id, 'fecha_devolucion' => now(), 'retraso' => true, 'dias_retraso' => 1,
            'multa_calculada' => 10000, 'multa_aplicada' => 10000, 'garantia_original' => 100000, 'monto_devuelto' => 90000,
        ]);

        // Cancelación: recibido 100000 - devuelto 20000 = 80000 neto
        Reserva::create([
            'cliente_id' => $cliente->id, 'fecha_reserva' => now(), 'fecha_entrega_programada' => now()->addDay(),
            'fecha_devolucion_programada' => now()->addDays(2), 'monto_total' => 60000, 'garantia_total' => 60000,
            'senia_alquiler' => 40000, 'senia_garantia' => 60000, 'senia_devuelta' => 20000,
            'estado' => EstadoReserva::Cancelada,
        ]);

        // Egresos
        $prod = Producto::create(['nombre' => 'Tela', 'tipo' => 'comprado', 'precio_venta' => 0, 'precio_compra' => 20000, 'fecha_compra' => now()]);
        $prod->talles()->create(['talle' => 'U', 'cantidad_total' => 5, 'cantidad_disponible' => 5]); // 20000 * 5 = 100000

        GastoVario::create(['nombre_gasto' => 'Luz', 'fecha' => now(), 'monto' => 30000]);
    }

    public function test_invitado_es_redirigido_al_login(): void
    {
        $this->get(route('cierre-caja.index'))->assertRedirect(route('login'));
    }

    public function test_sin_permiso_recibe_403(): void
    {
        $this->actingAs($this->usuarioCon([]))->get(route('cierre-caja.index'))->assertForbidden();
    }

    public function test_movimientos_dia_suma_ingresos_y_egresos(): void
    {
        $this->sembrarMovimientosHoy();

        $mov = app(ReporteCajaService::class)->movimientosDia(now()->toDateString());

        $this->assertSame(50000, $mov['ingresos']['alquileres']);
        $this->assertSame(10000, $mov['ingresos']['multas_retraso']);
        $this->assertSame(100000, $mov['ingresos']['ventas']);
        $this->assertSame(80000, $mov['ingresos']['ingresos_cancelaciones']);
        $this->assertSame(240000, $mov['ingresos']['total']);

        $this->assertSame(100000, $mov['egresos']['compras']);
        $this->assertSame(30000, $mov['egresos']['gastos_varios']);
        $this->assertSame(130000, $mov['egresos']['total']);

        $this->assertSame(110000, $mov['saldo_neto']);
    }

    public function test_venta_anulada_no_cuenta(): void
    {
        $this->sembrarMovimientosHoy();

        Venta::first()->delete(); // soft delete = anulada

        $mov = app(ReporteCajaService::class)->movimientosDia(now()->toDateString());
        $this->assertSame(0, $mov['ingresos']['ventas']);
        $this->assertSame(140000, $mov['ingresos']['total']); // 240000 - 100000
    }

    public function test_totales_por_rango_agrega_la_semana(): void
    {
        $this->sembrarMovimientosHoy();

        $semana = app(ReporteCajaService::class)
            ->totalesPorRango(now()->copy()->startOfWeek(), now()->copy()->endOfWeek());

        $totalIngresos = collect($semana)->sum(fn ($d) => $d['ingresos']['total']);
        $this->assertSame(240000, $totalIngresos);
        $this->assertCount(7, $semana); // 7 días
    }

    public function test_diario_renderiza_con_permiso(): void
    {
        $this->sembrarMovimientosHoy();

        $this->actingAs($this->usuarioCon(['ver-cierre-caja']))
            ->get(route('cierre-caja.index', ['fecha' => now()->toDateString()]))
            ->assertOk()
            ->assertSee('Cierre de caja')
            ->assertSee('Saldo neto');
    }

    public function test_semanal_y_mensual_renderizan(): void
    {
        $this->sembrarMovimientosHoy();

        $this->actingAs($this->usuarioCon(['ver-cierre-caja-semanal']))
            ->get(route('cierre-caja.semanal'))->assertOk()->assertSee('Cierre semanal');

        $this->actingAs($this->usuarioCon(['ver-cierre-caja-mensual']))
            ->get(route('cierre-caja.mensual'))->assertOk()->assertSee('Cierre mensual');
    }

    public function test_pdf_diario_descarga(): void
    {
        $this->sembrarMovimientosHoy();

        $resp = $this->actingAs($this->usuarioCon(['exportar-cierre-caja']))
            ->get(route('cierre-caja.pdf', ['fecha' => now()->toDateString()]));

        $resp->assertOk();
        $this->assertStringContainsString('application/pdf', $resp->headers->get('content-type'));
    }
}
