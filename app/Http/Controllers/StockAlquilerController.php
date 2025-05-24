<?php

namespace App\Http\Controllers;

use App\Models\StockAlquiler;
use App\Models\TalleStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockAlquilerController extends Controller
{
    public function index()
    {
        $items = StockAlquiler::with('talles')->get();
        return view('stock.alquiler.index', compact('items'));
    }

    public function create()
    {
        return view('stock.alquiler.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'codigo' => 'required|string|max:50|unique:stock_alquiler',
            'nombre' => 'required|string|max:255',
            'precio_alquiler' => 'required|numeric|min:0',
            'descripcion' => 'nullable|string',
            'talles' => 'required|array',
            'talles.*.talle' => 'required|string|max:20',
            'talles.*.cantidad' => 'required|integer|min:0',
        ]);

        DB::beginTransaction();
        
        try {
            // Crear el registro principal
            $stockItem = StockAlquiler::create([
                'codigo' => $validated['codigo'],
                'nombre' => $validated['nombre'],
                'precio_alquiler' => $validated['precio_alquiler'],
                'descripcion' => $validated['descripcion'],
            ]);
            
            // Crear los registros de talles
            foreach ($validated['talles'] as $talleData) {
                TalleStock::create([
                    'stock_id' => $stockItem->id,
                    'talle' => $talleData['talle'],
                    'cantidad_total' => $talleData['cantidad'],
                    'cantidad_disponible' => $talleData['cantidad'], // Inicialmente, todas están disponibles
                    'cantidad_alquilada' => 0,
                ]);
            }
            
            DB::commit();
            
            return redirect()->route('stock.alquiler.index')
                ->with('success', 'Prenda agregada al stock correctamente');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error al crear la prenda: ' . $e->getMessage()]);
        }
    }

    public function show(StockAlquiler $item)
    {
        $item->load('talles');
        
        // Obtener los alquileres activos de esta prenda
        $alquileres = $item->alquileres()
            ->where('alquileres.estado', 1)
            ->with('cliente')
            ->get();
        
        return view('stock.alquiler.show', compact('item', 'alquileres'));
    }

    public function edit(StockAlquiler $item)
    {
        $item->load('talles');
        return view('stock.alquiler.edit', compact('item'));
    }

    public function update(Request $request, StockAlquiler $item)
    {
        $validated = $request->validate([
            'codigo' => 'required|string|max:50|unique:stock_alquiler,codigo,' . $item->id,
            'nombre' => 'required|string|max:255',
            'precio_alquiler' => 'required|numeric|min:0',
            'descripcion' => 'nullable|string',
            'talles' => 'required|array',
            'talles.*.id' => 'nullable|exists:talle_stock,id',
            'talles.*.talle' => 'required|string|max:20',
            'talles.*.cantidad' => 'required|integer|min:0',
        ]);

        DB::beginTransaction();
        
        try {
            // Actualizar el registro principal
            $item->update([
                'codigo' => $validated['codigo'],
                'nombre' => $validated['nombre'],
                'precio_alquiler' => $validated['precio_alquiler'],
                'descripcion' => $validated['descripcion'],
            ]);
            
            // Procesar los talles
            $talleIds = [];
            
            foreach ($validated['talles'] as $talleData) {
                if (isset($talleData['id'])) {
                    // Actualizar talle existente
                    $talle = TalleStock::find($talleData['id']);
                    
                    // Calcular la diferencia para ajustar la cantidad disponible
                    $diferencia = $talleData['cantidad'] - $talle->cantidad_total;
                    
                    $talle->update([
                        'talle' => $talleData['talle'],
                        'cantidad_total' => $talleData['cantidad'],
                        'cantidad_disponible' => max(0, $talle->cantidad_disponible + $diferencia),
                    ]);
                    
                    $talleIds[] = $talle->id;
                } else {
                    // Crear nuevo talle
                    $talle = TalleStock::create([
                        'stock_id' => $item->id,
                        'talle' => $talleData['talle'],
                        'cantidad_total' => $talleData['cantidad'],
                        'cantidad_disponible' => $talleData['cantidad'],
                        'cantidad_alquilada' => 0,
                    ]);
                    
                    $talleIds[] = $talle->id;
                }
            }
            
            // Eliminar talles que ya no están en la lista
            $item->talles()->whereNotIn('id', $talleIds)->delete();
            
            DB::commit();
            
            return redirect()->route('stock.alquiler.index')
                ->with('success', 'Prenda actualizada correctamente');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error al actualizar la prenda: ' . $e->getMessage()]);
        }
    }

    public function destroy(StockAlquiler $item)
    {
        // Verificar si la prenda está en uso en algún alquiler activo
        if ($item->alquileres()->whereHas('alquileres', function($q) {
            $q->where('estado', 1);
        })->count() > 0) {
            return back()->withErrors(['error' => 'No se puede eliminar esta prenda porque está en uso en un alquiler activo.']);
        }
        
        $item->delete();
        
        return redirect()->route('stock.alquiler.index')
            ->with('success', 'Prenda eliminada correctamente');
    }
}