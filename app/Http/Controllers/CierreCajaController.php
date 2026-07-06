<?php

namespace App\Http\Controllers;

use App\Services\ReporteCajaService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use PDF;
use Spatie\Permission\Middleware\PermissionMiddleware;

class CierreCajaController extends Controller implements HasMiddleware
{
    public function __construct(private readonly ReporteCajaService $service)
    {
    }

    public static function middleware(): array
    {
        return [
            new Middleware(PermissionMiddleware::using('exportar-cierre-caja')),
        ];
    }

    public function exportarPDF(Request $request)
    {
        $fecha = $request->get('fecha', now()->format('Y-m-d'));
        $movimientos = $this->service->movimientosDia($fecha);

        return PDF::loadView('cierre-caja.pdf', compact('movimientos', 'fecha'))
            ->download('cierre-caja-' . Carbon::parse($fecha)->format('d-m-Y') . '.pdf');
    }

    public function exportarPDFSemanal(Request $request)
    {
        $fecha = Carbon::parse($request->get('fecha', now()->format('Y-m-d')));
        $datos = $this->service->resumenSemanal($fecha);

        $nombre = 'cierre-caja-semanal-' . $datos['fechaInicio']->format('d-m-Y') . '-al-' . $datos['fechaFin']->format('d-m-Y') . '.pdf';

        return PDF::loadView('cierre-caja.pdf-semanal', $datos)->download($nombre);
    }

    public function exportarPDFMensual(Request $request)
    {
        $fecha = Carbon::parse($request->get('fecha', now()->format('Y-m-d')));
        $datos = $this->service->resumenMensual($fecha);

        $nombre = 'cierre-caja-mensual-' . $datos['nombreMes'] . '-' . $datos['año'] . '.pdf';

        return PDF::loadView('cierre-caja.pdf-mensual', $datos)->download($nombre);
    }
}
