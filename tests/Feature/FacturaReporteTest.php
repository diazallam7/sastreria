<?php

namespace Tests\Feature;

use App\Enums\EstadoAlquiler;
use App\Livewire\Reportes\Index as ReportesIndex;
use App\Models\Alquiler;
use App\Models\Cliente;
use App\Models\StockAlquiler;
use App\Models\User;
use App\Models\Venta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class FacturaReporteTest extends TestCase
{
    use RefreshDatabase;

    public function test_factura_venta_genera_pdf(): void
    {
        $cliente = Cliente::create(['nombre' => 'C']);
        $venta = Venta::create(['cliente_id' => $cliente->id, 'fecha_venta' => now(), 'precio_total' => 100000]);
        $venta->detalles()->create(['nombre_producto' => 'Camisa', 'talle' => 'M', 'cantidad' => 1, 'precio_unitario' => 100000, 'subtotal' => 100000]);

        $resp = $this->actingAs(User::factory()->create())->get(route('factura.venta', $venta));

        $resp->assertOk();
        $this->assertStringContainsString('application/pdf', $resp->headers->get('content-type'));
    }

    public function test_factura_alquiler_genera_pdf(): void
    {
        $item = StockAlquiler::create(['codigo' => 'A1', 'nombre' => 'Saco', 'precio_alquiler' => 50000]);
        $talle = $item->talles()->create(['talle' => 'M', 'cantidad_total' => 1, 'cantidad_disponible' => 0, 'cantidad_alquilada' => 1]);
        $alquiler = Alquiler::create([
            'cliente_id' => Cliente::create(['nombre' => 'C'])->id, 'fecha_inicio' => now(), 'fecha_fin' => now()->addDays(2),
            'costo_total' => 80000, 'garantia' => 200000, 'estado' => EstadoAlquiler::Activo,
        ]);
        $alquiler->stockItems()->attach($item->id, ['talle_id' => $talle->id, 'cantidad' => 1]);

        $resp = $this->actingAs(User::factory()->create())->get(route('factura.alquiler', $alquiler));

        $resp->assertOk();
        $this->assertStringContainsString('application/pdf', $resp->headers->get('content-type'));
    }

    public function test_reportes_agrupa_ventas(): void
    {
        $cliente = Cliente::create(['nombre' => 'C']);
        Venta::create(['cliente_id' => $cliente->id, 'fecha_venta' => now(), 'precio_total' => 100000]);
        Venta::create(['cliente_id' => $cliente->id, 'fecha_venta' => now(), 'precio_total' => 50000]);

        Livewire::actingAs(User::factory()->create())
            ->test(ReportesIndex::class)
            ->assertSet('tipo', 'ventas')
            ->assertSee(now()->format('Y-m'))
            ->assertSee('150.000'); // 100000 + 50000
    }
}
