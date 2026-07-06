<?php

namespace App\Services;

use App\Models\ProductoTalle;
use App\Models\Venta;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class VentaService
{
    /**
     * @param  array{cliente_id:int, fecha_venta?:mixed}  $datos
     * @param  array<int, array{producto_talle_id:int, cantidad:int}>  $items
     */
    public function crear(array $datos, array $items): Venta
    {
        return DB::transaction(function () use ($datos, $items) {
            $venta = Venta::create([
                'cliente_id' => $datos['cliente_id'],
                'user_id' => auth()->id(),
                'fecha_venta' => $datos['fecha_venta'] ?? now(),
                'precio_total' => 0,
            ]);

            $venta->update(['precio_total' => $this->aplicarItems($venta, $items)]);

            return $venta;
        });
    }

    /**
     * @param  array{cliente_id:int, fecha_venta?:mixed}  $datos
     * @param  array<int, array{producto_talle_id:int, cantidad:int}>  $items
     */
    public function actualizar(Venta $venta, array $datos, array $items): Venta
    {
        return DB::transaction(function () use ($venta, $datos, $items) {
            $this->restaurarStock($venta);
            $venta->detalles()->delete();

            $venta->update([
                'cliente_id' => $datos['cliente_id'],
                'fecha_venta' => $datos['fecha_venta'] ?? $venta->fecha_venta,
                'precio_total' => $this->aplicarItems($venta->refresh(), $items),
            ]);

            return $venta;
        });
    }

    public function anular(Venta $venta): void
    {
        DB::transaction(function () use ($venta) {
            $this->restaurarStock($venta);
            $venta->delete(); // soft delete: conserva el registro contable
        });
    }

    /**
     * Descuenta stock bloqueando cada talle (anti-sobreventa) y toma el precio
     * del servidor (no del cliente). Devuelve el total.
     *
     * @param  array<int, array{producto_talle_id:int, cantidad:int}>  $items
     */
    private function aplicarItems(Venta $venta, array $items): int
    {
        $total = 0;

        foreach ($items as $item) {
            $talle = ProductoTalle::whereKey($item['producto_talle_id'])->lockForUpdate()->firstOrFail();
            $producto = $talle->producto()->first();

            if ($talle->cantidad_disponible < $item['cantidad']) {
                throw ValidationException::withMessages([
                    'items' => "Stock insuficiente para {$producto->nombre} (talle {$talle->talle}). Disponible: {$talle->cantidad_disponible}.",
                ]);
            }

            $precio = (int) $producto->precio_venta;
            $subtotal = $precio * $item['cantidad'];

            $venta->detalles()->create([
                'producto_talle_id' => $talle->id,
                'nombre_producto' => $producto->nombre,
                'talle' => $talle->talle,
                'cantidad' => $item['cantidad'],
                'precio_unitario' => $precio,
                'subtotal' => $subtotal,
            ]);

            $talle->decrement('cantidad_disponible', $item['cantidad']);
            $talle->increment('cantidad_vendida', $item['cantidad']);

            $total += $subtotal;
        }

        return $total;
    }

    private function restaurarStock(Venta $venta): void
    {
        foreach ($venta->detalles as $detalle) {
            if (! $detalle->producto_talle_id) {
                continue;
            }

            $talle = ProductoTalle::whereKey($detalle->producto_talle_id)->lockForUpdate()->first();
            $talle?->increment('cantidad_disponible', $detalle->cantidad);
            $talle?->decrement('cantidad_vendida', $detalle->cantidad);
        }
    }

    /** Imprime (modo prueba) el ticket de la venta desde sus detalles. */
    public function imprimirTicket(Venta $venta): string
    {
        try {
            $venta->loadMissing('cliente', 'user', 'detalles');

            $ventaData = [
                'id' => $venta->id,
                'fecha' => $venta->fecha_venta->format('Y-m-d H:i:s'),
                'cajero' => $venta->user?->name ?? 'Desconocido',
                'cliente' => $venta->cliente?->nombre ?? 'Consumidor Final',
                'precio_total' => $venta->precio_total,
            ];

            $productos = $venta->detalles->map(fn ($d) => [
                'nombre' => $d->nombre_producto.' (T:'.$d->talle.')',
                'cantidad' => $d->cantidad,
                'subtotal' => $d->subtotal,
            ])->all();

            (new TicketPrinterService(
                printerConfig: (string) config('services.printer.host'),
                isTestMode: (bool) config('services.printer.test_mode'),
                printerPort: (int) config('services.printer.port'),
            ))->printSaleTicket($ventaData, $productos);

            return 'Venta registrada correctamente.';
        } catch (\Throwable $e) {
            Log::warning('Ticket no impreso', ['venta_id' => $venta->id, 'error' => $e->getMessage()]);

            return 'Venta registrada. Advertencia: no se pudo imprimir el ticket.';
        }
    }
}
