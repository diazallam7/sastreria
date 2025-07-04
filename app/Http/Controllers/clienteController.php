<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Http\Request;

class ClienteController extends Controller
{

    public static function middleware(): array {
        return [
            new Middleware(\Spatie\Permission\Middleware\PermissionMiddleware::using('ver-cliente|crear-cliente|editar-cliente|eliminar-cliente'),only:['index']),
            new Middleware(\Spatie\Permission\Middleware\PermissionMiddleware::using('crear-cliente'), only:['create','store']),
            new Middleware(\Spatie\Permission\Middleware\PermissionMiddleware::using('editar-cliente'),only:['edit','update']),
            new Middleware(\Spatie\Permission\Middleware\PermissionMiddleware::using('eliminar-cliente'), only:['destroy']),
        ]; 
    }

    public function index()
    {
        $clientes = Cliente::all();
        return view('clientes.index', compact('clientes'));
    }

    public function create()
    {
        return view('clientes.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'telefono' => 'nullable|string|max:20',
            'correo' => 'nullable|string|max:255',
            'direccion' => 'nullable|string|max:255',
            // Medidas básicas
            'medida_saco_basica' => 'nullable|string|max:20',
            'medida_pantalon_basica' => 'nullable|string|max:20',
            // Medidas saco
            'saco_talle' => 'nullable|numeric|min:0|max:999.99',
            'saco_largo' => 'nullable|numeric|min:0|max:999.99',
            'saco_espalda' => 'nullable|numeric|min:0|max:999.99',
            'saco_manga' => 'nullable|numeric|min:0|max:999.99',
            'saco_pecho' => 'nullable|numeric|min:0|max:999.99',
            'saco_cintura' => 'nullable|numeric|min:0|max:999.99',
            'saco_cadera' => 'nullable|numeric|min:0|max:999.99',
            'saco_alto_hombro' => 'nullable|numeric|min:0|max:999.99',
            'saco_plomo_trasero' => 'nullable|numeric|min:0|max:999.99',
            'saco_plomo_delantero' => 'nullable|numeric|min:0|max:999.99',
            'saco_sisa' => 'nullable|numeric|min:0|max:999.99',
            'saco_puno' => 'nullable|numeric|min:0|max:999.99',
            // Medidas pantalón
            'pantalon_largo' => 'nullable|numeric|min:0|max:999.99',
            'pantalon_cintura' => 'nullable|numeric|min:0|max:999.99',
            'pantalon_cadera' => 'nullable|numeric|min:0|max:999.99',
            'pantalon_entre_pierna' => 'nullable|numeric|min:0|max:999.99',
            'pantalon_muslo' => 'nullable|numeric|min:0|max:999.99',
            'pantalon_rodilla' => 'nullable|numeric|min:0|max:999.99',
            'pantalon_bajo' => 'nullable|numeric|min:0|max:999.99',
            // Medidas chaleco
            'chaleco_talle' => 'nullable|numeric|min:0|max:999.99',
            'chaleco_pecho' => 'nullable|numeric|min:0|max:999.99',
            'chaleco_cintura' => 'nullable|numeric|min:0|max:999.99',
            'chaleco_escote' => 'nullable|numeric|min:0|max:999.99',
            'chaleco_largo' => 'nullable|numeric|min:0|max:999.99',
            'chaleco_largo_trasero' => 'nullable|numeric|min:0|max:999.99',
            'chaleco_cuello' => 'nullable|numeric|min:0|max:999.99',
            // Observaciones
            'observaciones_medidas' => 'nullable|string|max:1000',
        ]);

        $cliente = Cliente::create($validated);

        // Si es una petición AJAX (desde el modal), devolver JSON
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'cliente' => $cliente,
                'message' => 'Cliente creado exitosamente'
            ]);
        }

        // Si es una petición normal, redirigir
        return redirect()->route('clientes.index')
            ->with('success', 'Cliente creado exitosamente.');
    }

    public function historial($clienteId)
    {
        $cliente = Cliente::with([
            'alquileres.stockItems', 
            'alquileres.stockItems.talles',
            'reservas.stockItems',
            'reservas.stockItems.talles', 
            'ventas.detalles'
        ])->findOrFail($clienteId);
        
        return view('clientes.historial', compact('cliente'));
    }

    public function show(Cliente $cliente)
    {
        return view('clientes.show', compact('cliente'));
    }

    public function edit(Cliente $cliente)
    {
        return view('clientes.edit', compact('cliente'));
    }

    public function update(Request $request, Cliente $cliente)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'telefono' => 'nullable|string|max:20',
            'correo' => 'nullable|string|max:255',
            'direccion' => 'nullable|string|max:255',
            // Medidas básicas
            'medida_saco_basica' => 'nullable|string|max:20',
            'medida_pantalon_basica' => 'nullable|string|max:20',
            // Medidas saco
            'saco_talle' => 'nullable|numeric|min:0|max:999.99',
            'saco_largo' => 'nullable|numeric|min:0|max:999.99',
            'saco_espalda' => 'nullable|numeric|min:0|max:999.99',
            'saco_manga' => 'nullable|numeric|min:0|max:999.99',
            'saco_pecho' => 'nullable|numeric|min:0|max:999.99',
            'saco_cintura' => 'nullable|numeric|min:0|max:999.99',
            'saco_cadera' => 'nullable|numeric|min:0|max:999.99',
            'saco_alto_hombro' => 'nullable|numeric|min:0|max:999.99',
            'saco_plomo_trasero' => 'nullable|numeric|min:0|max:999.99',
            'saco_plomo_delantero' => 'nullable|numeric|min:0|max:999.99',
            'saco_sisa' => 'nullable|numeric|min:0|max:999.99',
            'saco_puno' => 'nullable|numeric|min:0|max:999.99',
            // Medidas pantalón
            'pantalon_largo' => 'nullable|numeric|min:0|max:999.99',
            'pantalon_cintura' => 'nullable|numeric|min:0|max:999.99',
            'pantalon_cadera' => 'nullable|numeric|min:0|max:999.99',
            'pantalon_entre_pierna' => 'nullable|numeric|min:0|max:999.99',
            'pantalon_muslo' => 'nullable|numeric|min:0|max:999.99',
            'pantalon_rodilla' => 'nullable|numeric|min:0|max:999.99',
            'pantalon_bajo' => 'nullable|numeric|min:0|max:999.99',
            // Medidas chaleco
            'chaleco_talle' => 'nullable|numeric|min:0|max:999.99',
            'chaleco_pecho' => 'nullable|numeric|min:0|max:999.99',
            'chaleco_cintura' => 'nullable|numeric|min:0|max:999.99',
            'chaleco_escote' => 'nullable|numeric|min:0|max:999.99',
            'chaleco_largo' => 'nullable|numeric|min:0|max:999.99',
            'chaleco_largo_trasero' => 'nullable|numeric|min:0|max:999.99',
            'chaleco_cuello' => 'nullable|numeric|min:0|max:999.99',
            // Observaciones
            'observaciones_medidas' => 'nullable|string|max:1000',
        ]);

        $cliente->update($validated);

        return redirect()->route('clientes.index')
            ->with('success', 'Cliente actualizado exitosamente.');
    }

    public function updateEstado($id)
    {
        $cliente = Cliente::findOrFail($id);
        $cliente->estado = $cliente->estado == 1 ? 0 : 1;
        $cliente->save();
    
        return redirect()->route('clientes.index')->with('success', 'Estado actualizado correctamente.');
    }

    public function destroy(Cliente $cliente)
    {
        $cliente->delete();
        return redirect()->route('clientes.index');
    }
}
