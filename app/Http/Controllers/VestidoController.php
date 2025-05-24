<?php

namespace App\Http\Controllers;

use App\Models\Vestido;
use Illuminate\Http\Request;

class VestidoController extends Controller
{
    public function index()
    {
        $vestidos = Vestido::all();
        return view('vestidos.index', compact('vestidos'));
    }

    public function create()
    {
        return view('vestidos.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'talla' => 'required|string|max:50',
            'color' => 'required|string|max:50',
            'categoria' => 'required|string|max:100',
            'precio_alquiler' => 'nullable|numeric|min:0',
            'precio_venta' => 'nullable|numeric|min:0',
        ]);

        Vestido::create($validated);
        return redirect()->route('vestidos.index');
    }

    public function edit(Vestido $vestido)
    {
        return view('vestidos.edit', compact('vestido'));
    }

    public function update(Request $request, Vestido $vestido)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'talla' => 'required|string|max:50',
            'color' => 'required|string|max:50',
            'categoria' => 'required|string|max:100',
            'precio_alquiler' => 'nullable|numeric|min:0',
            'precio_venta' => 'nullable|numeric|min:0',
        ]);

        $vestido->update($validated);
        return redirect()->route('vestidos.index');
    }

    public function destroy(Vestido $vestido)
    {
        $vestido->delete();
        return redirect()->route('vestidos.index');
    }

}
