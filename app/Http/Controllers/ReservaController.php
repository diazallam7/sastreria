<?php
// Archivo: app/Http/Controllers/ReservaController.php

namespace App\Http\Controllers;

use App\Models\Reserva;
use App\Models\Cliente;
use App\Models\StockAlquiler;
use App\Models\TalleStock;
use App\Models\Alquiler;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReservaController extends Controller
{
    public function index()
    {
        $reservas = Reserva::with(['cliente', 'stockItems'])
                          ->orderBy('fecha_entrega_programada', 'asc')
                          ->get();
        
        return view('reservas.index', compact('reservas'));
    }

    public function create()
    {
        $clientes = Cliente::all();
        $prendas = StockAlquiler::with('talles')
            ->whereHas('talles', function($query) {
                $query->where('cantidad_disponible', '>', 0);
            })
            ->get();
        
        return view('reservas.create', compact('clientes', 'prendas'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'prendas' => 'required|array',
            'prendas.*.stock_id' => 'required|exists:stock_alquiler,id',
            'prendas.*.talle_id' => 'required|exists:talle_stock,id',
            'prendas.*.cantidad' => 'required|integer|min:1',
            'fecha_reserva' => 'required|date|',
            'fecha_entrega_programada' => 'required|date|after:fecha_reserva',
            'fecha_devolucion_programada' => 'required|date|after:fecha_reserva',
            'monto_total' => 'required|numeric|min:0',
            'garantia_total' => 'required|numeric|min:0',
            'seña_garantia' => 'required|numeric|min:0|lte:garantia_total',
            'seña_alquiler' => 'nullable|numeric|min:0|lte:monto_total',
            'observaciones' => 'nullable|string|max:1000'
        ]);

        DB::beginTransaction();
        
        try {
            // Crear la reserva
            $reserva = Reserva::create([
                'cliente_id' => $validated['cliente_id'],
                'fecha_reserva' => Carbon::now(),
                'fecha_reserva' => $validated['fecha_reserva'],
                'fecha_entrega_programada' => $validated['fecha_entrega_programada'],
                'fecha_devolucion_programada' => $validated['fecha_devolucion_programada'],
                'monto_total' => $validated['monto_total'],
                'garantia_total' => $validated['garantia_total'],
                'seña_garantia' => $validated['seña_garantia'],
                'seña_alquiler' => $validated['seña_alquiler'] ?? 0,
                'estado' => 'confirmada',
                'observaciones' => $validated['observaciones']
            ]);

            // Asociar las prendas a la reserva
            foreach ($validated['prendas'] as $prendaData) {
                $stockId = $prendaData['stock_id'];
                $talleId = $prendaData['talle_id'];
                $cantidad = $prendaData['cantidad'];
                
                // Verificar disponibilidad
                $talle = TalleStock::findOrFail($talleId);
                if ($talle->cantidad_disponible < $cantidad) {
                    throw new \Exception("No hay suficiente stock disponible para el talle {$talle->talle}");
                }
                
                // Asociar la prenda a la reserva
                $reserva->stockItems()->attach($stockId, [
                    'talle_id' => $talleId,
                    'cantidad' => $cantidad
                ]);
                
                // Actualizar stock (reservar las prendas)
                $talle->cantidad_disponible -= $cantidad;
                $talle->cantidad_reservada += $cantidad;
                $talle->save();
            }
            
            DB::commit();
            
            return redirect()->route('reservas.index')->with('success', 'Reserva creada correctamente');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al crear la reserva: ' . $e->getMessage())->withInput();
        }
    }

    public function show($id)
    {
        $reserva = Reserva::with(['cliente', 'stockItems', 'alquiler'])->findOrFail($id);
        return view('reservas.show', compact('reserva'));
    }

    public function edit($id)
    {
        $reserva = Reserva::with(['stockItems'])->findOrFail($id);
        
        if ($reserva->estado === 'entregada') {
            return back()->with('error', 'No se puede editar una reserva que ya fue entregada');
        }
        
        $clientes = Cliente::all();
        $prendas = StockAlquiler::with('talles')->get();
        
        return view('reservas.edit', compact('reserva', 'clientes', 'prendas'));
    }

    public function convertirAAlquiler(Request $request, $id)
    {
        $request->validate([
            'fecha_devolucion' => 'required|date|after:today',
            'observaciones_entrega' => 'nullable|string|max:1000'
        ]);

        DB::beginTransaction();
        
        try {
            $reserva = Reserva::with(['stockItems'])->findOrFail($id);
            
            if ($reserva->estado !== 'confirmada') {
                throw new \Exception('Solo se pueden convertir reservas confirmadas');
            }

            // Crear el alquiler
            $alquiler = Alquiler::create([
                'cliente_id' => $reserva->cliente_id,
                'fecha_inicio' => Carbon::now(),
                'fecha_fin' => $request->fecha_devolucion,
                'costo_total' => $reserva->monto_total,
                'garantia' => $reserva->garantia_total,
                'estado' => 1 // activo
            ]);

            // Transferir las prendas de reserva a alquiler
            foreach ($reserva->stockItems as $item) {
                $talleId = $item->pivot->talle_id;
                $cantidad = $item->pivot->cantidad;
                
                // Asociar al alquiler
                $alquiler->stockItems()->attach($item->id, [
                    'talle_id' => $talleId,
                    'cantidad' => $cantidad
                ]);
                
                // Actualizar stock: de reservado a alquilado
                $talle = TalleStock::findOrFail($talleId);
                $talle->cantidad_reservada -= $cantidad;
                $talle->cantidad_alquilada += $cantidad;
                $talle->save();
            }

            // Actualizar la reserva
            $reserva->update([
                'estado' => 'entregada',
                'alquiler_id' => $alquiler->id,
                'observaciones' => $reserva->observaciones . "\n\nEntrega: " . ($request->observaciones_entrega ?? 'Sin observaciones')
            ]);

            DB::commit();
            
            return redirect()->route('alquileres.index')
                ->with('success', 'Reserva convertida a alquiler exitosamente. Monto cobrado: ₲ ' . number_format($reserva->total_a_cobrar, 0, ',', '.'));
                
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al convertir la reserva: ' . $e->getMessage());
        }
    }

public function cancelar(Request $request, $id)
{
    $request->validate([
        'seña_devuelta' => 'required|numeric|min:0',
        'motivo_devolucion' => 'required|string|max:255',
        'observaciones_cancelacion' => 'nullable|string|max:1000',
    ]);

    DB::beginTransaction();
    
    try {
        $reserva = Reserva::with(['stockItems'])->findOrFail($id);
        
        if ($reserva->estado === 'entregada') {
            throw new \Exception('No se puede cancelar una reserva que ya fue entregada');
        }

        $totalRecibido = $reserva->seña_garantia + $reserva->seña_alquiler;
        
        // Validar que no se devuelva más de lo recibido
        if ($request->seña_devuelta > $totalRecibido) {
            throw new \Exception('No se puede devolver más dinero del que se recibió');
        }

        // Liberar el stock reservado
        foreach ($reserva->stockItems as $item) {
            $talleStock = TalleStock::find($item->pivot->talle_id);
            if ($talleStock) {
                $talleStock->cantidad_disponible += $item->pivot->cantidad;
                $talleStock->cantidad_reservada -= $item->pivot->cantidad;
                $talleStock->save();
            }
        }

        // Actualizar la reserva con los datos de cancelación
        $observacionesCompletas = $reserva->observaciones;
        if ($request->observaciones_cancelacion) {
            $observacionesCompletas .= "\n\nCancelación: " . $request->observaciones_cancelacion;
        }

        $reserva->update([
            'estado' => 'cancelada',
            'seña_devuelta' => $request->seña_devuelta,
            'motivo_devolucion' => $request->motivo_devolucion,
            'observaciones' => $observacionesCompletas
        ]);

        DB::commit();
        
        $ingresoNeto = $totalRecibido - $request->seña_devuelta;
        
        return redirect()->route('reservas.index')
            ->with('success', 
                'Reserva cancelada correctamente. ' .
                'Monto devuelto: ₲ ' . number_format($request->seña_devuelta, 0, ',', '.') . 
                ' | Ingreso neto: ₲ ' . number_format($ingresoNeto, 0, ',', '.')
            );
        
    } catch (\Exception $e) {
        DB::rollBack();
        return back()->with('error', 'Error al cancelar la reserva: ' . $e->getMessage());
    }
}
    public function destroy($id)
    {
        DB::beginTransaction();
        
        try {
            $reserva = Reserva::with(['stockItems'])->findOrFail($id);
            
            if ($reserva->estado === 'entregada') {
                throw new \Exception('No se puede eliminar una reserva que ya fue entregada');
            }

            // Liberar el stock si está reservado
            if ($reserva->estado === 'confirmada') {
                foreach ($reserva->stockItems as $item) {
                    $talle = TalleStock::findOrFail($item->pivot->talle_id);
                    $talle->cantidad_disponible += $item->pivot->cantidad;
                    $talle->cantidad_reservada -= $item->pivot->cantidad;
                    $talle->save();
                }
            }

            $reserva->delete();

            DB::commit();
            
            return redirect()->route('reservas.index')->with('success', 'Reserva eliminada correctamente');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al eliminar la reserva: ' . $e->getMessage());
        }
    }
}