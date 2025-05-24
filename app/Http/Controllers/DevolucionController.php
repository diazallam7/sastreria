<?php

namespace App\Http\Controllers;

use App\Models\Alquiler;
use App\Models\Configuracion;
use App\Models\Devolucion;
use App\Models\Vestido;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DevolucionController extends Controller
{
    public function index()
    {
        // Obtener los alquileres con estados 1 (activo)
        $alquileres = Alquiler::with('cliente', 'prendas')
            ->where('estado', 1)
            ->get();

        // Combinar ambas colecciones con un identificador de tipo
        $registros = $alquileres->map(function ($item) {
            $item->tipo = 'alquiler';
            return $item;
        });

        return view('alquileres.devoluciones.index', compact('registros'));
    }

    public function actualizarEstado(Request $request, $id)
    {
        $validated = $request->validate([
            'estado' => 'required|in:2,3', // Validar que el estado sea "reservado" (2) o "alquilado" (3)
        ]);

        DB::beginTransaction();
        
        try {
            if ($validated['estado'] == 3) { // Estado "alquilado"
                // Buscar el alquiler
                $alquiler = Alquiler::with('prendas')->findOrFail($id);

                // Actualizar el estado del alquiler y las prendas asociadas
                $alquiler->update(['estado' => 2]); // Cambiar el estado del alquiler a "finalizado"
                
                foreach ($alquiler->stockItems as $prenda) {
                $prenda->update(['estado' => 1]); // disponible
            }
            }
            
            DB::commit();
            
            return redirect()->route('devoluciones.index')->with('success', 'Prendas Entregadas Correctamente!.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error al actualizar el estado: ' . $e->getMessage()]);
        }
    }

    public function calcularMultas($id)
    {
        // Buscar el alquiler correspondiente con las relaciones necesarias
        $alquiler = Alquiler::with('cliente', 'prendas')->findOrFail($id);

        // Obtener la configuración de la multa diaria
        $multaDiaria = Configuracion::where('nombre', 'multa')->value('valor') ?? 10000; // Valor predeterminado si no existe

        // Calcular días de retraso manualmente
        $fechaFin = strtotime($alquiler->fecha_fin); // Convertir fecha_fin a timestamp
        $fechaActual = strtotime(now()); // Convertir la fecha actual a timestamp

        $diasRetraso = 0;

        // Calcular la diferencia en días (si aplica)
        if ($fechaActual > $fechaFin) {
            $segundosDiferencia = $fechaActual - $fechaFin;
            $diasRetraso = floor($segundosDiferencia / 86400); // 86400 segundos en un día
        }

        // Calcular multa acumulada solo si hay días de retraso
        $multaTotal = $diasRetraso * $multaDiaria;

        // Pasar datos a la vista
        return view('alquileres.devoluciones.multas', [
            'alquiler' => $alquiler,
            'fechaFin' => date('d/m/Y', $fechaFin),
            'fechaActual' => date('d/m/Y', $fechaActual),
            'diasRetraso' => $diasRetraso,
            'multaDiaria' => $multaDiaria,
            'multaTotal' => $multaTotal,
        ]);
    }
}