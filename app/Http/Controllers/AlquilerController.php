<?php
namespace App\Http\Controllers;

use App\Models\Alquiler;
use App\Models\Cliente;
use App\Models\StockAlquiler;
use App\Models\TalleStock;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;

class AlquilerController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware(\Spatie\Permission\Middleware\PermissionMiddleware::using('ver-alquiler|crear-alquiler|editar-alquiler|eliminar-alquiler'), only: ['index']),
            new Middleware(\Spatie\Permission\Middleware\PermissionMiddleware::using('crear-alquiler'), only: ['create', 'store']),
            new Middleware(\Spatie\Permission\Middleware\PermissionMiddleware::using('editar-alquiler'), only: ['edit', 'update', 'show']),
            new Middleware(\Spatie\Permission\Middleware\PermissionMiddleware::using('eliminar-alquiler'), only: ['destroy']),
        ];
    }

    public function index()
{
    $alquileres = Alquiler::with('cliente', 'stockItems')
                          ->orderBy('created_at', 'desc')
                          ->get();

    // Calcular días de alquiler para cada alquiler
    foreach ($alquileres as $alquiler) {
        $fechaInicio    = Carbon::parse($alquiler->fecha_inicio);
        $fechaFin       = Carbon::parse($alquiler->fecha_fin);
        $alquiler->dias = $fechaInicio->diffInDays($fechaFin) + 1; // +1 para incluir el día de inicio
    }

    return view('alquileres.index', compact('alquileres'));
}
    public function create()
    {
        $clientes   = Cliente::all();
        $stockItems = StockAlquiler::with('talles')
            ->whereHas('talles', function ($query) {
                $query->where('cantidad_disponible', '>', 0);
            })
            ->get();

        return view('alquileres.create', compact('clientes', 'stockItems'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'cliente_id'         => 'required|exists:clientes,id',
            'prendas'            => 'required|array',
            'prendas.*.stock_id' => 'required|exists:stock_alquiler,id',
            'prendas.*.talle_id' => 'required|exists:talle_stock,id',
            'prendas.*.cantidad' => 'required|integer|min:1',
            'fecha_inicio'       => 'required|date',
            'fecha_fin'          => 'required|date|after:fecha_inicio',
            'costo_total'        => 'required|numeric|min:0',
            'garantia'           => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {
            // Crear el alquiler
            $alquiler               = new Alquiler();
            $alquiler->cliente_id   = $validated['cliente_id'];
            $alquiler->fecha_inicio = $validated['fecha_inicio'];
            $alquiler->fecha_fin    = $validated['fecha_fin'];
            $alquiler->costo_total  = $validated['costo_total'];
            $alquiler->garantia     = $validated['garantia'];
            $alquiler->estado       = 1; // activo
            $alquiler->save();

            // Asociar las prendas al alquiler y actualizar el stock
            foreach ($validated['prendas'] as $prendaData) {
                $stockId  = $prendaData['stock_id'];
                $talleId  = $prendaData['talle_id'];
                $cantidad = $prendaData['cantidad'];

                // Asociar la prenda al alquiler con el talle y cantidad
                $alquiler->stockItems()->attach($stockId, [
                    'talle_id' => $talleId,
                    'cantidad' => $cantidad,
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

    public function show(Alquiler $alquiler)
    {
        $alquiler->load('cliente', 'stockItems.talles');

        // Calcular días de alquiler
        $fechaInicio    = Carbon::parse($alquiler->fecha_inicio);
        $fechaFin       = Carbon::parse($alquiler->fecha_fin);
        $alquiler->dias = $fechaInicio->diffInDays($fechaFin) + 1;

        return view('alquileres.show', compact('alquiler'));
    }

    public function edit(Alquiler $alquiler)
    {
        // Solo permitir editar alquileres activos
        if ($alquiler->estado != 'activo') {
            return redirect()->route('alquileres.index')->with('error', 'Solo se pueden editar alquileres activos');
        }

        $clientes = Cliente::all();

        // Obtener todas las prendas disponibles más las que ya están en este alquiler
        $prendas = StockAlquiler::with('talles')->get();

        // Cargar las prendas actuales del alquiler
        $alquiler->load('cliente', 'stockItems');

        return view('alquileres.edit', compact('alquiler', 'clientes', 'prendas'));
    }

    public function update(Request $request, Alquiler $alquiler)
    {
        // Solo permitir editar alquileres activos
        if ($alquiler->estado != 'activo') {
            return redirect()->route('alquileres.index')->with('error', 'Solo se pueden editar alquileres activos');
        }

        $validated = $request->validate([
            'cliente_id'         => 'required|exists:clientes,id',
            'prendas'            => 'required|array',
            'prendas.*.stock_id' => 'required|exists:stock_alquiler,id',
            'prendas.*.talle_id' => 'required|exists:talle_stock,id',
            'prendas.*.cantidad' => 'required|integer|min:1',
            'fecha_inicio'       => 'required|date',
            'fecha_fin'          => 'required|date|after:fecha_inicio',
            'costo_total'        => 'required|numeric|min:0',
            'garantia'           => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {
            // Primero, restaurar el stock de las prendas actuales
            $stockItemsActuales = $alquiler->stockItems()->withPivot('talle_id', 'cantidad')->get();

            foreach ($stockItemsActuales as $item) {
                $talle = TalleStock::findOrFail($item->pivot->talle_id);
                $talle->cantidad_disponible += $item->pivot->cantidad;
                $talle->cantidad_alquilada -= $item->pivot->cantidad;
                $talle->save();
            }

            // Eliminar las relaciones actuales
            $alquiler->stockItems()->detach();

            // Actualizar los datos del alquiler
            $alquiler->update([
                'cliente_id'   => $validated['cliente_id'],
                'fecha_inicio' => $validated['fecha_inicio'],
                'fecha_fin'    => $validated['fecha_fin'],
                'costo_total'  => $validated['costo_total'],
                'garantia'     => $validated['garantia'],
            ]);

            // Asociar las nuevas prendas al alquiler y actualizar el stock
            foreach ($validated['prendas'] as $prendaData) {
                $stockId  = $prendaData['stock_id'];
                $talleId  = $prendaData['talle_id'];
                $cantidad = $prendaData['cantidad'];

                // Asociar la prenda al alquiler con el talle y cantidad
                $alquiler->stockItems()->attach($stockId, [
                    'talle_id' => $talleId,
                    'cantidad' => $cantidad,
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

            return redirect()->route('alquileres.index')->with('success', 'Alquiler actualizado correctamente');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error al actualizar el alquiler: ' . $e->getMessage()]);
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
