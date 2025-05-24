<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Http\Request;

class ClienteController extends Controller
{
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
            'correo' => 'nullable|unique:clientes',
            'direccion' => 'nullable|string|max:255',
        ]);

        Cliente::create($validated);
        return redirect()->route('clientes.index');
    }


    
    public function historial($clienteId)
    {
        $cliente = Cliente::with([
            'alquileres.prendas', 
            'ventas'
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
