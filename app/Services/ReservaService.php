<?php

namespace App\Services;

use App\Enums\EstadoAlquiler;
use App\Enums\EstadoReserva;
use App\Models\Alquiler;
use App\Models\Reserva;
use App\Models\TalleStock;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReservaService
{
    /**
     * @param  array<string, mixed>  $datos
     * @param  array<int, array{stock_id:int, talle_id:int, cantidad:int}>  $prendas
     */
    public function crear(array $datos, array $prendas): Reserva
    {
        return DB::transaction(function () use ($datos, $prendas) {
            $reserva = Reserva::create(array_merge($datos, [
                'user_id' => auth()->id(),
                'estado'  => EstadoReserva::Confirmada,
            ]));

            $this->reservarPrendas($reserva, $prendas);

            return $reserva;
        });
    }

    /**
     * @param  array<string, mixed>  $datos
     * @param  array<int, array{stock_id:int, talle_id:int, cantidad:int}>  $prendas
     */
    public function actualizar(Reserva $reserva, array $datos, array $prendas): Reserva
    {
        return DB::transaction(function () use ($reserva, $datos, $prendas) {
            $this->liberarStock($reserva);
            $reserva->stockItems()->detach();

            $reserva->update($datos);

            $this->reservarPrendas($reserva, $prendas);

            return $reserva;
        });
    }

    /**
     * Convierte una reserva confirmada en alquiler: mueve el stock de reservado
     * a alquilado y marca la reserva como entregada.
     */
    public function convertirAAlquiler(Reserva $reserva, string $fechaEntrega, string $fechaDevolucion, ?string $observacionesEntrega): Alquiler
    {
        return DB::transaction(function () use ($reserva, $fechaEntrega, $fechaDevolucion, $observacionesEntrega) {
            if ($reserva->estado !== EstadoReserva::Confirmada) {
                throw ValidationException::withMessages(['reserva' => 'Solo se pueden convertir reservas confirmadas.']);
            }

            $items = $reserva->stockItems()->withPivot('talle_id', 'cantidad')->get();

            if ($items->isEmpty()) {
                throw ValidationException::withMessages(['reserva' => 'La reserva no tiene prendas asociadas.']);
            }

            $alquiler = Alquiler::create([
                'cliente_id'   => $reserva->cliente_id,
                'fecha_inicio' => $fechaEntrega,
                'fecha_fin'    => $fechaDevolucion,
                'costo_total'  => $reserva->monto_total,
                'garantia'     => $reserva->garantia_total,
                'estado'       => EstadoAlquiler::Activo,
            ]);

            foreach ($items as $item) {
                $talle = TalleStock::whereKey($item->pivot->talle_id)->lockForUpdate()->firstOrFail();

                if ($talle->cantidad_reservada < $item->pivot->cantidad) {
                    throw ValidationException::withMessages(['reserva' => "Stock reservado insuficiente para el talle {$talle->talle}."]);
                }

                $alquiler->stockItems()->attach($item->id, [
                    'talle_id' => $item->pivot->talle_id,
                    'cantidad' => $item->pivot->cantidad,
                ]);

                $talle->decrement('cantidad_reservada', $item->pivot->cantidad);
                $talle->increment('cantidad_alquilada', $item->pivot->cantidad);
            }

            $observaciones = $reserva->observaciones;
            if (! empty($observacionesEntrega)) {
                $observaciones .= "\n\nEntrega: " . $observacionesEntrega;
            }

            $reserva->update([
                'estado'        => EstadoReserva::Entregada,
                'alquiler_id'   => $alquiler->id,
                'observaciones' => $observaciones,
            ]);

            return $alquiler;
        });
    }

    public function cancelar(Reserva $reserva, int $seniaDevuelta, string $motivo, ?string $observacionesCancelacion): void
    {
        if (in_array($reserva->estado, [EstadoReserva::Entregada, EstadoReserva::Cancelada], true)) {
            throw ValidationException::withMessages(['reserva' => 'No se puede cancelar esta reserva.']);
        }

        if ($seniaDevuelta > $reserva->total_recibido) {
            throw ValidationException::withMessages(['senia_devuelta' => 'No se puede devolver más de lo recibido.']);
        }

        DB::transaction(function () use ($reserva, $seniaDevuelta, $motivo, $observacionesCancelacion) {
            $this->liberarStock($reserva);

            $observaciones = $reserva->observaciones;
            if (! empty($observacionesCancelacion)) {
                $observaciones .= "\n\nCancelación: " . $observacionesCancelacion;
            }

            $reserva->update([
                'estado'            => EstadoReserva::Cancelada,
                'senia_devuelta'    => $seniaDevuelta,
                'motivo_devolucion' => $motivo,
                'observaciones'     => $observaciones,
            ]);
        });
    }

    public function anular(Reserva $reserva): void
    {
        DB::transaction(function () use ($reserva) {
            if ($reserva->estado === EstadoReserva::Entregada) {
                throw ValidationException::withMessages(['reserva' => 'No se puede eliminar una reserva ya entregada.']);
            }

            if ($reserva->estado === EstadoReserva::Confirmada) {
                $this->liberarStock($reserva);
            }

            $reserva->delete();
        });
    }

    /**
     * @param  array<int, array{stock_id:int, talle_id:int, cantidad:int}>  $prendas
     */
    private function reservarPrendas(Reserva $reserva, array $prendas): void
    {
        foreach ($prendas as $prenda) {
            $talle = TalleStock::whereKey($prenda['talle_id'])->lockForUpdate()->firstOrFail();

            if ($talle->cantidad_disponible < $prenda['cantidad']) {
                throw ValidationException::withMessages([
                    'prendas' => "Stock insuficiente para el talle {$talle->talle}. Disponible: {$talle->cantidad_disponible}.",
                ]);
            }

            $reserva->stockItems()->attach($prenda['stock_id'], [
                'talle_id' => $prenda['talle_id'],
                'cantidad' => $prenda['cantidad'],
            ]);

            $talle->decrement('cantidad_disponible', $prenda['cantidad']);
            $talle->increment('cantidad_reservada', $prenda['cantidad']);
        }
    }

    private function liberarStock(Reserva $reserva): void
    {
        foreach ($reserva->stockItems()->withPivot('talle_id', 'cantidad')->get() as $item) {
            $talle = TalleStock::whereKey($item->pivot->talle_id)->lockForUpdate()->first();
            $talle?->increment('cantidad_disponible', $item->pivot->cantidad);
            $talle?->decrement('cantidad_reservada', $item->pivot->cantidad);
        }
    }
}
