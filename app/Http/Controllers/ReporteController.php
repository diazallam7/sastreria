<?php

namespace App\Http\Controllers;

use App\Models\Alquiler;
use App\Models\Venta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReporteController extends Controller
{
    public function index()
    {
        return view('reportes.index');
    }




public function ventas(Request $request)
{
    $intervalo = $request->get('intervalo', 'mensual');

    if ($intervalo === 'mensual') {
        $ventas = DB::table('ventas')
            ->selectRaw("DATE_FORMAT(fecha_venta, '%Y-%m') as periodo, COUNT(*) as total_ventas, SUM(precio_total) as ingresos")
            ->groupBy(DB::raw("DATE_FORMAT(fecha_venta, '%Y-%m')"))
            ->orderBy('periodo', 'desc')
            ->get();
    } else { // Anual
        $ventas = DB::table('ventas')
            ->selectRaw("YEAR(fecha_venta) as periodo, COUNT(*) as total_ventas, SUM(precio_total) as ingresos")
            ->groupBy(DB::raw("YEAR(fecha_venta)"))
            ->orderBy('periodo', 'desc')
            ->get();
    }

    return view('reportes.ventas', compact('ventas', 'intervalo'));
}

}
