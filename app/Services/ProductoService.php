<?php

namespace App\Services;

use App\Enums\TipoProducto;
use App\Models\Producto;
use Illuminate\Support\Facades\DB;

class ProductoService
{
    public function __construct(private BarcodeService $barcodes) {}

    /**
     * Crea o actualiza un producto y sincroniza sus talles.
     * Los productos fabricados no tienen costo/fecha de compra (se anulan).
     *
     * @param  array<string, mixed>  $datos
     * @param  array<int, array{id?:int|null, talle:string, cantidad:int, codigo_barra?:string|null}>  $talles
     */
    public function guardar(Producto $producto, array $datos, array $talles): Producto
    {
        return DB::transaction(function () use ($producto, $datos, $talles) {
            if (($datos['tipo'] ?? null) === TipoProducto::Fabricado->value) {
                $datos['precio_compra'] = null;
                $datos['fecha_compra'] = null;
            }

            $producto->fill($datos)->save();
            $this->sincronizarTalles($producto, $talles);

            return $producto;
        });
    }

    /**
     * Actualiza talles existentes (ajustando disponible por la diferencia de
     * total, sin bajar de cero ni tocar lo vendido), crea nuevos y elimina los quitados.
     *
     * @param  array<int, array{id?:int|null, talle:string, cantidad:int, codigo_barra?:string|null}>  $talles
     */
    private function sincronizarTalles(Producto $producto, array $talles): void
    {
        $idsConservados = [];

        foreach ($talles as $talle) {
            if (! empty($talle['id'])) {
                $modelo = $producto->talles()->findOrFail($talle['id']);
                $diferencia = $talle['cantidad'] - $modelo->cantidad_total;

                $modelo->update([
                    'talle' => $talle['talle'],
                    'cantidad_total' => $talle['cantidad'],
                    'cantidad_disponible' => max(0, $modelo->cantidad_disponible + $diferencia),
                    'codigo_barra' => ($talle['codigo_barra'] ?? null) ?: $modelo->codigo_barra,
                ]);

                $idsConservados[] = $modelo->id;
            } else {
                $nuevo = $producto->talles()->create([
                    'talle' => $talle['talle'],
                    'cantidad_total' => $talle['cantidad'],
                    'cantidad_disponible' => $talle['cantidad'],
                    'cantidad_vendida' => 0,
                    'codigo_barra' => $talle['codigo_barra'] ?? null,
                ]);

                if (! $nuevo->codigo_barra) {
                    $nuevo->update(['codigo_barra' => $this->barcodes->generarCodigoVenta($nuevo->id)]);
                }

                $idsConservados[] = $nuevo->id;
            }
        }

        $producto->talles()->whereNotIn('id', $idsConservados)->delete();
    }
}
