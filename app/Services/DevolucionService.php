<?php

namespace App\Services;

use App\Enums\EstadoAlquiler;
use App\Models\Alquiler;
use App\Models\Configuracion;
use App\Models\Devolucion;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DevolucionService
{
    public function __construct(private AlquilerService $alquilerService) {}

    /**
     * Calcula (sin persistir) la multa y el monto a devolver de un alquiler.
     *
     * @return array{multa_diaria:int, dias_retraso:int, multa_calculada:int, garantia_original:int, monto_devuelto:int}
     */
    public function calcular(Alquiler $alquiler): array
    {
        $multaDiaria = (int) (Configuracion::where('nombre', 'multa')->value('valor') ?? 10000);

        $fechaFin = Carbon::parse($alquiler->fecha_fin)->startOfDay();
        $hoy = Carbon::now()->startOfDay();
        $dias = $hoy->gt($fechaFin) ? $fechaFin->diffInDays($hoy) : 0;

        $multaCalculada = $dias * $multaDiaria;
        $garantia = (int) $alquiler->garantia;

        return [
            'multa_diaria' => $multaDiaria,
            'dias_retraso' => $dias,
            'multa_calculada' => $multaCalculada,
            'garantia_original' => $garantia,
            'monto_devuelto' => max(0, $garantia - $multaCalculada),
        ];
    }

    /**
     * Procesa la devolución de un alquiler de forma atómica: registra la
     * devolución, restaura el stock (con locks) y marca el alquiler completado.
     *
     * Camino ÚNICO de devolución: el guard anti doble-devolución vive aquí, así
     * que todos los que llaman (AlquilerController, DevolucionController) quedan
     * protegidos. El monto devuelto se calcula en el servidor (no se confía en
     * el cliente); solo la multa aplicada puede ajustarse manualmente.
     *
     * @throws ValidationException si el alquiler no está activo.
     */
    public function procesar(
        Alquiler $alquiler,
        ?int $multaAplicada = null,
        ?string $motivoAjuste = null,
        ?string $observaciones = null,
    ): Devolucion {
        return DB::transaction(function () use ($alquiler, $multaAplicada, $motivoAjuste, $observaciones) {
            // Bloquea la fila y re-verifica el estado: evita doble devolución concurrente.
            $alquiler = Alquiler::whereKey($alquiler->id)->lockForUpdate()->firstOrFail();

            if (! $alquiler->estaActivo()) {
                throw ValidationException::withMessages([
                    'alquiler' => 'Este alquiler ya fue devuelto o cancelado.',
                ]);
            }

            $calc = $this->calcular($alquiler);
            $aplicada = $multaAplicada ?? $calc['multa_calculada'];

            $devolucion = Devolucion::create([
                'alquiler_id' => $alquiler->id,
                'user_id' => auth()->id(),
                'fecha_devolucion' => now(),
                'retraso' => $calc['dias_retraso'] > 0,
                'dias_retraso' => $calc['dias_retraso'],
                'multa_calculada' => $calc['multa_calculada'],
                'multa_aplicada' => $aplicada,
                'garantia_original' => $calc['garantia_original'],
                'monto_devuelto' => max(0, $calc['garantia_original'] - $aplicada),
                'motivo_ajuste' => $motivoAjuste,
                'observaciones' => $observaciones,
            ]);

            $this->alquilerService->liberarUnidades($alquiler);

            $alquiler->update(['estado' => EstadoAlquiler::Completado]);

            return $devolucion;
        });
    }
}
