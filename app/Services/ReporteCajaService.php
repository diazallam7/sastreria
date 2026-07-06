<?php

namespace App\Services;

use App\Models\Alquiler;
use App\Models\Devolucion;
use App\Models\GastoVario;
use App\Models\Producto;
use App\Models\Reserva;
use App\Models\Venta;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ReporteCajaService
{
    /**
     * Totales de caja por día para un rango, con pocas queries agrupadas
     * (GROUP BY DATE) en vez de recalcular día por día. Registros anulados
     * (soft-deleted) quedan excluidos automáticamente.
     *
     * @return Collection<string, array> keyed by 'Y-m-d'
     */
    public function totalesPorRango(Carbon $desde, Carbon $hasta): Collection
    {
        $inicio = $desde->copy()->startOfDay();
        $fin    = $hasta->copy()->endOfDay();

        // --- INGRESOS ---
        $ventas = Venta::whereBetween('fecha_venta', [$inicio, $fin])
            ->groupByRaw('DATE(fecha_venta)')
            ->selectRaw('DATE(fecha_venta) as f, SUM(precio_total) as t')->pluck('t', 'f');

        $alquileres = Alquiler::where('estado', '!=', 'cancelado')
            ->whereBetween('fecha_inicio', [$inicio, $fin])
            ->groupByRaw('DATE(fecha_inicio)')
            ->selectRaw('DATE(fecha_inicio) as f, SUM(costo_total) as t')->pluck('t', 'f');

        $multas = Devolucion::whereBetween('fecha_devolucion', [$inicio, $fin])
            ->groupByRaw('DATE(fecha_devolucion)')
            ->selectRaw('DATE(fecha_devolucion) as f, SUM(multa_aplicada) as t')->pluck('t', 'f');

        // Cancelaciones: ingreso neto = recibido - devuelto (la validación impide devolver de más).
        $cancelaciones = Reserva::where('estado', 'cancelada')
            ->whereBetween('updated_at', [$inicio, $fin])
            ->groupByRaw('DATE(updated_at)')
            ->selectRaw('DATE(updated_at) as f, SUM(senia_alquiler + senia_garantia - COALESCE(senia_devuelta, 0)) as t')
            ->pluck('t', 'f');

        // --- EGRESOS ---
        // Egreso de compra = precio_compra * unidades compradas (sumadas por talle).
        $compras = Producto::comprados()
            ->join('producto_talles', 'producto_talles.producto_id', '=', 'productos.id')
            ->whereBetween('fecha_compra', [$inicio, $fin])
            ->groupByRaw('DATE(fecha_compra)')
            ->selectRaw('DATE(fecha_compra) as f, SUM(precio_compra * cantidad_total) as t')->pluck('t', 'f');

        $gastos = GastoVario::whereBetween('fecha', [$inicio, $fin])
            ->groupByRaw('DATE(fecha)')
            ->selectRaw('DATE(fecha) as f, SUM(monto) as t')->pluck('t', 'f');

        $resultado = collect();

        for ($fecha = $desde->copy()->startOfDay(); $fecha <= $fin; $fecha->addDay()) {
            $k = $fecha->toDateString();

            $ingresos = [
                'alquileres'             => (int) ($alquileres[$k] ?? 0),
                'multas_retraso'         => (int) ($multas[$k] ?? 0),
                'ventas'                 => (int) ($ventas[$k] ?? 0),
                'ingresos_cancelaciones' => (int) ($cancelaciones[$k] ?? 0),
            ];
            $ingresos['total'] = array_sum($ingresos);

            $egresos = [
                'compras'       => (int) ($compras[$k] ?? 0),
                'gastos_varios' => (int) ($gastos[$k] ?? 0),
            ];
            $egresos['total'] = array_sum($egresos);

            $resultado[$k] = [
                'fecha'      => $k,
                'ingresos'   => $ingresos,
                'egresos'    => $egresos,
                'saldo_neto' => $ingresos['total'] - $egresos['total'],
            ];
        }

        return $resultado;
    }

    /**
     * Movimientos de un solo día: totales + colecciones de detalle
     * (para el listado y el PDF diario).
     *
     * @return array
     */
    public function movimientosDia(string $fecha): array
    {
        $carbon  = Carbon::parse($fecha);
        $totales = $this->totalesPorRango($carbon->copy(), $carbon->copy())->first();

        $detalles = [
            'reservas'              => collect(), // reservado para detalle futuro; mantiene el shape de la vista
            'ventas'                => Venta::with('cliente')->whereDate('fecha_venta', $fecha)->get(),
            'alquileres_iniciados'  => Alquiler::with('cliente')->where('estado', '!=', 'cancelado')->whereDate('fecha_inicio', $fecha)->get(),
            'devoluciones'          => Devolucion::with('alquiler.cliente')->whereDate('fecha_devolucion', $fecha)->get(),
            'cancelaciones'         => Reserva::with('cliente')->where('estado', 'cancelada')->whereDate('updated_at', $fecha)->get(),
            'compras'               => Producto::comprados()->with('talles')->whereDate('fecha_compra', $fecha)->get(),
            'gastos'                => GastoVario::whereDate('fecha', $fecha)->get(),
        ];

        return array_merge($totales, ['detalles' => $detalles]);
    }

    /** Arma el resumen semanal (por día) a partir de una fecha cualquiera de la semana. */
    public function resumenSemanal(Carbon $fecha): array
    {
        $fechaInicio = $fecha->copy()->startOfWeek();
        $fechaFin    = $fecha->copy()->endOfWeek();
        $dias        = $this->totalesPorRango($fechaInicio, $fechaFin);

        $resumenSemanal = [];
        $totalesSemana  = ['ingresos' => 0, 'egresos' => 0, 'saldo_neto' => 0];
        $mejorDia       = ['fecha' => null, 'dia' => '', 'saldo' => PHP_INT_MIN];
        $peorDia        = ['fecha' => null, 'dia' => '', 'saldo' => PHP_INT_MAX];

        foreach ($dias as $fechaDia => $mov) {
            $diaSemana = $this->traducirDiaSemana(Carbon::parse($fechaDia)->format('l'));

            $resumenSemanal[] = [
                'fecha'             => $fechaDia,
                'dia'               => $diaSemana,
                'ingresos'          => $mov['ingresos']['total'],
                'egresos'           => $mov['egresos']['total'],
                'saldo_neto'        => $mov['saldo_neto'],
                'desglose_ingresos' => $mov['ingresos'],
                'desglose_egresos'  => $mov['egresos'],
            ];

            $totalesSemana['ingresos']   += $mov['ingresos']['total'];
            $totalesSemana['egresos']    += $mov['egresos']['total'];
            $totalesSemana['saldo_neto'] += $mov['saldo_neto'];

            if ($mov['saldo_neto'] > $mejorDia['saldo']) {
                $mejorDia = ['fecha' => $fechaDia, 'dia' => $diaSemana, 'saldo' => $mov['saldo_neto']];
            }
            if ($mov['saldo_neto'] < $peorDia['saldo']) {
                $peorDia = ['fecha' => $fechaDia, 'dia' => $diaSemana, 'saldo' => $mov['saldo_neto']];
            }
        }

        $conMovimientos = count(array_filter($resumenSemanal, fn ($d) => $d['ingresos'] > 0 || $d['egresos'] > 0));

        return compact('resumenSemanal', 'fechaInicio', 'fechaFin', 'totalesSemana', 'mejorDia', 'peorDia')
            + ['promedios' => $this->promedios($totalesSemana, $conMovimientos)];
    }

    /** Arma el resumen mensual (agrupado por semanas) a partir de una fecha del mes. */
    public function resumenMensual(Carbon $fecha): array
    {
        $fechaInicio = $fecha->copy()->startOfMonth();
        $fechaFin    = $fecha->copy()->endOfMonth();
        $dias        = $this->totalesPorRango($fechaInicio, $fechaFin);

        $movimientosMensuales = [];
        $totalesMes  = ['ingresos' => 0, 'egresos' => 0, 'saldo_neto' => 0];
        $mejorSemana = ['numero' => null, 'saldo' => PHP_INT_MIN];
        $peorSemana  = ['numero' => null, 'saldo' => PHP_INT_MAX];

        $numeroSemana = 1;
        $cursor = $fechaInicio->copy();

        while ($cursor <= $fechaFin) {
            $finSemana = $cursor->copy()->endOfWeek()->min($fechaFin);

            $ingresos = $egresos = $saldo = 0;
            $desglose = [
                'ingresos' => ['alquileres' => 0, 'multas_retraso' => 0, 'ventas' => 0, 'ingresos_cancelaciones' => 0, 'total' => 0],
                'egresos'  => ['compras' => 0, 'gastos_varios' => 0, 'total' => 0],
            ];

            for ($d = $cursor->copy(); $d <= $finSemana; $d->addDay()) {
                $mov = $dias[$d->toDateString()] ?? null;
                if (! $mov) {
                    continue;
                }

                $ingresos += $mov['ingresos']['total'];
                $egresos  += $mov['egresos']['total'];
                $saldo    += $mov['saldo_neto'];

                foreach ($mov['ingresos'] as $key => $valor) {
                    $desglose['ingresos'][$key] += $valor;
                }
                foreach ($mov['egresos'] as $key => $valor) {
                    $desglose['egresos'][$key] += $valor;
                }
            }

            $movimientosMensuales[] = [
                'semana'       => $numeroSemana,
                'fecha_inicio' => $cursor->format('d/m'),
                'fecha_fin'    => $finSemana->format('d/m'),
                'ingresos'     => $ingresos,
                'egresos'      => $egresos,
                'saldo_neto'   => $saldo,
                'desglose'     => $desglose,
            ];

            $totalesMes['ingresos']   += $ingresos;
            $totalesMes['egresos']    += $egresos;
            $totalesMes['saldo_neto'] += $saldo;

            if ($saldo > $mejorSemana['saldo']) {
                $mejorSemana = ['numero' => $numeroSemana, 'saldo' => $saldo];
            }
            if ($saldo < $peorSemana['saldo']) {
                $peorSemana = ['numero' => $numeroSemana, 'saldo' => $saldo];
            }

            $cursor = $finSemana->copy()->addDay();
            $numeroSemana++;
        }

        $conMovimientos = count(array_filter($movimientosMensuales, fn ($s) => $s['ingresos'] > 0 || $s['egresos'] > 0));

        return compact('movimientosMensuales', 'fechaInicio', 'fechaFin', 'totalesMes', 'mejorSemana', 'peorSemana')
            + [
                'promedios' => $this->promedios($totalesMes, $conMovimientos),
                'nombreMes' => $this->traducirMes($fecha->format('F')),
                'año'       => $fecha->format('Y'),
            ];
    }

    /** @param array{ingresos:int,egresos:int,saldo_neto:int} $totales */
    private function promedios(array $totales, int $divisor): array
    {
        $div = max(1, $divisor);

        return [
            'ingresos'   => $divisor > 0 ? $totales['ingresos'] / $div : 0,
            'egresos'    => $divisor > 0 ? $totales['egresos'] / $div : 0,
            'saldo_neto' => $divisor > 0 ? $totales['saldo_neto'] / $div : 0,
        ];
    }

    private function traducirDiaSemana(string $dia): string
    {
        return [
            'Monday' => 'Lunes', 'Tuesday' => 'Martes', 'Wednesday' => 'Miércoles',
            'Thursday' => 'Jueves', 'Friday' => 'Viernes', 'Saturday' => 'Sábado', 'Sunday' => 'Domingo',
        ][$dia] ?? $dia;
    }

    private function traducirMes(string $mes): string
    {
        return [
            'January' => 'Enero', 'February' => 'Febrero', 'March' => 'Marzo', 'April' => 'Abril',
            'May' => 'Mayo', 'June' => 'Junio', 'July' => 'Julio', 'August' => 'Agosto',
            'September' => 'Septiembre', 'October' => 'Octubre', 'November' => 'Noviembre', 'December' => 'Diciembre',
        ][$mes] ?? $mes;
    }
}
