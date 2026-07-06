<?php

namespace App\Services;

use App\Enums\EstadoUnidad;
use App\Models\StockAlquiler;
use App\Models\TalleStock;
use App\Models\UnidadStock;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StockAlquilerService
{
    public function __construct(private BarcodeService $barcodes) {}

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

            if (! $item->codigo) {
                $item->update(['codigo' => 'PRENDA-'.str_pad((string) $item->id, 7, '0', STR_PAD_LEFT)]);
            }

            $this->sincronizarTalles($item, $talles);

            return $item;
        });
    }

    /**
     * Actualiza talles existentes (ajustando disponible por la diferencia de
     * total, sin bajar de cero ni tocar alquilado/reservado), crea nuevos y
     * elimina los quitados. Cada unidad de cambio en cantidad_total se refleja
     * en filas `unidad_stock` reales (fuente de verdad de disponibilidad).
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
                    'talle' => $talle['talle'],
                    'cantidad_total' => $talle['cantidad'],
                    'cantidad_disponible' => max(0, $modelo->cantidad_disponible + $diferencia),
                ]);

                $this->ajustarUnidades($modelo, $diferencia);

                $idsConservados[] = $modelo->id;
            } else {
                $nuevo = $item->talles()->create([
                    'talle' => $talle['talle'],
                    'cantidad_total' => $talle['cantidad'],
                    'cantidad_disponible' => $talle['cantidad'],
                    'cantidad_alquilada' => 0,
                    'cantidad_reservada' => 0,
                ]);

                $this->crearUnidades($nuevo, $talle['cantidad']);

                $idsConservados[] = $nuevo->id;
            }
        }

        $item->talles()->whereNotIn('id', $idsConservados)->delete();
    }

    private function crearUnidades(TalleStock $talle, int $cantidad): void
    {
        for ($i = 0; $i < $cantidad; $i++) {
            $unidad = $talle->unidades()->create(['estado' => EstadoUnidad::Disponible]);
            $unidad->update(['codigo' => $this->barcodes->generarCodigoUnidad($unidad->id)]);
        }
    }

    /** Sube (crea unidades nuevas) o baja (da de baja unidades disponibles) según la diferencia. */
    private function ajustarUnidades(TalleStock $talle, int $diferencia): void
    {
        if ($diferencia > 0) {
            $this->crearUnidades($talle, $diferencia);

            return;
        }

        if ($diferencia < 0) {
            $aDarDeBaja = abs($diferencia);
            $disponibles = $talle->unidades()->disponibles()->orderBy('id')->limit($aDarDeBaja)->get();

            if ($disponibles->count() < $aDarDeBaja) {
                throw ValidationException::withMessages([
                    'talles' => "No se puede bajar la cantidad del talle {$talle->talle}: hay unidades alquiladas que lo impiden.",
                ]);
            }

            UnidadStock::whereIn('id', $disponibles->pluck('id'))->update(['estado' => EstadoUnidad::Baja->value]);
        }
    }
}
