<?php

namespace App\Http\Controllers;

use App\Models\Alquiler;
use App\Models\TalleStock;
use App\Models\Venta;
use Illuminate\Support\Facades\App;

class FacturaController extends Controller
{
    public function alquiler(Alquiler $alquiler)
    {
        $alquiler->load('cliente', 'stockItems');
        $tallesNombres = TalleStock::whereIn('id', $alquiler->stockItems->pluck('pivot.talle_id'))->pluck('talle', 'id');

        $pdf = App::make('dompdf.wrapper');
        $pdf->loadView('facturas.alquiler', compact('alquiler', 'tallesNombres'));

        return $pdf->stream("Factura_Alquiler_{$alquiler->id}.pdf");
    }

    public function venta(Venta $venta)
    {
        $venta->load('cliente', 'detalles');

        $pdf = App::make('dompdf.wrapper');
        $pdf->loadView('facturas.venta', compact('venta'));

        return $pdf->stream("Factura_Venta_{$venta->id}.pdf");
    }
}
