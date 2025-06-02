<?php
// Archivo: app/Http/Controllers/GastoVarioController.php

namespace App\Http\Controllers;

use App\Models\GastoVario;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;

use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;

class GastoVarioController extends Controller
{

    public function index()
    {
        $gastos = GastoVario::orderBy('fecha', 'desc')->paginate(10);
        
        // Calcular totales
        $totalGeneral = GastoVario::sum('monto');
        $totalMesActual = GastoVario::whereMonth('fecha', now()->month)
                                   ->whereYear('fecha', now()->year)
                                   ->sum('monto');
        
        return view('gastos-varios.index', compact('gastos', 'totalGeneral', 'totalMesActual'));
    }

    public function create()
    {
        return view('gastos-varios.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre_gasto' => 'required|string|max:255',
            'fecha' => 'required|date',
            'monto' => 'required|numeric|min:0',
            'observacion' => 'nullable|string|max:1000'
        ]);

        try {
            GastoVario::create($request->all());
            return redirect()->route('gastos-varios.index')->with('success', 'Gasto registrado exitosamente');
        } catch (\Exception $e) {
            return back()->with('error', 'Error al registrar el gasto: ' . $e->getMessage())->withInput();
        }
    }

    public function edit($id)
    {
        $gasto = GastoVario::findOrFail($id);
        return view('gastos-varios.edit', compact('gasto'));
    }

    public function update(Request $request, $id)
    {
        $gasto = GastoVario::findOrFail($id);
        
        $request->validate([
            'nombre_gasto' => 'required|string|max:255',
            'fecha' => 'required|date',
            'monto' => 'required|numeric|min:0',
            'observacion' => 'nullable|string|max:1000'
        ]);

        try {
            $gasto->update($request->all());
            return redirect()->route('gastos-varios.index')->with('success', 'Gasto actualizado exitosamente');
        } catch (\Exception $e) {
            return back()->with('error', 'Error al actualizar el gasto: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy($id)
    {
        try {
            $gasto = GastoVario::findOrFail($id);
            $gasto->delete();
            return redirect()->route('gastos-varios.index')->with('success', 'Gasto eliminado exitosamente');
        } catch (\Exception $e) {
            return back()->with('error', 'Error al eliminar el gasto: ' . $e->getMessage());
        }
    }
}