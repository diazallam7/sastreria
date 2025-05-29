<?php
// Archivo: app/Http/Controllers/ProductoVentaController.php

namespace App\Http\Controllers;

use App\Models\ProductoVenta;
use App\Models\TalleProductoVenta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductoVentaController extends Controller
{
    public function index()
    {
        $productos = ProductoVenta::with('talles')->get();
        return view('productos-venta.index', compact('productos'));
    }

    public function create()
    {
        return view('productos-venta.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre_producto' => 'required|string|max:255',
            'precio_venta' => 'required|numeric|min:0',
            'observacion' => 'nullable|string',
            'talles' => 'required|array',
            'talles.*.talle' => 'required|string|max:20',
            'talles.*.cantidad' => 'required|integer|min:0',
        ]);

        DB::beginTransaction();
        
        try {
            // Crear el producto
            $producto = ProductoVenta::create([
                'nombre_producto' => $validated['nombre_producto'],
                'precio_venta' => $validated['precio_venta'],
                'observacion' => $validated['observacion'],
            ]);
            
            // Crear los registros de talles
            foreach ($validated['talles'] as $talleData) {
                TalleProductoVenta::create([
                    'producto_venta_id' => $producto->id,
                    'talle' => $talleData['talle'],
                    'cantidad_total' => $talleData['cantidad'],
                    'cantidad_disponible' => $talleData['cantidad'],
                    'cantidad_vendida' => 0,
                ]);
            }
            
            DB::commit();
            
            return redirect()->route('productos-venta.index')
                ->with('success', 'Producto agregado correctamente');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error al agregar el producto: ' . $e->getMessage()]);
        }
    }

    public function show(ProductoVenta $producto)
    {
        $producto->load('talles');
        return view('productos-venta.show', compact('producto'));
    }

    public function edit(ProductoVenta $producto)
    {
        $producto->load('talles');
        return view('productos-venta.edit', compact('producto'));
    }

    public function update(Request $request, ProductoVenta $producto)
    {
        $validated = $request->validate([
            'nombre_producto' => 'required|string|max:255',
            'precio_venta' => 'required|numeric|min:0',
            'observacion' => 'nullable|string',
            'talles' => 'required|array',
            'talles.*.id' => 'nullable|exists:talle_producto_venta,id',
            'talles.*.talle' => 'required|string|max:20',
            'talles.*.cantidad' => 'required|integer|min:0',
        ]);

        DB::beginTransaction();
        
        try {
            // Actualizar el producto
            $producto->update([
                'nombre_producto' => $validated['nombre_producto'],
                'precio_venta' => $validated['precio_venta'],
                'observacion' => $validated['observacion'],
            ]);
            
            // Procesar los talles
            $talleIds = [];
            
            foreach ($validated['talles'] as $talleData) {
                if (isset($talleData['id'])) {
                    // Actualizar talle existente
                    $talle = TalleProductoVenta::find($talleData['id']);
                    
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
                    $talle = TalleProductoVenta::create([
                        'producto_venta_id' => $producto->id,
                        'talle' => $talleData['talle'],
                        'cantidad_total' => $talleData['cantidad'],
                        'cantidad_disponible' => $talleData['cantidad'],
                        'cantidad_vendida' => 0,
                    ]);
                    
                    $talleIds[] = $talle->id;
                }
            }
            
            // Eliminar talles que ya no están en la lista
            $producto->talles()->whereNotIn('id', $talleIds)->delete();
            
            DB::commit();
            
            return redirect()->route('productos-venta.index')
                ->with('success', 'Producto actualizado correctamente');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error al actualizar el producto: ' . $e->getMessage()]);
        }
    }

    public function destroy(ProductoVenta $producto)
    {
        // Verificar si el producto tiene ventas asociadas
        if ($producto->ventas()->count() > 0) {
            return back()->withErrors(['error' => 'No se puede eliminar este producto porque tiene ventas asociadas.']);
        }
        
        $producto->delete();
        
        return redirect()->route('productos-venta.index')
            ->with('success', 'Producto eliminado correctamente');
    }
}