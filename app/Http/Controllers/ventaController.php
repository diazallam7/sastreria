<?php
// Archivo: app/Http/Controllers/VentaController.php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Compra;
use App\Models\DetalleVenta;
use App\Models\ProductoVenta;
use App\Models\TalleCompra;
use App\Models\TalleProductoVenta;
use App\Models\Venta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VentaController extends Controller
{
    public function index()
    {
        $ventas = Venta::with(['cliente', 'detalles'])->latest()->paginate(10);
        return view('ventas.index', compact('ventas'));
    }

    public function create()
    {
        $clientes = Cliente::all();
        $compras  = Compra::with(['talles' => function ($query) {
            $query->where('cantidad_disponible', '>', 0);
        }])->get();
        $productosVenta = ProductoVenta::with(['talles' => function ($query) {
            $query->where('cantidad_disponible', '>', 0);
        }])->get();

        return view('ventas.create', compact('clientes', 'compras', 'productosVenta'));
    }

    public function store(Request $request)
    {
        Log::info('Datos recibidos:', $request->all());

        $request->validate([
            'cliente_id'                  => 'required|exists:clientes,id',
            'fecha_venta'                 => 'required|date',
            'precio_total'                => 'required|numeric|min:0',
            'productos'                   => 'required|array|min:1',
            'productos.*.tipo_producto'   => 'required|in:compra,manual',
            'productos.*.producto_id'     => 'required|integer',
            'productos.*.talle_id'        => 'required|integer',
            'productos.*.cantidad'        => 'required|integer|min:1',
            'productos.*.precio_unitario' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            // Crear la venta principal
            $venta = Venta::create([
                'cliente_id'   => $request->cliente_id,
                'fecha_venta'  => $request->fecha_venta,
                'precio_total' => $request->precio_total,
            ]);

            Log::info('Venta creada:', ['venta_id' => $venta->id]);

            // Procesar cada producto
            foreach ($request->productos as $index => $producto) {
                Log::info('Procesando producto:', ['index' => $index, 'producto' => $producto]);

                // Verificar stock antes de crear el detalle
                if ($producto['tipo_producto'] === 'compra') {
                    $talle = TalleCompra::findOrFail($producto['talle_id']);
                } else {
                    $talle = TalleProductoVenta::findOrFail($producto['talle_id']);
                }

                if ($talle->cantidad_disponible < $producto['cantidad']) {
                    throw new \Exception('Stock insuficiente para el producto seleccionado. Stock disponible: ' . $talle->cantidad_disponible);
                }

                // Crear detalle de venta
                $detalle = DetalleVenta::create([
                    'venta_id'        => $venta->id,
                    'tipo_producto'   => $producto['tipo_producto'],
                    'producto_id'     => $producto['producto_id'],
                    'talle_id'        => $producto['talle_id'],
                    'cantidad'        => $producto['cantidad'],
                    'precio_unitario' => $producto['precio_unitario'],
                    'subtotal'        => $producto['precio_unitario'] * $producto['cantidad'],
                ]);

                Log::info('Detalle creado:', ['detalle_id' => $detalle->id]);

                // Reducir el stock
                $talle->cantidad_disponible -= $producto['cantidad'];
                $talle->save();

                Log::info('Stock actualizado:', ['talle_id' => $talle->id, 'nuevo_stock' => $talle->cantidad_disponible]);
            }

            DB::commit();
            Log::info('Venta completada exitosamente');

            return redirect()->route('ventas.index')->with('success', 'Venta registrada exitosamente');
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error al registrar venta:', ['error' => $e->getMessage()]);

            return back()->with('error', 'Error al registrar la venta: ' . $e->getMessage())->withInput();
        }
    }

    public function show($id)
    {
        $venta = Venta::with(['cliente', 'detalles'])->findOrFail($id);

        // Obtener información detallada de cada producto
        $detallesConProductos = $venta->detalles->map(function ($detalle) {
            if ($detalle->tipo_producto === 'compra') {
                $producto = Compra::find($detalle->producto_id);
                $talle    = TalleCompra::find($detalle->talle_id);
            } else {
                $producto = ProductoVenta::find($detalle->producto_id);
                $talle    = TalleProductoVenta::find($detalle->talle_id);
            }

            return [
                'detalle'  => $detalle,
                'producto' => $producto,
                'talle'    => $talle,
            ];
        });

        return view('ventas.show', compact('venta', 'detallesConProductos'));
    }

    public function edit($id)
    {
        $venta    = Venta::with(['detalles'])->findOrFail($id);
        $clientes = Cliente::all();

        $compras = Compra::with(['talles' => function ($query) {
            $query->where('cantidad_disponible', '>', 0);
        }])->get();

        $productosVenta = ProductoVenta::with(['talles' => function ($query) {
            $query->where('cantidad_disponible', '>', 0);
        }])->get();

        // Obtener información detallada de productos actuales
        $productosActuales = $venta->detalles->map(function ($detalle) {
            if ($detalle->tipo_producto === 'compra') {
                $producto = Compra::find($detalle->producto_id);
                $talle    = TalleCompra::find($detalle->talle_id);
            } else {
                $producto = ProductoVenta::find($detalle->producto_id);
                $talle    = TalleProductoVenta::find($detalle->talle_id);
            }

            return [
                'detalle'  => $detalle,
                'producto' => $producto,
                'talle'    => $talle,
            ];
        });

        return view('ventas.edit', compact('venta', 'clientes', 'compras', 'productosVenta', 'productosActuales'));
    }

    public function update(Request $request, $id)
    {
        $venta = Venta::with('detalles')->findOrFail($id);

        $request->validate([
            'cliente_id'                  => 'required|exists:clientes,id',
            'fecha_venta'                 => 'required|date',
            'precio_total'                => 'required|numeric|min:0',
            'productos'                   => 'required|array|min:1',
            'productos.*.tipo_producto'   => 'required|in:compra,manual',
            'productos.*.producto_id'     => 'required|integer',
            'productos.*.talle_id'        => 'required|integer',
            'productos.*.cantidad'        => 'required|integer|min:1',
            'productos.*.precio_unitario' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            // Restaurar stock de productos originales
            foreach ($venta->detalles as $detalle) {
                if ($detalle->tipo_producto === 'compra') {
                    $talle = TalleCompra::findOrFail($detalle->talle_id);
                } else {
                    $talle = TalleProductoVenta::findOrFail($detalle->talle_id);
                }
                $talle->cantidad_disponible += $detalle->cantidad;
                $talle->save();
            }

            // Eliminar detalles anteriores
            $venta->detalles()->delete();

            // Actualizar venta principal
            $venta->update([
                'cliente_id'   => $request->cliente_id,
                'fecha_venta'  => $request->fecha_venta,
                'precio_total' => $request->precio_total,
            ]);

            // Crear nuevos detalles
            foreach ($request->productos as $producto) {
                // Verificar stock
                if ($producto['tipo_producto'] === 'compra') {
                    $talle = TalleCompra::findOrFail($producto['talle_id']);
                } else {
                    $talle = TalleProductoVenta::findOrFail($producto['talle_id']);
                }

                if ($talle->cantidad_disponible < $producto['cantidad']) {
                    throw new \Exception('Stock insuficiente para el producto seleccionado');
                }

                // Crear nuevo detalle
                DetalleVenta::create([
                    'venta_id'        => $venta->id,
                    'tipo_producto'   => $producto['tipo_producto'],
                    'producto_id'     => $producto['producto_id'],
                    'talle_id'        => $producto['talle_id'],
                    'cantidad'        => $producto['cantidad'],
                    'precio_unitario' => $producto['precio_unitario'],
                    'subtotal'        => $producto['precio_unitario'] * $producto['cantidad'],
                ]);

                // Reducir stock
                $talle->cantidad_disponible -= $producto['cantidad'];
                $talle->save();
            }

            DB::commit();
            return redirect()->route('ventas.index')->with('success', 'Venta actualizada exitosamente');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Error al actualizar la venta: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy($id)
    {
        $venta = Venta::with('detalles')->findOrFail($id);

        DB::beginTransaction();
        try {
            // Restaurar stock de todos los productos
            foreach ($venta->detalles as $detalle) {
                if ($detalle->tipo_producto === 'compra') {
                    $talle = TalleCompra::findOrFail($detalle->talle_id);
                } else {
                    $talle = TalleProductoVenta::findOrFail($detalle->talle_id);
                }

                $talle->cantidad_disponible += $detalle->cantidad;
                $talle->save();
            }

            $venta->delete(); // Esto también eliminará los detalles por cascade

            DB::commit();
            return redirect()->route('ventas.index')->with('success', 'Venta eliminada exitosamente');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Error al eliminar la venta: ' . $e->getMessage());
        }
    }
}
