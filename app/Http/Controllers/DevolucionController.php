<?php
// Archivo: app/Http/Controllers/DevolucionController.php

namespace App\Http\Controllers;

use App\Models\Alquiler;
use App\Models\Configuracion;
use App\Models\Devolucion;
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
            'observaciones' => 'nullable|string|max:1000',
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
            $diasRetraso = $fechaFin->diffInDays($fechaDevolucion); // o diffInDays($fechaFin, true)
        }


            // Calcular multa total
            $multaTotal = $diasRetraso * $multaDiaria;

            // Calcular monto a devolver
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
                'observaciones'     => $request->observaciones,
            ]);

                                                // Actualizar estado del alquiler
            $alquiler->update(['estado' => 2]); // finalizado

            // Actualizar estado de las prendas
            foreach ($alquiler->stockItems as $prenda) {
                $prenda->update(['estado' => 1]); // disponible
            }

            DB::commit();

            return redirect()->route('devoluciones.comprobante', $devolucion->id)
                ->with('success', 'Devolución procesada correctamente');

        } catch (\Exception $e) {
            DB::rollBack();
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
    public function actualizarEstado(Request $request, $id)
    {
        $validated = $request->validate([
            'estado' => 'required|in:2,3',
        ]);

        DB::beginTransaction();

        try {
            if ($validated['estado'] == 3) {
                $alquiler = Alquiler::with('stockItems')->findOrFail($id);

                // Calcular automáticamente la devolución SOLO POR FECHAS
                $multaDiaria     = Configuracion::where('nombre', 'multa')->value('valor') ?? 10000;
                $fechaFin        = Carbon::parse($alquiler->fecha_fin)->startOfDay();
                $fechaDevolucion = Carbon::now()->startOfDay();

                $diasRetraso = 0;
        if ($fechaDevolucion->gt($fechaFin)) {
            $diasRetraso = $fechaFin->diffInDays($fechaDevolucion); // o diffInDays($fechaFin, true)
        }

                $multaTotal       = $diasRetraso * $multaDiaria;
                $garantiaOriginal = $alquiler->garantia ?? 0;
                $montoDevolver    = max(0, $garantiaOriginal - $multaTotal);

                // Crear registro de devolución
                Devolucion::create([
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

                $alquiler->update(['estado' => 2]);

                foreach ($alquiler->stockItems as $prenda) {
                    $prenda->update(['estado' => 1]);
                }
            }

            DB::commit();

            return redirect()->route('devoluciones.index')
                ->with('success', 'Devolución procesada correctamente. Monto a devolver: ₲ ' . number_format($montoDevolver ?? 0, 0, ',', '.'));

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al procesar la devolución: ' . $e->getMessage());
        }
    }
}
