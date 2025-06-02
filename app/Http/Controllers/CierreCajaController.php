<?php
namespace App\Http\Controllers;

use App\Models\Alquiler;
use App\Models\Compra;
use App\Models\Devolucion;
use App\Models\GastoVario;
use App\Models\Reserva;
use App\Models\Venta;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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
    public function index()
    {
        $fechaHoy    = now()->format('Y-m-d');
        $movimientos = $this->calcularMovimientosDia($fechaHoy);

        return view('cierre-caja.index', compact('movimientos', 'fechaHoy'));
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

    public function calcularMovimientosDia($fecha)
    {
        // INGRESOS
        // 1. Señas recibidas en reservas del día
        $señasRecibidas = Reserva::whereDate('created_at', $fecha)
            ->whereIn('estado', ['confirmada', 'entregada', 'cancelada'])
            ->sum(DB::raw('seña_alquiler + seña_garantia'));

        // 2. Alquileres iniciados del día (usando fecha_inicio)
        $alquileresDelDia = Alquiler::whereDate('fecha_inicio', $fecha)->get();
        $totalAlquileres  = $alquileresDelDia->sum('costo_total');

        // 3. Multas por retraso (si existen en devoluciones)
        $multasRetraso = 0;
        if (Schema::hasTable('devoluciones') && Schema::hasColumn('devoluciones', 'multa_aplicada_real')) {
            $multasRetraso = Devolucion::whereDate('fecha_devolucion', $fecha)
                ->sum('multa_aplicada_real'); // Usar multa_aplicada_real en lugar de multa
        } elseif (Schema::hasTable('devoluciones') && Schema::hasColumn('devoluciones', 'multa_aplicada')) {
            $multasRetraso = Devolucion::whereDate('fecha_devolucion', $fecha)
                ->sum('multa_aplicada'); // Alternativa si no existe multa_aplicada_real
        } elseif (Schema::hasTable('devoluciones') && Schema::hasColumn('devoluciones', 'multa')) {
            $multasRetraso = Devolucion::whereDate('fecha_devolucion', $fecha)
                ->sum('multa'); // Última alternativa
        }

        // 4. Ventas del día - Corregido para usar precio_total
        $ventasDelDia = Venta::whereDate('fecha_venta', $fecha)->get();
        $totalVentas  = $ventasDelDia->sum('precio_total');

        // 5. Cancelaciones del día
        $cancelacionesDelDia = Reserva::where('estado', 'cancelada')
            ->whereDate('updated_at', $fecha)
            ->get();

        $ingresosCancelaciones = $cancelacionesDelDia->sum(function ($reserva) {
            $totalRecibido = $reserva->seña_alquiler + $reserva->seña_garantia;
            $devuelto      = $reserva->seña_devuelta ?? 0;
            $diferencia    = $totalRecibido - $devuelto;
            return $diferencia > 0 ? $diferencia : 0;
        });

        // EGRESOS
        // 1. Devoluciones por cancelaciones
        $devolucionesCancelaciones = $cancelacionesDelDia->sum('seña_devuelta');

        // 2. Devoluciones de garantías (si existe la tabla)
        $garantiasDevueltas = 0;
        $devolucionesDelDia = collect();
        if (Schema::hasTable('devoluciones')) {
            try {
                $devolucionesDelDia = Devolucion::whereDate('fecha_devolucion', $fecha)
                    ->with(['alquiler.cliente'])
                    ->get();

                // Intentar con diferentes campos según lo que exista
                if (Schema::hasColumn('devoluciones', 'monto_devuelto_real')) {
                    $garantiasDevueltas = $devolucionesDelDia->sum('monto_devuelto_real');
                } elseif (Schema::hasColumn('devoluciones', 'monto_devuelto')) {
                    $garantiasDevueltas = $devolucionesDelDia->sum('monto_devuelto');
                }
            } catch (\Exception $e) {
                // Si hay error en la consulta, mantener valores por defecto
                $devolucionesDelDia = collect();
                $garantiasDevueltas = 0;
            }
        }

        // 3. Compras del día
        $comprasDelDia = Compra::whereDate('fecha_compra', $fecha)->get();
        $totalCompras  = $comprasDelDia->sum('precio_compra');

        // 4. Gastos varios del día
        $gastosDelDia = GastoVario::whereDate('fecha', $fecha)->get();
        $totalGastos  = $gastosDelDia->sum('monto');

        // TOTALES
        $totalIngresos = $señasRecibidas + $totalAlquileres + $multasRetraso + $totalVentas + $ingresosCancelaciones;
        $totalEgresos  = $devolucionesCancelaciones + $garantiasDevueltas + $totalCompras + $totalGastos;
        $saldoNeto     = $totalIngresos - $totalEgresos;

        // DETALLES PARA EL REPORTE
        $reservasDelDia = Reserva::whereDate('created_at', $fecha)
            ->whereIn('estado', ['confirmada', 'entregada', 'cancelada'])
            ->with('cliente')
            ->get();

        $alquileresIniciados = Alquiler::whereDate('fecha_inicio', $fecha)
            ->with(['cliente'])
            ->get();

        return [
            'fecha'      => $fecha,
            'ingresos'   => [
                'señas_recibidas'        => $señasRecibidas,
                'alquileres'             => $totalAlquileres,
                'multas_retraso'         => $multasRetraso,
                'ventas'                 => $totalVentas,
                'ingresos_cancelaciones' => $ingresosCancelaciones,
                'total'                  => $totalIngresos,
            ],
            'egresos'    => [
                'devoluciones_cancelaciones' => $devolucionesCancelaciones,
                'garantias_devueltas'        => $garantiasDevueltas,
                'compras'                    => $totalCompras,
                'gastos_varios'              => $totalGastos,
                'total'                      => $totalEgresos,
            ],
            'saldo_neto' => $saldoNeto,
            'detalles'   => [
                'reservas'             => $reservasDelDia,
                'cancelaciones'        => $cancelacionesDelDia,
                'alquileres_iniciados' => $alquileresIniciados,
                'ventas'               => $ventasDelDia,
                'compras'              => $comprasDelDia,
                'gastos'               => $gastosDelDia,
                'devoluciones'         => $devolucionesDelDia,
            ],
        ];
    }

    public function exportarPDF(Request $request)
    {
        $fecha       = $request->get('fecha', now()->format('Y-m-d'));
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

        // Agrupar por semanas
        $semanaActual = $fechaInicio->copy()->startOfWeek();
        $numeroSemana = 1;

        while ($semanaActual <= $fechaFin) {
            $finSemana = $semanaActual->copy()->endOfWeek();
            if ($finSemana > $fechaFin) {
                $finSemana = $fechaFin->copy();
            }

            $ingresosSemana  = 0;
            $egresosSemana   = 0;
            $saldoNetoSemana = 0;

            $desgloseSemana = [
                'ingresos' => [
                    'señas_recibidas'        => 0,
                    'alquileres'             => 0,
                    'multas_retraso'         => 0,
                    'ventas'                 => 0,
                    'ingresos_cancelaciones' => 0,
                    'total'                  => 0,
                ],
                'egresos'  => [
                    'devoluciones_cancelaciones' => 0,
                    'garantias_devueltas'        => 0,
                    'compras'                    => 0,
                    'gastos_varios'              => 0,
                    'total'                      => 0,
                ],
            ];

            for ($fecha = $semanaActual->copy(); $fecha <= $finSemana; $fecha->addDay()) {
                // Solo incluir días que pertenecen al mes actual
                if ($fecha->month == $fechaCarbon->month) {
                    $movimientos = $this->calcularMovimientosDia($fecha->format('Y-m-d'));

                    $ingresosSemana += $movimientos['ingresos']['total'];
                    $egresosSemana += $movimientos['egresos']['total'];
                    $saldoNetoSemana += $movimientos['saldo_neto'];

                    // Acumular desglose
                    foreach ($movimientos['ingresos'] as $key => $valor) {
                        $desgloseSemana['ingresos'][$key] += $valor;
                    }

                    foreach ($movimientos['egresos'] as $key => $valor) {
                        $desgloseSemana['egresos'][$key] += $valor;
                    }
                }
            }

            $movimientosMensuales[] = [
                'semana'       => $numeroSemana,
                'fecha_inicio' => $semanaActual->format('d/m'),
                'fecha_fin'    => $finSemana->format('d/m'),
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

            $semanaActual->addWeek();
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

        // Agrupar por semanas
        $semanaActual = $fechaInicio->copy()->startOfWeek();
        $numeroSemana = 1;

        while ($semanaActual <= $fechaFin) {
            $finSemana = $semanaActual->copy()->endOfWeek();
            if ($finSemana > $fechaFin) {
                $finSemana = $fechaFin->copy();
            }

            $ingresosSemana  = 0;
            $egresosSemana   = 0;
            $saldoNetoSemana = 0;

            $desgloseSemana = [
                'ingresos' => [
                    'señas_recibidas'        => 0,
                    'alquileres'             => 0,
                    'multas_retraso'         => 0,
                    'ventas'                 => 0,
                    'ingresos_cancelaciones' => 0,
                    'total'                  => 0,
                ],
                'egresos'  => [
                    'devoluciones_cancelaciones' => 0,
                    'garantias_devueltas'        => 0,
                    'compras'                    => 0,
                    'gastos_varios'              => 0,
                    'total'                      => 0,
                ],
            ];

            for ($fecha = $semanaActual->copy(); $fecha <= $finSemana; $fecha->addDay()) {
                // Solo incluir días que pertenecen al mes actual
                if ($fecha->month == $fechaCarbon->month) {
                    $movimientos = $this->calcularMovimientosDia($fecha->format('Y-m-d'));

                    $ingresosSemana += $movimientos['ingresos']['total'];
                    $egresosSemana += $movimientos['egresos']['total'];
                    $saldoNetoSemana += $movimientos['saldo_neto'];

                    // Acumular desglose
                    foreach ($movimientos['ingresos'] as $key => $valor) {
                        $desgloseSemana['ingresos'][$key] += $valor;
                    }

                    foreach ($movimientos['egresos'] as $key => $valor) {
                        $desgloseSemana['egresos'][$key] += $valor;
                    }
                }
            }

            $movimientosMensuales[] = [
                'semana'       => $numeroSemana,
                'fecha_inicio' => $semanaActual->format('d/m'),
                'fecha_fin'    => $finSemana->format('d/m'),
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

            $semanaActual->addWeek();
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
