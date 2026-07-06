<?php

namespace App\Services;

use App\Enums\EstadoAlquiler;
use App\Models\Alquiler;
use App\Models\TalleStock;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AlquilerService
{
    /**
     * @param  array{cliente_id:int, fecha_inicio:mixed, fecha_fin:mixed, costo_total:int, garantia:int}  $datos
     * @param  array<int, array{stock_id:int, talle_id:int, cantidad:int}>  $prendas
     */
    public function crear(array $datos, array $prendas): Alquiler
    {
        return DB::transaction(function () use ($datos, $prendas) {
            $alquiler = Alquiler::create([
                'cliente_id'   => $datos['cliente_id'],
                'fecha_inicio' => $datos['fecha_inicio'],
                'fecha_fin'    => $datos['fecha_fin'],
                'costo_total'  => $datos['costo_total'],
                'garantia'     => $datos['garantia'],
                'estado'       => EstadoAlquiler::Activo,
            ]);

            $this->entregarPrendas($alquiler, $prendas);

            return $alquiler;
        });
    }

    /**
     * @param  array{cliente_id:int, fecha_inicio:mixed, fecha_fin:mixed, costo_total:int, garantia:int}  $datos
     * @param  array<int, array{stock_id:int, talle_id:int, cantidad:int}>  $prendas
     */
    public function actualizar(Alquiler $alquiler, array $datos, array $prendas): Alquiler
    {
        return DB::transaction(function () use ($alquiler, $datos, $prendas) {
            $this->restaurarStock($alquiler);
            $alquiler->stockItems()->detach();

            $alquiler->update([
                'cliente_id'   => $datos['cliente_id'],
                'fecha_inicio' => $datos['fecha_inicio'],
                'fecha_fin'    => $datos['fecha_fin'],
                'costo_total'  => $datos['costo_total'],
                'garantia'     => $datos['garantia'],
            ]);

            $this->entregarPrendas($alquiler, $prendas);

            return $alquiler;
        });
    }

    public function anular(Alquiler $alquiler): void
    {
        DB::transaction(function () use ($alquiler) {
            if ($alquiler->estaActivo()) {
                $this->restaurarStock($alquiler);
            }

            $alquiler->delete(); // soft delete
        });
    }

    /**
     * Bloquea cada talle (anti sobre-alquiler) y mueve stock de disponible a alquilado.
     *
     * @param  array<int, array{stock_id:int, talle_id:int, cantidad:int}>  $prendas
     */
    private function entregarPrendas(Alquiler $alquiler, array $prendas): void
    {
        foreach ($prendas as $prenda) {
            $talle = TalleStock::whereKey($prenda['talle_id'])->lockForUpdate()->firstOrFail();

            if ($talle->cantidad_disponible < $prenda['cantidad']) {
                throw ValidationException::withMessages([
                    'prendas' => "Stock insuficiente para el talle {$talle->talle}. Disponible: {$talle->cantidad_disponible}.",
                ]);
            }

            $alquiler->stockItems()->attach($prenda['stock_id'], [
                'talle_id' => $prenda['talle_id'],
                'cantidad' => $prenda['cantidad'],
            ]);

            $talle->decrement('cantidad_disponible', $prenda['cantidad']);
            $talle->increment('cantidad_alquilada', $prenda['cantidad']);
        }
    }

    private function restaurarStock(Alquiler $alquiler): void
    {
        $items = $alquiler->stockItems()->withPivot('talle_id', 'cantidad')->get();

        foreach ($items as $item) {
            $talle = TalleStock::whereKey($item->pivot->talle_id)->lockForUpdate()->first();
            $talle?->increment('cantidad_disponible', $item->pivot->cantidad);
            $talle?->decrement('cantidad_alquilada', $item->pivot->cantidad);
        }
    }
}
