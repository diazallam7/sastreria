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
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ReservaController extends Controller
{
    public function index()
    {
        $reservas = Reserva::with(['cliente', 'stockItems'])
                          ->orderBy('id', 'desc')
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
            'fecha_reserva' => 'required|date',
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
        // Log para debug
        Log::info("=== INICIO CONVERSIÓN A ALQUILER ===");
        Log::info("Reserva ID: {$id}");
        Log::info("Request data: ", $request->all());
        Log::info("Request method: " . $request->method());

        // CAMBIO IMPORTANTE: Validación corregida
        $validated = $request->validate([
            'fecha_entrega' => 'required|date',
            'fecha_devolucion' => 'required|date|after_or_equal:fecha_entrega',
            'observaciones_entrega' => 'nullable|string|max:1000'
        ]);

        Log::info("Validación pasada exitosamente");
        Log::info("Fecha de devolución validada: " . $validated['fecha_devolucion']);

        DB::beginTransaction();
        
        try {
            $reserva = Reserva::with(['stockItems'])->findOrFail($id);
            
            Log::info("Reserva encontrada: {$reserva->id}, Estado: {$reserva->estado}");
            
            if ($reserva->estado !== 'confirmada') {
                throw new \Exception('Solo se pueden convertir reservas confirmadas');
            }

            // Verificar que la reserva tenga prendas
            if ($reserva->stockItems->isEmpty()) {
                throw new \Exception('La reserva no tiene prendas asociadas');
            }

            Log::info("Creando alquiler...");

            // Crear el alquiler
            $alquiler = Alquiler::create([
                'cliente_id' => $reserva->cliente_id,
                'fecha_inicio' => $validated['fecha_entrega'], // Usar la fecha de entrega real
                'fecha_fin' => $validated['fecha_devolucion'], // Usar el dato validado
                'costo_total' => $reserva->monto_total,
                'garantia' => $reserva->garantia_total,
                'estado' => 1 // activo
            ]);

            Log::info("Alquiler creado con ID: {$alquiler->id}");

            // Transferir las prendas de reserva a alquiler
            foreach ($reserva->stockItems as $item) {
                $talleId = $item->pivot->talle_id;
                $cantidad = $item->pivot->cantidad;
                
                Log::info("Procesando prenda: Stock ID {$item->id}, Talle ID {$talleId}, Cantidad {$cantidad}");
                
                // Verificar que el talle existe
                $talle = TalleStock::find($talleId);
                if (!$talle) {
                    throw new \Exception("No se encontró el talle con ID {$talleId}");
                }
                
                // Verificar que hay suficiente stock reservado
                if ($talle->cantidad_reservada < $cantidad) {
                    throw new \Exception("No hay suficiente stock reservado para el talle {$talle->talle}");
                }
                
                // Asociar al alquiler
                $alquiler->stockItems()->attach($item->id, [
                    'talle_id' => $talleId,
                    'cantidad' => $cantidad
                ]);
                
                Log::info("Prenda asociada al alquiler");
                
                // Actualizar stock: de reservado a alquilado
                $talle->cantidad_reservada -= $cantidad;
                $talle->cantidad_alquilada += $cantidad;
                $talle->save();
                
                Log::info("Stock actualizado - Reservado: {$talle->cantidad_reservada}, Alquilado: {$talle->cantidad_alquilada}");
            }

            // Actualizar la reserva
            $observacionesCompletas = $reserva->observaciones;
            if ($validated['observaciones_entrega']) {
                $observacionesCompletas .= "\n\nEntrega: " . $validated['observaciones_entrega'];
            }

            $reserva->update([
                'estado' => 'entregada',
                'alquiler_id' => $alquiler->id,
                'observaciones' => $observacionesCompletas
            ]);

            Log::info("Reserva actualizada a estado 'entregada'");

            DB::commit();
            
            Log::info("=== CONVERSIÓN EXITOSA ===");
            
            return redirect()->route('reservas.index')
                ->with('success', 'Reserva convertida a alquiler exitosamente. Alquiler #' . $alquiler->id . ' creado.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error en conversión: " . $e->getMessage());
            Log::error("Stack trace: " . $e->getTraceAsString());
            
            return back()->with('error', 'Error al convertir la reserva: ' . $e->getMessage());
        }
    }

    public function cancelar(Request $request, $id)
    {
        Log::info("=== INICIO CANCELACIÓN DE RESERVA ===");
        Log::info("Reserva ID: {$id}");
        Log::info("Request data: ", $request->all());

        $validated = $request->validate([
            'seña_devuelta' => 'required|numeric|min:0',
            'motivo_devolucion' => 'required|string|max:255',
            'observaciones_cancelacion' => 'nullable|string|max:1000',
        ]);

        Log::info("Validación de cancelación pasada");

        DB::beginTransaction();
        
        try {
            $reserva = Reserva::with(['stockItems'])->findOrFail($id);
            
            if ($reserva->estado === 'entregada') {
                throw new \Exception('No se puede cancelar una reserva que ya fue entregada');
            }

            $totalRecibido = $reserva->seña_garantia + $reserva->seña_alquiler;
            
            // Validar que no se devuelva más de lo recibido
            if ($validated['seña_devuelta'] > $totalRecibido) {
                throw new \Exception('No se puede devolver más dinero del que se recibió');
            }

            Log::info("Liberando stock reservado...");

            // Liberar el stock reservado
            foreach ($reserva->stockItems as $item) {
                $talleStock = TalleStock::find($item->pivot->talle_id);
                if ($talleStock) {
                    $talleStock->cantidad_disponible += $item->pivot->cantidad;
                    $talleStock->cantidad_reservada -= $item->pivot->cantidad;
                    $talleStock->save();
                    
                    Log::info("Stock liberado para talle {$talleStock->talle}: +{$item->pivot->cantidad} disponible, -{$item->pivot->cantidad} reservado");
                }
            }

            // Actualizar la reserva con los datos de cancelación
            $observacionesCompletas = $reserva->observaciones;
            if ($validated['observaciones_cancelacion']) {
                $observacionesCompletas .= "\n\nCancelación: " . $validated['observaciones_cancelacion'];
            }

            $reserva->update([
                'estado' => 'cancelada',
                'seña_devuelta' => $validated['seña_devuelta'],
                'motivo_devolucion' => $validated['motivo_devolucion'],
                'observaciones' => $observacionesCompletas
            ]);

            Log::info("Reserva actualizada a estado 'cancelada'");

            DB::commit();
            
            $ingresoNeto = $totalRecibido - $validated['seña_devuelta'];
            
            Log::info("=== CANCELACIÓN EXITOSA ===");
            
            return redirect()->route('reservas.index')
                ->with('success', 
                    'Reserva cancelada correctamente. ' .
                    'Monto devuelto: ₲ ' . number_format($validated['seña_devuelta'], 0, ',', '.') . 
                    ' | Ingreso neto: ₲ ' . number_format($ingresoNeto, 0, ',', '.')
                );
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error al cancelar reserva: " . $e->getMessage());
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
