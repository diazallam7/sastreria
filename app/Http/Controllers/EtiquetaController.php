<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\StockAlquiler;
use App\Services\BarcodeService;
use Illuminate\Support\Facades\App;

class EtiquetaController extends Controller
{
    public function producto(Producto $producto, BarcodeService $barcodes)
    {
        $producto->load('talles');

        $etiquetas = $producto->talles->map(fn ($talle) => [
            'talle' => $talle->talle,
            'codigo' => $talle->codigo_barra,
            'png' => $talle->codigo_barra ? $barcodes->pngBase64($talle->codigo_barra) : null,
        ]);

        $pdf = App::make('dompdf.wrapper');
        $pdf->loadView('etiquetas.producto', compact('producto', 'etiquetas'));

        return $pdf->stream("Etiquetas_Producto_{$producto->id}.pdf");
    }

    public function stockAlquiler(StockAlquiler $item, BarcodeService $barcodes)
    {
        $item->load('talles.unidades');

        $etiquetas = $item->talles->flatMap(
            fn ($talle) => $talle->unidades->map(fn ($unidad) => [
                'talle' => $talle->talle,
                'codigo' => $unidad->codigo,
                'png' => $unidad->codigo ? $barcodes->pngBase64($unidad->codigo) : null,
            ])
        );

        $pdf = App::make('dompdf.wrapper');
        $pdf->loadView('etiquetas.stock-alquiler', ['item' => $item, 'etiquetas' => $etiquetas]);

        return $pdf->stream("Etiquetas_Stock_{$item->id}.pdf");
    }
}
