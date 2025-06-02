@extends('template')

@section('title', 'Resumen Semanal de Caja')

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
        <i class="fas fa-calendar-week me-2"></i>Resumen Semanal de Caja
    </h1>
    
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item"><a href="{{ route('cierre-caja.index') }}">Cierre de Caja</a></li>
        <li class="breadcrumb-item active">Resumen Semanal</li>
    </ol>

    <!-- Selector de Semana -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Semana del {{ $fechaInicio->format('d/m/Y') }} al {{ $fechaFin->format('d/m/Y') }}</h5>
                    <form action="{{ route('cierre-caja.semanal') }}" method="GET" class="d-flex">
                        <input type="date" name="fecha" class="form-control me-2" 
                               value="{{ request('fecha', now()->format('Y-m-d')) }}" max="{{ now()->format('Y-m-d') }}">
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
                <a href="{{ route('cierre-caja.mensual') }}" class="btn btn-outline-info">
                    <i class="fas fa-calendar-alt me-1"></i>Vista Mensual
                </a>
                <a href="{{ route('cierre-caja.cierre-caja.semanal.pdf', ['fecha' => request('fecha', now()->format('Y-m-d'))]) }}" 
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
                        <i class="fas fa-arrow-up me-2"></i>Total Ingresos Semana
                    </h5>
                </div>
                <div class="card-body text-center">
                    <h2 class="text-success">₲ {{ number_format($totalesSemana['ingresos'], 0, ',', '.') }}</h2>
                    <small class="text-muted">Promedio diario: ₲ {{ number_format($promedios['ingresos'], 0, ',', '.') }}</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-danger">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-arrow-down me-2"></i>Total Egresos Semana
                    </h5>
                </div>
                <div class="card-body text-center">
                    <h2 class="text-danger">₲ {{ number_format($totalesSemana['egresos'], 0, ',', '.') }}</h2>
                    <small class="text-muted">Promedio diario: ₲ {{ number_format($promedios['egresos'], 0, ',', '.') }}</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-{{ $totalesSemana['saldo_neto'] >= 0 ? 'primary' : 'warning' }}">
                <div class="card-header bg-{{ $totalesSemana['saldo_neto'] >= 0 ? 'primary' : 'warning' }} text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-balance-scale me-2"></i>Saldo Neto Semana
                    </h5>
                </div>
                <div class="card-body text-center">
                    <h2 class="text-{{ $totalesSemana['saldo_neto'] >= 0 ? 'primary' : 'warning' }}">
                        ₲ {{ number_format($totalesSemana['saldo_neto'], 0, ',', '.') }}
                    </h2>
                    <small class="text-muted">Promedio diario: ₲ {{ number_format($promedios['saldo_neto'], 0, ',', '.') }}</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Resumen Diario -->
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-table me-1"></i>
            Detalle Diario de la Semana
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Día</th>
                            <th>Fecha</th>
                            <th class="text-end">Ingresos</th>
                            <th class="text-end">Egresos</th>
                            <th class="text-end">Saldo Neto</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($resumenSemanal as $dia)
                        <tr class="{{ $dia['saldo_neto'] > 0 ? 'bg-light-success' : ($dia['saldo_neto'] < 0 ? 'bg-light-danger' : '') }}">
                            <td><strong>{{ $dia['dia'] }}</strong></td>
                            <td>{{ \Carbon\Carbon::parse($dia['fecha'])->format('d/m/Y') }}</td>
                            <td class="text-end">₲ {{ number_format($dia['ingresos'], 0, ',', '.') }}</td>
                            <td class="text-end">₲ {{ number_format($dia['egresos'], 0, ',', '.') }}</td>
                            <td class="text-end fw-bold {{ $dia['saldo_neto'] >= 0 ? 'text-success' : 'text-danger' }}">
                                ₲ {{ number_format($dia['saldo_neto'], 0, ',', '.') }}
                            </td>
                            <td class="text-center">
                                <a href="{{ route('cierre-caja.index', ['fecha' => $dia['fecha']]) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye"></i> Ver Detalle
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-dark">
                        <tr>
                            <th colspan="2">TOTALES</th>
                            <th class="text-end">₲ {{ number_format($totalesSemana['ingresos'], 0, ',', '.') }}</th>
                            <th class="text-end">₲ {{ number_format($totalesSemana['egresos'], 0, ',', '.') }}</th>
                            <th class="text-end">₲ {{ number_format($totalesSemana['saldo_neto'], 0, ',', '.') }}</th>
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
                    <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Estadísticas de la Semana</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h6>Mejor día de la semana:</h6>
                        <p class="mb-1">
                            <strong>{{ $mejorDia['dia'] }} {{ \Carbon\Carbon::parse($mejorDia['fecha'])->format('d/m/Y') }}</strong>
                        </p>
                        <p class="text-success">
                            <i class="fas fa-arrow-up me-1"></i>
                            ₲ {{ number_format($mejorDia['saldo'], 0, ',', '.') }}
                        </p>
                    </div>
                    <div>
                        <h6>Peor día de la semana:</h6>
                        <p class="mb-1">
                            <strong>{{ $peorDia['dia'] }} {{ \Carbon\Carbon::parse($peorDia['fecha'])->format('d/m/Y') }}</strong>
                        </p>
                        <p class="text-danger">
                            <i class="fas fa-arrow-down me-1"></i>
                            ₲ {{ number_format($peorDia['saldo'], 0, ',', '.') }}
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
                        $totalIngresos = $totalesSemana['ingresos'];
                        $categorias = [
                            'señas_recibidas' => ['nombre' => 'Señas de Reservas', 'total' => 0],
                            'alquileres' => ['nombre' => 'Alquileres', 'total' => 0],
                            'multas_retraso' => ['nombre' => 'Multas', 'total' => 0],
                            'ventas' => ['nombre' => 'Ventas', 'total' => 0],
                            'ingresos_cancelaciones' => ['nombre' => 'Cancelaciones', 'total' => 0]
                        ];
                        
                        foreach ($resumenSemanal as $dia) {
                            foreach ($categorias as $key => $value) {
                                $categorias[$key]['total'] += $dia['desglose_ingresos'][$key];
                            }
                        }
                    @endphp
                    
                    @foreach($categorias as $key => $categoria)
                        @php
                            $porcentaje = $totalIngresos > 0 ? ($categoria['total'] / $totalIngresos) * 100 : 0;
                        @endphp
                        <div class="mb-2">
                            <div class="d-flex justify-content-between mb-1">
                                <span>{{ $categoria['nombre'] }}</span>
                                <span>{{ number_format($porcentaje, 1) }}%</span>
                            </div>
                            <div class="progress" style="height: 10px;">
                                <div class="progress-bar bg-success" role="progressbar" style="width: {{ $porcentaje }}%"
                                     aria-valuenow="{{ $porcentaje }}" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-seleccionar la fecha si no hay fecha seleccionada
    const fechaInput = document.querySelector('input[name="fecha"]');
    if (!fechaInput.value) {
        fechaInput.value = new Date().toISOString().split('T')[0];
    }
});
</script>
@endpush