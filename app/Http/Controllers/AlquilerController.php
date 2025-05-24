<?php

namespace App\Http\Controllers;

use App\Models\Alquiler;
use App\Models\Cliente;
use App\Models\StockAlquiler;
use App\Models\TalleStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AlquilerController extends Controller
{
    public function index()
    {
        $alquileres = Alquiler::with('cliente', 'stockItems')->get();
        
        // Calcular días de alquiler para cada alquiler
        foreach ($alquileres as $alquiler) {
            $fechaInicio = Carbon::parse($alquiler->fecha_inicio);
            $fechaFin = Carbon::parse($alquiler->fecha_fin);
            $alquiler->dias = $fechaInicio->diffInDays($fechaFin) + 1; // +1 para incluir el día de inicio
        }
        
        return view('alquileres.index', compact('alquileres'));
    }
    
    public function create()
    {
        $clientes = Cliente::all();
        $prendas = StockAlquiler::with('talles')
            ->whereHas('talles', function($query) {
                $query->where('cantidad_disponible', '>', 0);
            })
            ->get();
        
        return view('alquileres.create', compact('clientes', 'prendas'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'prendas' => 'required|array',
            'prendas.*.stock_id' => 'required|exists:stock_alquiler,id',
            'prendas.*.talle_id' => 'required|exists:talle_stock,id',
            'prendas.*.cantidad' => 'required|integer|min:1',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after:fecha_inicio',
            'costo_total' => 'required|numeric|min:0',
            'garantia' => 'required|numeric|min:0',
        ]);
        
        DB::beginTransaction();
        
        try {
            // Crear el alquiler
            $alquiler = new Alquiler();
            $alquiler->cliente_id = $validated['cliente_id'];
            $alquiler->fecha_inicio = $validated['fecha_inicio'];
            $alquiler->fecha_fin = $validated['fecha_fin'];
            $alquiler->costo_total = $validated['costo_total'];
            $alquiler->garantia = $validated['garantia'];
            $alquiler->estado = 1; // activo
            $alquiler->save();
            
            // Asociar las prendas al alquiler y actualizar el stock
            foreach ($validated['prendas'] as $prendaData) {
                $stockId = $prendaData['stock_id'];
                $talleId = $prendaData['talle_id'];
                $cantidad = $prendaData['cantidad'];
                
                // Asociar la prenda al alquiler con el talle y cantidad
                $alquiler->stockItems()->attach($stockId, [
                    'talle_id' => $talleId,
                    'cantidad' => $cantidad
                ]);
                
                // Actualizar el stock del talle
                $talle = TalleStock::findOrFail($talleId);
                
                // Verificar que hay suficiente stock disponible
                if ($talle->cantidad_disponible < $cantidad) {
                    throw new \Exception("No hay suficiente stock disponible para el talle {$talle->talle}");
                }
                
                // Actualizar cantidades
                $talle->cantidad_disponible -= $cantidad;
                $talle->cantidad_alquilada += $cantidad;
                $talle->save();
            }
            
            DB::commit();
            
            return redirect()->route('alquileres.index')->with('success', 'Alquiler creado correctamente');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error al crear el alquiler: ' . $e->getMessage()]);
        }
    }

    public function destroy(Alquiler $alquiler)
    {
        DB::beginTransaction();
        
        try {
            // Obtener las relaciones de alquiler_stock antes de eliminar
            $stockItems = $alquiler->stockItems()->withPivot('talle_id', 'cantidad')->get();
            
            // Actualizar el stock de cada prenda
            foreach ($stockItems as $item) {
                $talle = TalleStock::findOrFail($item->pivot->talle_id);
                
                // Devolver las prendas al stock disponible
                $talle->cantidad_disponible += $item->pivot->cantidad;
                $talle->cantidad_alquilada -= $item->pivot->cantidad;
                $talle->save();
            }
            
            // Eliminar el alquiler (esto también eliminará las relaciones en la tabla pivote)
            $alquiler->delete();
            
            DB::commit();
            
            return redirect()->route('alquileres.index')->with('success', 'Alquiler eliminado correctamente');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error al eliminar el alquiler: ' . $e->getMessage()]);
        }
    }

    public function devolver(Alquiler $alquiler)
    {
        DB::beginTransaction();
        
        try {
            // Obtener las relaciones de alquiler_stock
            $stockItems = $alquiler->stockItems()->withPivot('talle_id', 'cantidad')->get();
            
            // Actualizar el stock de cada prenda
            foreach ($stockItems as $item) {
                $talle = TalleStock::findOrFail($item->pivot->talle_id);
                
                // Devolver las prendas al stock disponible
                $talle->cantidad_disponible += $item->pivot->cantidad;
                $talle->cantidad_alquilada -= $item->pivot->cantidad;
                $talle->save();
            }
            
            // Cambiar el estado del alquiler a "completado"
            $alquiler->update(['estado' => 2]); // completado
            
            DB::commit();
            
            return redirect()->route('alquileres.index')->with('success', 'Las prendas fueron devueltas con éxito.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error al devolver las prendas: ' . $e->getMessage()]);
        }
    }
}