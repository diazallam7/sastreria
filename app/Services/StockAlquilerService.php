<?php

namespace App\Services;

use App\Models\StockAlquiler;
use Illuminate\Support\Facades\DB;

class StockAlquilerService
{
    /**
     * Crea o actualiza una prenda de stock y sincroniza sus talles.
     *
     * @param  array<string, mixed>  $datos
     * @param  array<int, array{id?:int|null, talle:string, cantidad:int}>  $talles
     */
    public function guardar(StockAlquiler $item, array $datos, array $talles): StockAlquiler
    {
        return DB::transaction(function () use ($item, $datos, $talles) {
            $item->fill($datos)->save();
            $this->sincronizarTalles($item, $talles);

            return $item;
        });
    }

    /**
     * Actualiza talles existentes (ajustando disponible por la diferencia de
     * total, sin bajar de cero ni tocar alquilado/reservado), crea nuevos y
     * elimina los quitados.
     *
     * @param  array<int, array{id?:int|null, talle:string, cantidad:int}>  $talles
     */
    private function sincronizarTalles(StockAlquiler $item, array $talles): void
    {
        $idsConservados = [];

        foreach ($talles as $talle) {
            if (! empty($talle['id'])) {
                $modelo = $item->talles()->findOrFail($talle['id']);
                $diferencia = $talle['cantidad'] - $modelo->cantidad_total;

                $modelo->update([
                    'talle'               => $talle['talle'],
                    'cantidad_total'      => $talle['cantidad'],
                    'cantidad_disponible' => max(0, $modelo->cantidad_disponible + $diferencia),
                ]);

                $idsConservados[] = $modelo->id;
            } else {
                $nuevo = $item->talles()->create([
                    'talle'               => $talle['talle'],
                    'cantidad_total'      => $talle['cantidad'],
                    'cantidad_disponible' => $talle['cantidad'],
                    'cantidad_alquilada'  => 0,
                    'cantidad_reservada'  => 0,
                ]);

                $idsConservados[] = $nuevo->id;
            }
        }

        $item->talles()->whereNotIn('id', $idsConservados)->delete();
    }
}
