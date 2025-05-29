<?php
// Archivo: app/Http/Controllers/CompraController.php

namespace App\Http\Controllers;

use App\Models\Compra;
use App\Models\TalleCompra;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Support\Facades\DB;

class CompraController extends Controller implements HasMiddleware
{

    public static function middleware(): array {

       return [
        
          new Middleware(\Spatie\Permission\Middleware\PermissionMiddleware::using('ver-role|crear-role|editar-role|eliminar-role'),only:['index']),
         new Middleware(\Spatie\Permission\Middleware\PermissionMiddleware::using('crear-role'), only:['create','store']),
         new Middleware(\Spatie\Permission\Middleware\PermissionMiddleware::using('editar-role'),only:['edit','update']),
         new Middleware(\Spatie\Permission\Middleware\PermissionMiddleware::using('eliminar-role'), only:['destroy']),
        ]; 
     }
    public function index()
    {
        $compras = Compra::with('talles')->get();
        return view('compras.index', compact('compras'));
    }

    public function create()
    {
        return view('compras.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre_producto' => 'required|string|max:255',
            'fecha_compra' => 'required|date',
            'precio_compra' => 'required|numeric|min:0',
            'precio_venta' => 'required|numeric|min:0',
            'observacion' => 'nullable|string',
            'talles' => 'required|array',
            'talles.*.talle' => 'required|string|max:20',
            'talles.*.cantidad' => 'required|integer|min:0',
        ]);

        DB::beginTransaction();
        
        try {
            // Crear la compra
            $compra = Compra::create([
                'nombre_producto' => $validated['nombre_producto'],
                'fecha_compra' => $validated['fecha_compra'],
                'precio_compra' => $validated['precio_compra'],
                'precio_venta' => $validated['precio_venta'],
                'observacion' => $validated['observacion'],
                'activo_para_venta' => false,
            ]);
            
            // Crear los registros de talles
            foreach ($validated['talles'] as $talleData) {
                TalleCompra::create([
                    'compra_id' => $compra->id,
                    'talle' => $talleData['talle'],
                    'cantidad_total' => $talleData['cantidad'],
                    'cantidad_disponible' => $talleData['cantidad'],
                    'cantidad_vendida' => 0,
                ]);
            }
            
            DB::commit();
            
            return redirect()->route('compras.index')
                ->with('success', 'Compra registrada correctamente');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error al registrar la compra: ' . $e->getMessage()]);
        }
    }

    public function show(Compra $compra)
    {
        $compra->load('talles');
        return view('compras.show', compact('compra'));
    }

    public function edit(Compra $compra)
    {
        $compra->load('talles');
        return view('compras.edit', compact('compra'));
    }

    public function update(Request $request, Compra $compra)
    {
        $validated = $request->validate([
            'nombre_producto' => 'required|string|max:255',
            'fecha_compra' => 'required|date',
            'precio_compra' => 'required|numeric|min:0',
            'precio_venta' => 'required|numeric|min:0',
            'observacion' => 'nullable|string',
            'talles' => 'required|array',
            'talles.*.id' => 'nullable|exists:talle_compra,id',
            'talles.*.talle' => 'required|string|max:20',
            'talles.*.cantidad' => 'required|integer|min:0',
        ]);

        DB::beginTransaction();
        
        try {
            // Actualizar la compra
            $compra->update([
                'nombre_producto' => $validated['nombre_producto'],
                'fecha_compra' => $validated['fecha_compra'],
                'precio_compra' => $validated['precio_compra'],
                'precio_venta' => $validated['precio_venta'],
                'observacion' => $validated['observacion'],
            ]);
            
            // Procesar los talles
            $talleIds = [];
            
            foreach ($validated['talles'] as $talleData) {
                if (isset($talleData['id'])) {
                    // Actualizar talle existente
                    $talle = TalleCompra::find($talleData['id']);
                    
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
                    $talle = TalleCompra::create([
                        'compra_id' => $compra->id,
                        'talle' => $talleData['talle'],
                        'cantidad_total' => $talleData['cantidad'],
                        'cantidad_disponible' => $talleData['cantidad'],
                        'cantidad_vendida' => 0,
                    ]);
                    
                    $talleIds[] = $talle->id;
                }
            }
            
            // Eliminar talles que ya no están en la lista
            $compra->talles()->whereNotIn('id', $talleIds)->delete();
            
            DB::commit();
            
            return redirect()->route('compras.index')
                ->with('success', 'Compra actualizada correctamente');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error al actualizar la compra: ' . $e->getMessage()]);
        }
    }

    public function destroy(Compra $compra)
    {
        // Verificar si la compra tiene ventas asociadas
        if ($compra->ventas()->count() > 0) {
            return back()->withErrors(['error' => 'No se puede eliminar esta compra porque tiene ventas asociadas.']);
        }
        
        $compra->delete();
        
        return redirect()->route('compras.index')
            ->with('success', 'Compra eliminada correctamente');
    }

    public function activarParaVenta(Compra $compra)
    {
        $compra->update(['activo_para_venta' => true]);
        
        return redirect()->route('compras.index')
            ->with('success', 'Producto activado para venta correctamente');
    }

    public function desactivarParaVenta(Compra $compra)
    {
        $compra->update(['activo_para_venta' => false]);
        
        return redirect()->route('compras.index')
            ->with('success', 'Producto desactivado para venta correctamente');
    }
}