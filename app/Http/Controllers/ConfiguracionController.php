<?php

namespace App\Http\Controllers;

use App\Models\Configuracion;
use Illuminate\Http\Request;

class ConfiguracionController extends Controller
{
    public function index()
    {
        $configuraciones = Configuracion::all();
        return view('configuraciones.index', compact('configuraciones'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'configuraciones.*.id' => 'required|exists:configuraciones,id',
            'configuraciones.*.valor' => 'required|numeric|min:0',
        ]);

        foreach ($validated['configuraciones'] as $config) {
            Configuracion::find($config['id'])->update(['valor' => $config['valor']]);
        }

        return redirect()->route('configuraciones.index')->with('success', 'Configuraciones actualizadas correctamente.');
    }
}
