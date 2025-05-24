<?php

namespace App\Http\Controllers;

use App\Models\Alquiler;
use App\Models\Venta;
use Illuminate\Support\Facades\App;

class FacturaController extends Controller
{


    public function alquiler(Alquiler $alquiler)
    {
        $pdf = App::make('dompdf.wrapper');
        $pdf->loadView('facturas.alquiler', compact('alquiler'));
        return $pdf->stream("Factura_Alquiler_{$alquiler->id}.pdf");
    }

    public function venta(Venta $venta)
    {
        $pdf = App::make('dompdf.wrapper');
        $pdf->loadView('facturas.venta', compact('venta'));
        return $pdf->stream("Factura_Venta_{$venta->id}.pdf");
    }
}
