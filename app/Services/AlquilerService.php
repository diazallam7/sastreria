<?php

namespace App\Services;

use App\Enums\EstadoAlquiler;
use App\Enums\EstadoUnidad;
use App\Models\Alquiler;
use App\Models\TalleStock;
use App\Models\UnidadStock;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

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
                'cliente_id' => $datos['cliente_id'],
                'fecha_inicio' => $datos['fecha_inicio'],
                'fecha_fin' => $datos['fecha_fin'],
                'costo_total' => $datos['costo_total'],
                'garantia' => $datos['garantia'],
                'estado' => EstadoAlquiler::Activo,
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
            $this->liberarUnidades($alquiler);
            $alquiler->unidades()->detach();

            $alquiler->update([
                'cliente_id' => $datos['cliente_id'],
                'fecha_inicio' => $datos['fecha_inicio'],
                'fecha_fin' => $datos['fecha_fin'],
                'costo_total' => $datos['costo_total'],
                'garantia' => $datos['garantia'],
            ]);

            $this->entregarPrendas($alquiler, $prendas);

            return $alquiler;
        });
    }

    public function anular(Alquiler $alquiler): void
    {
        DB::transaction(function () use ($alquiler) {
            if ($alquiler->estaActivo()) {
                $this->liberarUnidades($alquiler);
            }

            $alquiler->delete(); // soft delete
        });
    }

    /**
     * Auto-asigna N unidades físicas disponibles del talle a un alquiler (orden estable por
     * id, bajo lock), las marca `alquilada`, snapshotea el precio y ajusta los contadores del
     * talle. `$origen` indica de qué contador sale el stock: 'disponible' (alquiler directo) o
     * 'reservado' (conversión de una reserva confirmada — ver ReservaService::convertirAAlquiler).
     *
     * @throws ValidationException si no hay stock (contador u unidades físicas) suficiente.
     */
    public function asignarUnidades(Alquiler $alquiler, int $talleId, int $cantidad, string $origen = 'disponible'): void
    {
        $campoOrigen = match ($origen) {
            'disponible' => 'cantidad_disponible',
            'reservado' => 'cantidad_reservada',
            default => throw new InvalidArgumentException("Origen de contador inválido: {$origen}"),
        };

        $talle = TalleStock::with('stock')->whereKey($talleId)->lockForUpdate()->firstOrFail();

        if ($talle->{$campoOrigen} < $cantidad) {
            throw ValidationException::withMessages([
                'prendas' => "Stock insuficiente para el talle {$talle->talle}. Disponible: {$talle->{$campoOrigen}}.",
            ]);
        }

        $unidades = $talle->unidades()->disponibles()->lockForUpdate()->orderBy('id')->limit($cantidad)->get();

        if ($unidades->count() < $cantidad) {
            throw ValidationException::withMessages([
                'prendas' => "Stock insuficiente para el talle {$talle->talle}. Unidades físicas disponibles: {$unidades->count()}.",
            ]);
        }

        $precio = (int) $talle->stock->precio_alquiler;

        foreach ($unidades as $unidad) {
            $unidad->update(['estado' => EstadoUnidad::Alquilada]);
            $alquiler->unidades()->attach($unidad->id, ['precio' => $precio]);
        }

        $talle->decrement($campoOrigen, $cantidad);
        $talle->increment('cantidad_alquilada', $cantidad);
    }

    /**
     * Asigna una unidad física puntual (resuelta por escaneo de su código `ALQ-`) a un alquiler.
     * A diferencia de `asignarUnidades`, no elige por orden: es exactamente la unidad pedida.
     *
     * @throws ValidationException si la unidad no existe o no está disponible.
     */
    public function asignarUnidadEspecifica(Alquiler $alquiler, int $unidadId): void
    {
        $unidad = UnidadStock::whereKey($unidadId)->lockForUpdate()->first();

        if (! $unidad || $unidad->estado !== EstadoUnidad::Disponible) {
            throw ValidationException::withMessages([
                'prendas' => 'Esa unidad no está disponible.',
            ]);
        }

        $talle = TalleStock::with('stock')->whereKey($unidad->talle_stock_id)->lockForUpdate()->firstOrFail();
        $precio = (int) $talle->stock->precio_alquiler;

        $unidad->update(['estado' => EstadoUnidad::Alquilada]);
        $alquiler->unidades()->attach($unidad->id, ['precio' => $precio]);

        $talle->decrement('cantidad_disponible');
        $talle->increment('cantidad_alquilada');
    }

    /** Libera las unidades de un alquiler: vuelven a `disponible` y se restauran los contadores. */
    public function liberarUnidades(Alquiler $alquiler): void
    {
        $unidades = $alquiler->unidades;

        foreach ($unidades->groupBy('talle_stock_id') as $talleId => $grupo) {
            $talle = TalleStock::whereKey($talleId)->lockForUpdate()->first();

            UnidadStock::whereIn('id', $grupo->pluck('id'))->update(['estado' => EstadoUnidad::Disponible->value]);

            $talle?->increment('cantidad_disponible', $grupo->count());
            $talle?->decrement('cantidad_alquilada', $grupo->count());
        }
    }

    /**
     * Unidades pineadas por escaneo (`unidad_ids`) se asignan puntualmente; el resto de la
     * cantidad de esa línea se auto-asigna como siempre.
     *
     * @param  array<int, array{stock_id:int, talle_id:int, cantidad:int, unidad_ids?:array<int,int>}>  $prendas
     */
    private function entregarPrendas(Alquiler $alquiler, array $prendas): void
    {
        foreach ($prendas as $prenda) {
            $unidadIds = $prenda['unidad_ids'] ?? [];

            foreach ($unidadIds as $unidadId) {
                $this->asignarUnidadEspecifica($alquiler, $unidadId);
            }

            $restante = $prenda['cantidad'] - count($unidadIds);

            if ($restante > 0) {
                $this->asignarUnidades($alquiler, $prenda['talle_id'], $restante);
            }
        }
    }
}
