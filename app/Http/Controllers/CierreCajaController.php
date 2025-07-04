<?php
namespace App\Http\Controllers;

use App\Models\Alquiler;
use App\Models\Compra;
use App\Models\Devolucion;
use App\Models\GastoVario;
use App\Models\Reserva;
use App\Models\Venta;
use Carbon\Carbon;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use PDF;

class CierreCajaController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware(\Spatie\Permission\Middleware\PermissionMiddleware::using('ver-cierre-caja'), only: ['index', 'consultarFecha']),
            new Middleware(\Spatie\Permission\Middleware\PermissionMiddleware::using('exportar-cierre-caja'), only: ['exportarPDF', 'exportarPDFSemanal', 'exportarPDFMensual']),
            new Middleware(\Spatie\Permission\Middleware\PermissionMiddleware::using('ver-cierre-caja-semanal'), only: ['resumenSemanal']),
            new Middleware(\Spatie\Permission\Middleware\PermissionMiddleware::using('ver-cierre-caja-mensual'), only: ['resumenMensual']),
        ];
    }
   public function index(Request $request)
    {
        $fecha = $request->get('fecha', now()->format('Y-m-d'));
        $movimientos = $this->calcularMovimientosDia($fecha);

        return view('cierre-caja.index', compact('movimientos', 'fecha'));
    }

    
    private function calcularMovimientosDia($fecha)
    {
        $fechaCarbon = Carbon::parse($fecha);

        // INGRESOS
        $ingresos = [
            'alquileres' => 0,
            'multas_retraso' => 0,
            'ventas' => 0,
            'ingresos_cancelaciones' => 0,
            'total' => 0,
        ];

        // EGRESOS
        $egresos = [
            'compras' => 0,
            'gastos_varios' => 0,
            'total' => 0,
        ];

        // Detalles para mostrar en el PDF
        $detalles = [
            'reservas' => collect(),
            'cancelaciones' => collect(),
            'alquileres_iniciados' => collect(),
            'devoluciones' => collect(),
            'ventas' => collect(),
            'compras' => collect(),
            'gastos' => collect(),
        ];

        // 1. Alquileres iniciados hoy
        $alquileresHoy = Alquiler::with('cliente')
            ->whereDate('fecha_inicio', $fecha)
            ->where('estado', '!=', 0) // No cancelados
            ->get();

        foreach ($alquileresHoy as $alquiler) {
            $ingresos['alquileres'] += $alquiler->costo_total;
        }
        $detalles['alquileres_iniciados'] = $alquileresHoy;

        // 2. Multas por retraso (solo las multas como INGRESOS POSITIVOS)
        $devolucionesHoy = Devolucion::with(['alquiler.cliente'])
            ->whereDate('fecha_devolucion', $fecha)
            ->get();

        foreach ($devolucionesHoy as $devolucion) {
            // Sumar la multa aplicada como INGRESO POSITIVO
            $multaAplicada = $devolucion->multa_aplicada_real ?? $devolucion->multa_aplicada ?? 0;
            if ($multaAplicada > 0) {
                $ingresos['multas_retraso'] += $multaAplicada; // Suma positiva
            }
        }
        $detalles['devoluciones'] = $devolucionesHoy;

        // 3. Ventas del día
        $ventasHoy = Venta::with('cliente')
            ->whereDate('fecha_venta', $fecha)
            ->get();

        foreach ($ventasHoy as $venta) {
            $ingresos['ventas'] += $venta->precio_total;
        }
        $detalles['ventas'] = $ventasHoy;

        // 4. Ingresos por cancelaciones (lo que se queda la empresa)
        $cancelacionesHoy = Reserva::with('cliente')
            ->whereDate('updated_at', $fecha)
            ->where('estado', 'cancelada')
            ->get();

        foreach ($cancelacionesHoy as $cancelacion) {
            $totalRecibido = $cancelacion->seña_alquiler + $cancelacion->seña_garantia;
            $devuelto = $cancelacion->seña_devuelta ?? 0;
            $ingresoNeto = $totalRecibido - $devuelto;
            if ($ingresoNeto > 0) {
                $ingresos['ingresos_cancelaciones'] += $ingresoNeto;
            }
        }
        $detalles['cancelaciones'] = $cancelacionesHoy;

        // EGRESOS

        // 1. Compras del día - CORREGIDO: Cargar correctamente los talles y calcular el total
        $comprasHoy = Compra::with('talles')->whereDate('fecha_compra', $fecha)->get();

        Log::info("=== INICIO CÁLCULO COMPRAS ===");
        Log::info("Fecha: {$fecha}");
        Log::info("Compras encontradas: " . $comprasHoy->count());

        foreach ($comprasHoy as $compra) {
            Log::info("--- Procesando Compra ID: {$compra->id} ---");
            Log::info("Nombre: {$compra->nombre_producto}");
            Log::info("Precio compra: {$compra->precio_compra}");
            Log::info("Talles cargados: " . $compra->talles->count());
            
            // Calcular cantidad total usando la relación talles
            $cantidadTotal = 0;
            foreach ($compra->talles as $talle) {
                Log::info("Talle {$talle->talle}: cantidad_total = {$talle->cantidad_total}");
                $cantidadTotal += $talle->cantidad_total;
            }
            
            Log::info("Cantidad total calculada: {$cantidadTotal}");
            
            // Calcular el total de la compra (precio unitario * cantidad total)
            $totalCompra = $compra->precio_compra * $cantidadTotal;
            
            Log::info("Total compra: {$compra->precio_compra} x {$cantidadTotal} = {$totalCompra}");
            
            $egresos['compras'] += $totalCompra;
            
            // Agregar información calculada para mostrar en el detalle
            $compra->cantidad_total_calculada = $cantidadTotal;
            $compra->precio_total_calculado = $totalCompra;
            
            Log::info("Egreso acumulado hasta ahora: {$egresos['compras']}");
        }
        
        Log::info("=== TOTAL FINAL EGRESOS COMPRAS: {$egresos['compras']} ===");
        
        $detalles['compras'] = $comprasHoy;

        // 2. Gastos varios
        $gastosHoy = GastoVario::whereDate('fecha', $fecha)->get();

        foreach ($gastosHoy as $gasto) {
            $egresos['gastos_varios'] += $gasto->monto;
        }
        $detalles['gastos'] = $gastosHoy;

        // Calcular totales
        $ingresos['total'] = array_sum(array_filter($ingresos, 'is_numeric'));
        $egresos['total'] = array_sum(array_filter($egresos, 'is_numeric'));

        $saldoNeto = $ingresos['total'] - $egresos['total'];

        Log::info("=== RESUMEN FINAL ===");
        Log::info("Total ingresos: {$ingresos['total']}");
        Log::info("Total egresos: {$egresos['total']}");
        Log::info("Saldo neto: {$saldoNeto}");

        return [
            'fecha' => $fecha,
            'ingresos' => $ingresos,
            'egresos' => $egresos,
            'saldo_neto' => $saldoNeto,
            'detalles' => $detalles,
        ];
    }
    public function consultarFecha(Request $request)
    {
        $request->validate([
            'fecha' => 'required|date',
        ]);

        $fecha       = $request->fecha;
        $movimientos = $this->calcularMovimientosDia($fecha);

        return view('cierre-caja.index', compact('movimientos', 'fecha'));
    }
public function exportarPDF(Request $request)
    {
        $fecha = $request->get('fecha', now()->format('Y-m-d'));
        $movimientos = $this->calcularMovimientosDia($fecha);

        $pdf = PDF::loadView('cierre-caja.pdf', compact('movimientos'));

        $nombreArchivo = 'cierre-caja-' . Carbon::parse($fecha)->format('d-m-Y') . '.pdf';

        return $pdf->download($nombreArchivo);
    }


   
     public function resumenSemanal(Request $request)
    {
        // Si se proporciona una fecha, usamos esa semana, de lo contrario usamos la semana actual
        $fecha       = $request->get('fecha', now()->format('Y-m-d'));
        $fechaCarbon = Carbon::parse($fecha);

        $fechaInicio = $fechaCarbon->copy()->startOfWeek();
        $fechaFin    = $fechaCarbon->copy()->endOfWeek();

        $resumenSemanal = [];
        $totalesSemana  = [
            'ingresos'   => 0,
            'egresos'    => 0,
            'saldo_neto' => 0,
        ];

        $mejorDia = [
            'fecha' => null,
            'saldo' => -999999999,
        ];

        $peorDia = [
            'fecha' => null,
            'saldo' => 999999999,
        ];

        for ($fecha = $fechaInicio->copy(); $fecha <= $fechaFin; $fecha->addDay()) {
            $movimientos = $this->calcularMovimientosDia($fecha->format('Y-m-d'));

            $diaSemana = $this->traducirDiaSemana($fecha->format('l'));

            $resumenDia = [
                'fecha'             => $fecha->format('Y-m-d'),
                'dia'               => $diaSemana,
                'ingresos'          => $movimientos['ingresos']['total'],
                'egresos'           => $movimientos['egresos']['total'],
                'saldo_neto'        => $movimientos['saldo_neto'],
                'desglose_ingresos' => $movimientos['ingresos'],
                'desglose_egresos'  => $movimientos['egresos'],
            ];

            $resumenSemanal[] = $resumenDia;

            // Actualizar totales
            $totalesSemana['ingresos'] += $movimientos['ingresos']['total'];
            $totalesSemana['egresos'] += $movimientos['egresos']['total'];
            $totalesSemana['saldo_neto'] += $movimientos['saldo_neto'];

            // Verificar mejor y peor día
            if ($movimientos['saldo_neto'] > $mejorDia['saldo']) {
                $mejorDia['fecha'] = $fecha->format('Y-m-d');
                $mejorDia['dia']   = $diaSemana;
                $mejorDia['saldo'] = $movimientos['saldo_neto'];
            }

            if ($movimientos['saldo_neto'] < $peorDia['saldo']) {
                $peorDia['fecha'] = $fecha->format('Y-m-d');
                $peorDia['dia']   = $diaSemana;
                $peorDia['saldo'] = $movimientos['saldo_neto'];
            }
        }

        // Calcular promedios
        $diasConMovimientos = count(array_filter($resumenSemanal, function ($dia) {
            return $dia['ingresos'] > 0 || $dia['egresos'] > 0;
        }));

        $promedios = [
            'ingresos'   => $diasConMovimientos > 0 ? $totalesSemana['ingresos'] / $diasConMovimientos : 0,
            'egresos'    => $diasConMovimientos > 0 ? $totalesSemana['egresos'] / $diasConMovimientos : 0,
            'saldo_neto' => $diasConMovimientos > 0 ? $totalesSemana['saldo_neto'] / $diasConMovimientos : 0,
        ];

        return view('cierre-caja.semanal', compact(
            'resumenSemanal',
            'fechaInicio',
            'fechaFin',
            'totalesSemana',
            'mejorDia',
            'peorDia',
            'promedios'
        ));
    }


     public function resumenMensual(Request $request)
    {
        // Si se proporciona una fecha, usamos ese mes, de lo contrario usamos el mes actual
        $fecha       = $request->get('fecha', now()->format('Y-m-d'));
        $fechaCarbon = Carbon::parse($fecha);

        $fechaInicio = $fechaCarbon->copy()->startOfMonth();
        $fechaFin    = $fechaCarbon->copy()->endOfMonth();

        $movimientosMensuales = [];
        $totalesMes           = [
            'ingresos'   => 0,
            'egresos'    => 0,
            'saldo_neto' => 0,
        ];

        $mejorSemana = [
            'numero' => null,
            'saldo'  => -999999999,
        ];

        $peorSemana = [
            'numero' => null,
            'saldo'  => 999999999,
        ];

        // Agrupar por semanas del mes
        $numeroSemana = 1;
        $fechaActual = $fechaInicio->copy();

        while ($fechaActual <= $fechaFin) {
            // Encontrar el final de la semana actual, pero sin salir del mes
            $finSemanaActual = $fechaActual->copy()->endOfWeek();
            if ($finSemanaActual > $fechaFin) {
                $finSemanaActual = $fechaFin->copy();
            }

            $ingresosSemana  = 0;
            $egresosSemana   = 0;
            $saldoNetoSemana = 0;

            $desgloseSemana = [
                'ingresos' => [
                    'alquileres'             => 0,
                    'multas_retraso'         => 0,
                    'ventas'                 => 0,
                    'ingresos_cancelaciones' => 0,
                    'total'                  => 0,
                ],
                'egresos'  => [
                    'compras'                    => 0,
                    'gastos_varios'              => 0,
                    'total'                      => 0,
                ],
            ];

            // Procesar cada día de la semana actual
            for ($fecha = $fechaActual->copy(); $fecha <= $finSemanaActual; $fecha->addDay()) {
                $movimientos = $this->calcularMovimientosDia($fecha->format('Y-m-d'));

                $ingresosSemana += $movimientos['ingresos']['total'];
                $egresosSemana += $movimientos['egresos']['total'];
                $saldoNetoSemana += $movimientos['saldo_neto'];

                // Acumular desglose
                foreach ($movimientos['ingresos'] as $key => $valor) {
                    if (isset($desgloseSemana['ingresos'][$key])) {
                        $desgloseSemana['ingresos'][$key] += $valor;
                    }
                }

                foreach ($movimientos['egresos'] as $key => $valor) {
                    if (isset($desgloseSemana['egresos'][$key])) {
                        $desgloseSemana['egresos'][$key] += $valor;
                    }
                }
            }

            $movimientosMensuales[] = [
                'semana'       => $numeroSemana,
                'fecha_inicio' => $fechaActual->format('d/m'),
                'fecha_fin'    => $finSemanaActual->format('d/m'),
                'ingresos'     => $ingresosSemana,
                'egresos'      => $egresosSemana,
                'saldo_neto'   => $saldoNetoSemana,
                'desglose'     => $desgloseSemana,
            ];

            // Actualizar totales
            $totalesMes['ingresos'] += $ingresosSemana;
            $totalesMes['egresos'] += $egresosSemana;
            $totalesMes['saldo_neto'] += $saldoNetoSemana;

            // Verificar mejor y peor semana
            if ($saldoNetoSemana > $mejorSemana['saldo']) {
                $mejorSemana['numero'] = $numeroSemana;
                $mejorSemana['saldo']  = $saldoNetoSemana;
            }

            if ($saldoNetoSemana < $peorSemana['saldo']) {
                $peorSemana['numero'] = $numeroSemana;
                $peorSemana['saldo']  = $saldoNetoSemana;
            }

            // Avanzar al siguiente lunes o al siguiente día si ya estamos en lunes
            $fechaActual = $finSemanaActual->copy()->addDay();
            
            // Si el siguiente día está fuera del mes, salir del bucle
            if ($fechaActual > $fechaFin) {
                break;
            }
            
            // Si no es lunes, avanzar hasta el próximo lunes
            if ($fechaActual->dayOfWeek !== 1) { // 1 = lunes
                $fechaActual = $fechaActual->next('Monday');
            }
            
            $numeroSemana++;
        }

        // Calcular promedios semanales
        $semanasConMovimientos = count(array_filter($movimientosMensuales, function ($semana) {
            return $semana['ingresos'] > 0 || $semana['egresos'] > 0;
        }));

        $promedios = [
            'ingresos'   => $semanasConMovimientos > 0 ? $totalesMes['ingresos'] / $semanasConMovimientos : 0,
            'egresos'    => $semanasConMovimientos > 0 ? $totalesMes['egresos'] / $semanasConMovimientos : 0,
            'saldo_neto' => $semanasConMovimientos > 0 ? $totalesMes['saldo_neto'] / $semanasConMovimientos : 0,
        ];

        $nombreMes = $this->traducirMes($fechaCarbon->format('F'));
        $año      = $fechaCarbon->format('Y');

        return view('cierre-caja.mensual', compact(
            'movimientosMensuales',
            'fechaInicio',
            'fechaFin',
            'totalesMes',
            'mejorSemana',
            'peorSemana',
            'promedios',
            'nombreMes',
            'año'
        ));
    }



    
    public function exportarPDFSemanal(Request $request)
    {
        // Si se proporciona una fecha, usamos esa semana, de lo contrario usamos la semana actual
        $fecha       = $request->get('fecha', now()->format('Y-m-d'));
        $fechaCarbon = Carbon::parse($fecha);

        $fechaInicio = $fechaCarbon->copy()->startOfWeek();
        $fechaFin    = $fechaCarbon->copy()->endOfWeek();

        $resumenSemanal = [];
        $totalesSemana  = [
            'ingresos'   => 0,
            'egresos'    => 0,
            'saldo_neto' => 0,
        ];

        $mejorDia = [
            'fecha' => null,
            'saldo' => -999999999,
            'dia'   => '',
        ];

        $peorDia = [
            'fecha' => null,
            'saldo' => 999999999,
            'dia'   => '',
        ];

        for ($fecha = $fechaInicio->copy(); $fecha <= $fechaFin; $fecha->addDay()) {
            $movimientos = $this->calcularMovimientosDia($fecha->format('Y-m-d'));

            $diaSemana = $this->traducirDiaSemana($fecha->format('l'));

            $resumenDia = [
                'fecha'             => $fecha->format('Y-m-d'),
                'dia'               => $diaSemana,
                'ingresos'          => $movimientos['ingresos']['total'],
                'egresos'           => $movimientos['egresos']['total'],
                'saldo_neto'        => $movimientos['saldo_neto'],
                'desglose_ingresos' => $movimientos['ingresos'],
                'desglose_egresos'  => $movimientos['egresos'],
            ];

            $resumenSemanal[] = $resumenDia;

            // Actualizar totales
            $totalesSemana['ingresos'] += $movimientos['ingresos']['total'];
            $totalesSemana['egresos'] += $movimientos['egresos']['total'];
            $totalesSemana['saldo_neto'] += $movimientos['saldo_neto'];

            // Verificar mejor y peor día
            if ($movimientos['saldo_neto'] > $mejorDia['saldo']) {
                $mejorDia['fecha'] = $fecha->format('Y-m-d');
                $mejorDia['dia']   = $diaSemana;
                $mejorDia['saldo'] = $movimientos['saldo_neto'];
            }

            if ($movimientos['saldo_neto'] < $peorDia['saldo']) {
                $peorDia['fecha'] = $fecha->format('Y-m-d');
                $peorDia['dia']   = $diaSemana;
                $peorDia['saldo'] = $movimientos['saldo_neto'];
            }
        }

        // Calcular promedios
        $diasConMovimientos = count(array_filter($resumenSemanal, function ($dia) {
            return $dia['ingresos'] > 0 || $dia['egresos'] > 0;
        }));

        $promedios = [
            'ingresos'   => $diasConMovimientos > 0 ? $totalesSemana['ingresos'] / $diasConMovimientos : 0,
            'egresos'    => $diasConMovimientos > 0 ? $totalesSemana['egresos'] / $diasConMovimientos : 0,
            'saldo_neto' => $diasConMovimientos > 0 ? $totalesSemana['saldo_neto'] / $diasConMovimientos : 0,
        ];

        $pdf = PDF::loadView('cierre-caja.pdf-semanal', compact(
            'resumenSemanal',
            'fechaInicio',
            'fechaFin',
            'totalesSemana',
            'mejorDia',
            'peorDia',
            'promedios'
        ));

        $nombreArchivo = 'cierre-caja-semanal-' . $fechaInicio->format('d-m-Y') . '-al-' . $fechaFin->format('d-m-Y') . '.pdf';

        return $pdf->download($nombreArchivo);
    }

    public function exportarPDFMensual(Request $request)
    {
        // Si se proporciona una fecha, usamos ese mes, de lo contrario usamos el mes actual
        $fecha       = $request->get('fecha', now()->format('Y-m-d'));
        $fechaCarbon = Carbon::parse($fecha);

        $fechaInicio = $fechaCarbon->copy()->startOfMonth();
        $fechaFin    = $fechaCarbon->copy()->endOfMonth();

        $movimientosMensuales = [];
        $totalesMes           = [
            'ingresos'   => 0,
            'egresos'    => 0,
            'saldo_neto' => 0,
        ];

        $mejorSemana = [
            'numero' => null,
            'saldo'  => -999999999,
        ];

        $peorSemana = [
            'numero' => null,
            'saldo'  => 999999999,
        ];

        // Agrupar por semanas del mes
        $numeroSemana = 1;
        $fechaActual = $fechaInicio->copy();

        while ($fechaActual <= $fechaFin) {
            // Encontrar el final de la semana actual, pero sin salir del mes
            $finSemanaActual = $fechaActual->copy()->endOfWeek();
            if ($finSemanaActual > $fechaFin) {
                $finSemanaActual = $fechaFin->copy();
            }

            $ingresosSemana  = 0;
            $egresosSemana   = 0;
            $saldoNetoSemana = 0;

            $desgloseSemana = [
                'ingresos' => [
                    'alquileres'             => 0,
                    'multas_retraso'         => 0,
                    'ventas'                 => 0,
                    'ingresos_cancelaciones' => 0,
                    'total'                  => 0,
                ],
                'egresos'  => [
                    'compras'                    => 0,
                    'gastos_varios'              => 0,
                    'total'                      => 0,
                ],
            ];

            // Procesar cada día de la semana actual
            for ($fecha = $fechaActual->copy(); $fecha <= $finSemanaActual; $fecha->addDay()) {
                $movimientos = $this->calcularMovimientosDia($fecha->format('Y-m-d'));

                $ingresosSemana += $movimientos['ingresos']['total'];
                $egresosSemana += $movimientos['egresos']['total'];
                $saldoNetoSemana += $movimientos['saldo_neto'];

                // Acumular desglose
                foreach ($movimientos['ingresos'] as $key => $valor) {
                    if (isset($desgloseSemana['ingresos'][$key])) {
                        $desgloseSemana['ingresos'][$key] += $valor;
                    }
                }

                foreach ($movimientos['egresos'] as $key => $valor) {
                    if (isset($desgloseSemana['egresos'][$key])) {
                        $desgloseSemana['egresos'][$key] += $valor;
                    }
                }
            }

            $movimientosMensuales[] = [
                'semana'       => $numeroSemana,
                'fecha_inicio' => $fechaActual->format('d/m'),
                'fecha_fin'    => $finSemanaActual->format('d/m'),
                'ingresos'     => $ingresosSemana,
                'egresos'      => $egresosSemana,
                'saldo_neto'   => $saldoNetoSemana,
                'desglose'     => $desgloseSemana,
            ];

            // Actualizar totales
            $totalesMes['ingresos'] += $ingresosSemana;
            $totalesMes['egresos'] += $egresosSemana;
            $totalesMes['saldo_neto'] += $saldoNetoSemana;

            // Verificar mejor y peor semana
            if ($saldoNetoSemana > $mejorSemana['saldo']) {
                $mejorSemana['numero'] = $numeroSemana;
                $mejorSemana['saldo']  = $saldoNetoSemana;
            }

            if ($saldoNetoSemana < $peorSemana['saldo']) {
                $peorSemana['numero'] = $numeroSemana;
                $peorSemana['saldo']  = $saldoNetoSemana;
            }

            // Avanzar al siguiente lunes o al siguiente día si ya estamos en lunes
            $fechaActual = $finSemanaActual->copy()->addDay();
            
            // Si el siguiente día está fuera del mes, salir del bucle
            if ($fechaActual > $fechaFin) {
                break;
            }
            
            // Si no es lunes, avanzar hasta el próximo lunes
            if ($fechaActual->dayOfWeek !== 1) { // 1 = lunes
                $fechaActual = $fechaActual->next('Monday');
            }
            
            $numeroSemana++;
        }

        // Calcular promedios semanales
        $semanasConMovimientos = count(array_filter($movimientosMensuales, function ($semana) {
            return $semana['ingresos'] > 0 || $semana['egresos'] > 0;
        }));

        $promedios = [
            'ingresos'   => $semanasConMovimientos > 0 ? $totalesMes['ingresos'] / $semanasConMovimientos : 0,
            'egresos'    => $semanasConMovimientos > 0 ? $totalesMes['egresos'] / $semanasConMovimientos : 0,
            'saldo_neto' => $semanasConMovimientos > 0 ? $totalesMes['saldo_neto'] / $semanasConMovimientos : 0,
        ];

        $nombreMes = $this->traducirMes($fechaCarbon->format('F'));
        $año      = $fechaCarbon->format('Y');

        $pdf = PDF::loadView('cierre-caja.pdf-mensual', compact(
            'movimientosMensuales',
            'fechaInicio',
            'fechaFin',
            'totalesMes',
            'mejorSemana',
            'peorSemana',
            'promedios',
            'nombreMes',
            'año'
        ));

        $nombreArchivo = 'cierre-caja-mensual-' . $nombreMes . '-' . $año . '.pdf';

        return $pdf->download($nombreArchivo);
    }
    /**
     * Traduce el nombre del día de la semana al español
     */
       private function traducirDiaSemana($dia)
    {
        $dias = [
            'Monday'    => 'Lunes',
            'Tuesday'   => 'Martes',
            'Wednesday' => 'Miércoles',
            'Thursday'  => 'Jueves',
            'Friday'    => 'Viernes',
            'Saturday'  => 'Sábado',
            'Sunday'    => 'Domingo',
        ];

        return $dias[$dia] ?? $dia;
    }
    /**
     * Traduce el nombre del mes al español
     */
    private function traducirMes($mes)
    {
        $meses = [
            'January'   => 'Enero',
            'February'  => 'Febrero',
            'March'     => 'Marzo',
            'April'     => 'Abril',
            'May'       => 'Mayo',
            'June'      => 'Junio',
            'July'      => 'Julio',
            'August'    => 'Agosto',
            'September' => 'Septiembre',
            'October'   => 'Octubre',
            'November'  => 'Noviembre',
            'December'  => 'Diciembre',
        ];

        return $meses[$mes] ?? $mes;
    }
}
