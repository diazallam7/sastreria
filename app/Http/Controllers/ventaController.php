<?php

namespace App\Http\Controllers;

use App\Models\Venta;
use App\Models\Cliente;
use App\Models\Vestido;
use Illuminate\Http\Request;

class VentaController extends Controller
{
    public function index()
    {
        $ventas = Venta::with('cliente', 'vestido')->get();
        return view('ventas.index', compact('ventas'));
    }

    public function create()
    {
        $clientes = Cliente::all();
        $vestidos = Vestido::where('estado', 1)->get();
        return view('ventas.create', compact('clientes', 'vestidos'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'vestido_id' => 'required|exists:vestidos,id',
            'fecha_venta' => 'required|date',
            'precio_total' => 'required|numeric|min:0',
        ]);

        $vestido = Vestido::findOrFail($validated['vestido_id']);
    $vestido->update(['estado' => 4]);

        Venta::create($validated);
        return redirect()->route('ventas.index');
    }

    public function destroy(Venta $venta)
    {
        $venta->delete();
        return redirect()->route('ventas.index');
    }
}
