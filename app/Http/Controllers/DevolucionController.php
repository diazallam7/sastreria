<?php
// Archivo: app/Http/Controllers/DevolucionController.php

namespace App\Http\Controllers;

use App\Models\Alquiler;
use App\Models\Configuracion;
use App\Models\Devolucion;
use App\Models\TalleStock;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DevolucionController extends Controller
{
    public function index()
    {
        // Obtener los alquileres con estados 1 (activo)
        $alquileres = Alquiler::with('cliente', 'stockItems')
            ->where('estado', 1)
            ->get();

        // Combinar con un identificador de tipo
        $registros = $alquileres->map(function ($item) {
            $item->tipo = 'alquiler';
            return $item;
        });

        return view('alquileres.devoluciones.index', compact('registros'));
    }

    public function calcularMultas($id)
    {
        // Buscar el alquiler correspondiente
        $alquiler = Alquiler::with('cliente', 'stockItems')->findOrFail($id);

        // Obtener la configuración de la multa diaria
        $multaDiaria = Configuracion::where('nombre', 'multa')->value('valor') ?? 10000;

        // Calcular días de retraso SOLO POR FECHAS (sin considerar horas)
        $fechaFin    = Carbon::parse($alquiler->fecha_fin)->startOfDay();
        $fechaActual = Carbon::now()->startOfDay(); // Inicio del día actual

        $diasRetraso = 0;
        if ($fechaActual->gt($fechaFin)) {
            $diasRetraso = $fechaFin->diffInDays($fechaActual); // o diffInDays($fechaFin, true)
        }

        // Calcular multa total
        $multaTotal = $diasRetraso * $multaDiaria;

        // Calcular monto a devolver
        $garantiaOriginal = $alquiler->garantia ?? 0;
        $montoDevolver    = max(0, $garantiaOriginal - $multaTotal);

        return view('alquileres.devoluciones.multas', [
            'alquiler'         => $alquiler,
            'fechaFin'         => $fechaFin->format('d/m/Y'),
            'fechaActual'      => $fechaActual->format('d/m/Y'),
            'diasRetraso'      => $diasRetraso,
            'multaDiaria'      => $multaDiaria,
            'multaTotal'       => $multaTotal,
            'garantiaOriginal' => $garantiaOriginal,
            'montoDevolver'    => $montoDevolver,
        ]);
    }

    public function procesarDevolucion(Request $request, $id)
    {
        $request->validate([
            'observaciones'            => 'nullable|string|max:1000',
            'multa_calculada'          => 'required|numeric|min:0',
            'monto_devuelto_calculado' => 'required|numeric|min:0',
            'multa_aplicada_real'      => 'required|numeric|min:0',
            'monto_devuelto_real'      => 'required|numeric|min:0',
            'motivo_ajuste'            => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();

        try {
            $alquiler = Alquiler::with('stockItems')->findOrFail($id);

            // Obtener configuración de multa
            $multaDiaria = Configuracion::where('nombre', 'multa')->value('valor') ?? 10000;

            // Calcular días de retraso SOLO POR FECHAS
            $fechaFin        = Carbon::parse($alquiler->fecha_fin)->startOfDay();
            $fechaDevolucion = Carbon::now()->startOfDay();

            $diasRetraso = 0;
            if ($fechaDevolucion->gt($fechaFin)) {
                $diasRetraso = $fechaDevolucion->diffInDays($fechaFin);
            }

            $garantiaOriginal = $alquiler->garantia ?? 0;

            // Crear registro de devolución con valores calculados Y reales
            $devolucion = Devolucion::create([
                'alquiler_id'         => $alquiler->id,
                'fecha_devolucion'    => $fechaDevolucion,
                'retraso'             => $diasRetraso > 0 ? 1 : 0,
                'multa'               => $request->multa_calculada, // Multa calculada automáticamente
                'multa_calculada'     => $request->multa_calculada,
                'multa_aplicada_real' => $request->multa_aplicada_real, // Multa realmente aplicada
                'garantia_original'   => $garantiaOriginal,
                'multa_aplicada'      => $request->multa_aplicada_real,      // Para compatibilidad
                'monto_devuelto'      => $request->monto_devuelto_calculado, // Monto calculado
                'monto_devuelto_real' => $request->monto_devuelto_real,      // Monto realmente devuelto
                'dias_retraso'        => $diasRetraso,
                'observaciones'       => $request->observaciones,
                'motivo_ajuste'       => $request->motivo_ajuste,
            ]);

                                                // Actualizar estado del alquiler
            $alquiler->update(['estado' => 2]); // finalizado

            // Actualizar el stock de las prendas
            foreach ($alquiler->stockItems as $stockItem) {
                $stockItem->update(['estado' => 1]); // disponible

                $pivotData = $stockItem->pivot;

                if ($pivotData && isset($pivotData->talle_id) && $pivotData->talle_id) {
                    $talleStock = TalleStock::find($pivotData->talle_id);

                    if ($talleStock) {
                        $talleStock->increment('cantidad_disponible', $pivotData->cantidad);
                        $talleStock->decrement('cantidad_alquilada', $pivotData->cantidad);
                    }
                } else {
                    $talleStock = TalleStock::where('stock_id', $stockItem->id)->first();

                    if ($talleStock) {
                        $talleStock->increment('cantidad_disponible', $pivotData->cantidad ?? 1);
                        $talleStock->decrement('cantidad_alquilada', $pivotData->cantidad ?? 1);
                    }
                }
            }

            DB::commit();

            return redirect()->route('devoluciones.comprobante', $devolucion->id)
                ->with('success', 'Devolución procesada correctamente. Monto devuelto: ₲ ' . number_format($request->monto_devuelto_real, 0, ',', '.'));

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al procesar la devolución: ' . $e->getMessage());
        }
    }

    // Método actualizado para el proceso simple
    public function actualizarEstado(Request $request, $id)
    {
        $validated = $request->validate([
            'estado' => 'required|in:2,3',
        ]);

        DB::beginTransaction();

        try {
            if ($validated['estado'] == 3) {
                $alquiler = Alquiler::with('stockItems')->findOrFail($id);

                // Calcular automáticamente la devolución
                $multaDiaria     = Configuracion::where('nombre', 'multa')->value('valor') ?? 10000;
                $fechaFin        = Carbon::parse($alquiler->fecha_fin)->startOfDay();
                $fechaDevolucion = Carbon::now()->startOfDay();

                $diasRetraso = 0;
                if ($fechaDevolucion->gt($fechaFin)) {
                    $diasRetraso = $fechaDevolucion->diffInDays($fechaFin);
                }

                $multaTotal       = $diasRetraso * $multaDiaria;
                $garantiaOriginal = $alquiler->garantia ?? 0;
                $montoDevolver    = max(0, $garantiaOriginal - $multaTotal);

                // Crear registro de devolución
                $devolucion = Devolucion::create([
                    'alquiler_id'       => $alquiler->id,
                    'fecha_devolucion'  => $fechaDevolucion,
                    'retraso'           => $diasRetraso > 0 ? 1 : 0,
                    'multa'             => $multaTotal,
                    'garantia_original' => $garantiaOriginal,
                    'multa_aplicada'    => $multaTotal,
                    'monto_devuelto'    => $montoDevolver,
                    'dias_retraso'      => $diasRetraso,
                    'observaciones'     => 'Devolución procesada automáticamente',
                ]);

                // Actualizar estado del alquiler
                $alquiler->update(['estado' => 2]);

                // IMPORTANTE: Actualizar el stock de las prendas
                foreach ($alquiler->stockItems as $stockItem) {
                    // Cambiar estado a disponible
                    $stockItem->update(['estado' => 1]);

                    // Obtener la información del pivot
                    $pivotData = $stockItem->pivot;

                    // VERIFICAR SI talle_id EXISTE Y NO ES NULL
                    if ($pivotData && isset($pivotData->talle_id) && $pivotData->talle_id) {
                        // CORREGIDO: El talle_id en el pivot es el ID del talle_stock
                        $talleStock = TalleStock::find($pivotData->talle_id);

                        if ($talleStock) {
                            // Incrementar la cantidad disponible
                            $talleStock->increment('cantidad_disponible', $pivotData->cantidad);
                            // Decrementar la cantidad alquilada
                            $talleStock->decrement('cantidad_alquilada', $pivotData->cantidad);
                        }
                    } else {
                        // Si no hay talle_id, buscar por el stock_id
                        $talleStock = TalleStock::where('stock_id', $stockItem->id)->first();

                        if ($talleStock) {
                            // Incrementar la cantidad disponible
                            $talleStock->increment('cantidad_disponible', $pivotData->cantidad ?? 1);
                            // Decrementar la cantidad alquilada
                            $talleStock->decrement('cantidad_alquilada', $pivotData->cantidad ?? 1);
                        }
                    }
                }
            }

            DB::commit();

            if ($request->expectsJson()) {
                return response()->json([
                    'success'        => true,
                    'message'        => 'Devolución procesada correctamente',
                    'monto_devolver' => $montoDevolver ?? 0,
                ]);
            }

            return redirect()->route('devoluciones.index')
                ->with('success', 'Devolución procesada correctamente. Monto a devolver: ₲ ' . number_format($montoDevolver ?? 0, 0, ',', '.'));

        } catch (\Exception $e) {
            DB::rollBack();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al procesar la devolución: ' . $e->getMessage(),
                ], 500);
            }

            return back()->with('error', 'Error al procesar la devolución: ' . $e->getMessage());
        }
    }
    public function comprobante($id)
    {
        $devolucion = Devolucion::with(['alquiler.cliente', 'alquiler.stockItems'])->findOrFail($id);

        return view('alquileres.devoluciones.comprobante', compact('devolucion'));
    }

    public function historial()
    {
        $devoluciones = Devolucion::with(['alquiler.cliente'])
            ->orderBy('fecha_devolucion', 'desc')
            ->paginate(15);

        return view('alquileres.devoluciones.historial', compact('devoluciones'));
    }

    // Método actualizado para el proceso simple

}
