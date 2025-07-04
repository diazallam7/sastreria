@extends('template')

@section('title', 'Resumen Mensual de Caja')

@push('css')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .table-hover tbody tr:hover {
            background-color: rgba(0, 123, 255, 0.1);
        }

        .bg-light-success {
            background-color: rgba(40, 167, 69, 0.1);
        }

        .bg-light-danger {
            background-color: rgba(220, 53, 69, 0.1);
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid px-4">
        <h1 class="mt-4 text-center">
            <i class="fas fa-calendar-alt me-2"></i>Resumen Mensual de Caja
        </h1>

        <ol class="breadcrumb mb-4">
            <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
            <li class="breadcrumb-item"><a href="{{ route('cierre-caja.index') }}">Cierre de Caja</a></li>
            <li class="breadcrumb-item active">Resumen Mensual</li>
        </ol>

        <!-- Selector de Mes -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">{{ $nombreMes }} {{ $año }}</h5>
                        <form action="{{ route('cierre-caja.mensual') }}" method="GET" class="d-flex">
                            <input type="month" name="fecha" class="form-control me-2"
                                value="{{ request('fecha', now()->format('Y-m')) }}" max="{{ now()->format('Y-m') }}">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search me-1"></i>Consultar
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-6 text-end">
                <div class="btn-group">
                    <a href="{{ route('cierre-caja.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-calendar-day me-1"></i>Vista Diaria
                    </a>
                    <a href="{{ route('cierre-caja.semanal') }}" class="btn btn-outline-info">
                        <i class="fas fa-calendar-week me-1"></i>Vista Semanal
                    </a>
                    <a href="{{ route('cierre-caja.cierre-caja.mensual.pdf', ['fecha' => request('fecha', now()->format('Y-m-d'))]) }}"
                        class="btn btn-danger" target="_blank">
                        <i class="fas fa-file-pdf me-1"></i>Exportar PDF
                    </a>
                </div>
            </div>
        </div>

        <!-- Resumen Principal -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card border-success">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-arrow-up me-2"></i>Total Ingresos Mes
                        </h5>
                    </div>
                    <div class="card-body text-center">
                        <h2 class="text-success">₲ {{ number_format($totalesMes['ingresos'], 0, ',', '.') }}</h2>
                        <small class="text-muted">Promedio semanal: ₲
                            {{ number_format($promedios['ingresos'], 0, ',', '.') }}</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-danger">
                    <div class="card-header bg-danger text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-arrow-down me-2"></i>Total Egresos Mes
                        </h5>
                    </div>
                    <div class="card-body text-center">
                        <h2 class="text-danger">₲ {{ number_format($totalesMes['egresos'], 0, ',', '.') }}</h2>
                        <small class="text-muted">Promedio semanal: ₲
                            {{ number_format($promedios['egresos'], 0, ',', '.') }}</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-{{ $totalesMes['saldo_neto'] >= 0 ? 'primary' : 'warning' }}">
                    <div class="card-header bg-{{ $totalesMes['saldo_neto'] >= 0 ? 'primary' : 'warning' }} text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-balance-scale me-2"></i>Saldo Neto Mes
                        </h5>
                    </div>
                    <div class="card-body text-center">
                        <h2 class="text-{{ $totalesMes['saldo_neto'] >= 0 ? 'primary' : 'warning' }}">
                            ₲ {{ number_format($totalesMes['saldo_neto'], 0, ',', '.') }}
                        </h2>
                        <small class="text-muted">Promedio semanal: ₲
                            {{ number_format($promedios['saldo_neto'], 0, ',', '.') }}</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla de Resumen Semanal -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-table me-1"></i>
                Detalle Semanal del Mes
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Semana</th>
                                <th>Período</th>
                                <th class="text-end">Ingresos</th>
                                <th class="text-end">Egresos</th>
                                <th class="text-end">Saldo Neto</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($movimientosMensuales as $semana)
                                <tr
                                    class="{{ $semana['saldo_neto'] > 0 ? 'bg-light-success' : ($semana['saldo_neto'] < 0 ? 'bg-light-danger' : '') }}">
                                    <td><strong>Semana {{ $semana['semana'] }}</strong></td>
                                    <td>{{ $semana['fecha_inicio'] }} al {{ $semana['fecha_fin'] }}</td>
                                    <td class="text-end">₲ {{ number_format($semana['ingresos'], 0, ',', '.') }}</td>
                                    <td class="text-end">₲ {{ number_format($semana['egresos'], 0, ',', '.') }}</td>
                                    <td
                                        class="text-end fw-bold {{ $semana['saldo_neto'] >= 0 ? 'text-success' : 'text-danger' }}">
                                        ₲ {{ number_format($semana['saldo_neto'], 0, ',', '.') }}
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                                            data-bs-target="#detalleModal{{ $semana['semana'] }}">
                                            <i class="fas fa-eye"></i> Ver Detalle
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-dark">
                            <tr>
                                <th colspan="2">TOTALES</th>
                                <th class="text-end">₲ {{ number_format($totalesMes['ingresos'], 0, ',', '.') }}</th>
                                <th class="text-end">₲ {{ number_format($totalesMes['egresos'], 0, ',', '.') }}</th>
                                <th class="text-end">₲ {{ number_format($totalesMes['saldo_neto'], 0, ',', '.') }}</th>
                                <th></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- Estadísticas Adicionales -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Estadísticas del Mes</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <h6>Mejor semana del mes:</h6>
                            <p class="mb-1">
                                <strong>Semana {{ $mejorSemana['numero'] }}</strong>
                            </p>
                            <p class="text-success">
                                <i class="fas fa-arrow-up me-1"></i>
                                ₲ {{ number_format($mejorSemana['saldo'], 0, ',', '.') }}
                            </p>
                        </div>
                        <div>
                            <h6>Peor semana del mes:</h6>
                            <p class="mb-1">
                                <strong>Semana {{ $peorSemana['numero'] }}</strong>
                            </p>
                            <p class="text-danger">
                                <i class="fas fa-arrow-down me-1"></i>
                                ₲ {{ number_format($peorSemana['saldo'], 0, ',', '.') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-percentage me-2"></i>Distribución de Ingresos</h5>
                    </div>
                    <div class="card-body">
                        @php
                            $totalIngresos = $totalesMes['ingresos'];
                            $categorias = [
                                'alquileres' => ['nombre' => 'Alquileres', 'total' => 0],
                                'multas_retraso' => ['nombre' => 'Multas', 'total' => 0],
                                'ventas' => ['nombre' => 'Ventas', 'total' => 0],
                                'ingresos_cancelaciones' => ['nombre' => 'Ingresos por Cancelaciones', 'total' => 0],
                            ];

                            foreach ($movimientosMensuales as $semana) {
                                foreach ($categorias as $key => $value) {
                                    $categorias[$key]['total'] += $semana['desglose']['ingresos'][$key] ?? 0;
                                }
                            }
                        @endphp

                        @foreach ($categorias as $key => $categoria)
                            @php
                                $porcentaje = $totalIngresos > 0 ? ($categoria['total'] / $totalIngresos) * 100 : 0;
                            @endphp
                            @if($categoria['total'] > 0)
                            <div class="mb-2">
                                <div class="d-flex justify-content-between mb-1">
                                    <span>{{ $categoria['nombre'] }}</span>
                                    <span>{{ number_format($porcentaje, 1) }}%</span>
                                </div>
                                <div class="progress" style="height: 10px;">
                                    <div class="progress-bar bg-success" role="progressbar"
                                        style="width: {{ $porcentaje }}%" aria-valuenow="{{ $porcentaje }}"
                                        aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Modales de Detalle por Semana -->
        @foreach ($movimientosMensuales as $semana)
            <div class="modal fade" id="detalleModal{{ $semana['semana'] }}" tabindex="-1"
                aria-labelledby="detalleModalLabel{{ $semana['semana'] }}" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="detalleModalLabel{{ $semana['semana'] }}">
                                Detalle Semana {{ $semana['semana'] }} ({{ $semana['fecha_inicio'] }} al
                                {{ $semana['fecha_fin'] }})
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6 class="border-bottom pb-2">Desglose de Ingresos</h6>
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <tbody>
                                                <tr>
                                                    <td>Alquileres:</td>
                                                    <td class="text-end">₲
                                                        {{ number_format($semana['desglose']['ingresos']['alquileres'] ?? 0, 0, ',', '.') }}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>Multas por Retraso:</td>
                                                    <td class="text-end">₲
                                                        {{ number_format($semana['desglose']['ingresos']['multas_retraso'] ?? 0, 0, ',', '.') }}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>Ventas:</td>
                                                    <td class="text-end">₲
                                                        {{ number_format($semana['desglose']['ingresos']['ventas'] ?? 0, 0, ',', '.') }}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>Ingresos por Cancelaciones:</td>
                                                    <td class="text-end">₲
                                                        {{ number_format($semana['desglose']['ingresos']['ingresos_cancelaciones'] ?? 0, 0, ',', '.') }}
                                                    </td>
                                                </tr>
                                                <tr class="table-success">
                                                    <th>Total Ingresos:</th>
                                                    <th class="text-end">₲
                                                        {{ number_format($semana['ingresos'], 0, ',', '.') }}</th>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="border-bottom pb-2">Desglose de Egresos</h6>
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <tbody>
                                                <tr>
                                                    <td>Compras:</td>
                                                    <td class="text-end">₲
                                                        {{ number_format($semana['desglose']['egresos']['compras'] ?? 0, 0, ',', '.') }}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>Gastos Varios:</td>
                                                    <td class="text-end">₲
                                                        {{ number_format($semana['desglose']['egresos']['gastos_varios'] ?? 0, 0, ',', '.') }}
                                                    </td>
                                                </tr>
                                                <tr class="table-danger">
                                                    <th>Total Egresos:</th>
                                                    <th class="text-end">₲
                                                        {{ number_format($semana['egresos'], 0, ',', '.') }}</th>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-12">
                                    <div class="alert alert-{{ $semana['saldo_neto'] >= 0 ? 'success' : 'danger' }}">
                                        <strong>Saldo Neto de la Semana: ₲
                                            {{ number_format($semana['saldo_neto'], 0, ',', '.') }}</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                            <a href="{{ route('cierre-caja.semanal', ['fecha' => $fechaInicio->copy()->addWeeks($semana['semana'] - 1)->format('Y-m-d')]) }}"
                                class="btn btn-primary">
                                Ver Semana Completa
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endsection

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-seleccionar el mes si no hay fecha seleccionada
            const fechaInput = document.querySelector('input[name="fecha"]');
            if (!fechaInput.value) {
                const now = new Date();
                const year = now.getFullYear();
                const month = (now.getMonth() + 1).toString().padStart(2, '0');
                fechaInput.value = `${year}-${month}`;
            }
        });
    </script>
@endpush
