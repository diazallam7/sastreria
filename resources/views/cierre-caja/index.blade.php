@extends('template')

@section('title', 'Cierre de Caja')

@push('css')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
@endpush

@section('content')
    <div class="container-fluid px-4">
        <h1 class="mt-4 text-center">
            <i class="fas fa-cash-register me-2"></i>Cierre de Caja
        </h1>

        <ol class="breadcrumb mb-4">
            <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
            <li class="breadcrumb-item active">Cierre de Caja</li>
        </ol>

        <!-- Selector de Fecha -->
        <div class="row mb-4">
            <div class="col-md-6">
                <form action="{{ route('cierre-caja.consultar') }}" method="POST" class="d-flex">
                    @csrf
                    <input type="date" name="fecha" class="form-control me-2" value="{{ $fecha ?? $fechaHoy }}"
                        max="{{ now()->format('Y-m-d') }}">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search me-1"></i>Consultar
                    </button>
                </form>
            </div>
            <div class="col-md-6 text-end">
                <div class="btn-group">
                    <a href="{{ route('cierre-caja.semanal') }}" class="btn btn-outline-info">
                        <i class="fas fa-calendar-week me-1"></i>Resumen Semanal
                    </a>
                    <a href="{{ route('cierre-caja.mensual') }}" class="btn btn-outline-info">
                        <i class="fas fa-calendar-alt me-1"></i>Resumen Mensual
                    </a>
                    <a href="{{ route('cierre-caja.pdf', ['fecha' => $fecha ?? $fechaHoy]) }}" class="btn btn-danger"
                        target="_blank">
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
                            <i class="fas fa-arrow-up me-2"></i>Total Ingresos
                        </h5>
                    </div>
                    <div class="card-body text-center">
                        <h2 class="text-success">₲ {{ number_format($movimientos['ingresos']['total'], 0, ',', '.') }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-danger">
                    <div class="card-header bg-danger text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-arrow-down me-2"></i>Total Egresos
                        </h5>
                    </div>
                    <div class="card-body text-center">
                        <h2 class="text-danger">₲ {{ number_format($movimientos['egresos']['total'], 0, ',', '.') }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-{{ $movimientos['saldo_neto'] >= 0 ? 'primary' : 'warning' }}">
                    <div class="card-header bg-{{ $movimientos['saldo_neto'] >= 0 ? 'primary' : 'warning' }} text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-balance-scale me-2"></i>Saldo Neto
                        </h5>
                    </div>
                    <div class="card-body text-center">
                        <h2 class="text-{{ $movimientos['saldo_neto'] >= 0 ? 'primary' : 'warning' }}">
                            ₲ {{ number_format($movimientos['saldo_neto'], 0, ',', '.') }}
                        </h2>
                    </div>
                </div>
            </div>
        </div>

        <!-- Desglose de Ingresos -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-plus-circle me-2"></i>Desglose de Ingresos
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-2">
                            <div class="col-8">Alquileres Iniciados:</div>
                            <div class="col-4 text-end">₲
                                {{ number_format($movimientos['ingresos']['alquileres'], 0, ',', '.') }}</div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-8">Multas por Retraso:</div>
                            <div class="col-4 text-end">₲
                                {{ number_format($movimientos['ingresos']['multas_retraso'], 0, ',', '.') }}</div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-8">Ventas:</div>
                            <div class="col-4 text-end">₲
                                {{ number_format($movimientos['ingresos']['ventas'], 0, ',', '.') }}</div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-8">Ingresos por Cancelaciones:</div>
                            <div class="col-4 text-end">₲
                                {{ number_format($movimientos['ingresos']['ingresos_cancelaciones'], 0, ',', '.') }}</div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-8"><strong>Total Ingresos:</strong></div>
                            <div class="col-4 text-end"><strong>₲
                                    {{ number_format($movimientos['ingresos']['total'], 0, ',', '.') }}</strong></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Desglose de Egresos -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-danger text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-minus-circle me-2"></i>Desglose de Egresos
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-2">
                            <div class="col-8">Compras:</div>
                            <div class="col-4 text-end">₲
                                {{ number_format($movimientos['egresos']['compras'], 0, ',', '.') }}</div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-8">Gastos Varios:</div>
                            <div class="col-4 text-end">₲
                                {{ number_format($movimientos['egresos']['gastos_varios'], 0, ',', '.') }}</div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-8"><strong>Total Egresos:</strong></div>
                            <div class="col-4 text-end"><strong>₲
                                    {{ number_format($movimientos['egresos']['total'], 0, ',', '.') }}</strong></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detalles por Categoría -->
        <div class="row">
            <!-- Reservas del Día -->
            @if (count($movimientos['detalles']['reservas']) > 0)
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="fas fa-calendar-plus me-2"></i>Reservas del Día
                                ({{ count($movimientos['detalles']['reservas']) }})
                            </h6>
                        </div>
                        <div class="card-body" style="max-height: 300px; overflow-y: auto;">
                            @foreach ($movimientos['detalles']['reservas'] as $reserva)
                                <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                                    <div>
                                        <strong>{{ $reserva->cliente->nombre }}</strong><br>
                                        <small class="text-muted">Reserva #{{ $reserva->id }}</small>
                                    </div>
                                    <div class="text-end">
                                        <span
                                            class="badge bg-{{ $reserva->estado === 'confirmada' ? 'success' : ($reserva->estado === 'cancelada' ? 'danger' : 'primary') }}">
                                            {{ ucfirst($reserva->estado) }}
                                        </span><br>
                                        <strong>₲
                                            {{ number_format($reserva->seña_alquiler + $reserva->seña_garantia, 0, ',', '.') }}</strong>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <!-- Cancelaciones del Día -->
            @if (count($movimientos['detalles']['cancelaciones']) > 0)
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="fas fa-times-circle me-2"></i>Cancelaciones del Día
                                ({{ count($movimientos['detalles']['cancelaciones']) }})
                            </h6>
                        </div>
                        <div class="card-body" style="max-height: 300px; overflow-y: auto;">
                            @foreach ($movimientos['detalles']['cancelaciones'] as $cancelacion)
                                @php
                                    $totalRecibido = $cancelacion->seña_alquiler + $cancelacion->seña_garantia;
                                    $devuelto = $cancelacion->seña_devuelta ?? 0;
                                    $ingresoNeto = $totalRecibido - $devuelto;
                                @endphp
                                <div class="border-bottom py-2">
                                    <div class="d-flex justify-content-between">
                                        <strong>{{ $cancelacion->cliente->nombre }}</strong>
                                        <span class="badge bg-danger">Cancelada</span>
                                    </div>
                                    <small class="text-muted">Reserva #{{ $cancelacion->id }}</small>
                                    <div class="row mt-1">
                                        <div class="col-4 text-center">
                                            <small>Recibido</small><br>
                                            <strong>₲ {{ number_format($totalRecibido, 0, ',', '.') }}</strong>
                                        </div>
                                        <div class="col-4 text-center">
                                            <small>Devuelto</small><br>
                                            <strong>₲ {{ number_format($devuelto, 0, ',', '.') }}</strong>
                                        </div>
                                        <div class="col-4 text-center">
                                            <small>Neto</small><br>
                                            <strong class="text-{{ $ingresoNeto > 0 ? 'success' : 'secondary' }}">
                                                ₲ {{ number_format($ingresoNeto, 0, ',', '.') }}
                                            </strong>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <!-- Alquileres Iniciados -->
            @if (count($movimientos['detalles']['alquileres_iniciados']) > 0)
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="fas fa-play me-2"></i>Alquileres Iniciados
                                ({{ count($movimientos['detalles']['alquileres_iniciados']) }})
                            </h6>
                        </div>
                        <div class="card-body" style="max-height: 300px; overflow-y: auto;">
                            @foreach ($movimientos['detalles']['alquileres_iniciados'] as $alquiler)
                                <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                                    <div>
                                        <strong>{{ $alquiler->cliente->nombre ?? 'Cliente' }}</strong><br>
                                        <small class="text-muted">Alquiler #{{ $alquiler->id }}</small>
                                    </div>
                                    <div class="text-end">
                                        <strong>₲ {{ number_format($alquiler->costo_total, 0, ',', '.') }}</strong>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <!-- Devoluciones del Día -->
            @if (count($movimientos['detalles']['devoluciones']) > 0)
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="fas fa-undo me-2"></i>Devoluciones del Día
                                ({{ count($movimientos['detalles']['devoluciones']) }})
                            </h6>
                        </div>
                        <div class="card-body" style="max-height: 300px; overflow-y: auto;">
                            @foreach ($movimientos['detalles']['devoluciones'] as $devolucion)
                                <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                                    <div>
                                        <strong>{{ $devolucion->alquiler->cliente->nombre ?? 'Cliente' }}</strong><br>
                                        <small class="text-muted">Devolución #{{ $devolucion->id }}</small>
                                    </div>
                                    <div class="text-end">
                                        @if (isset($devolucion->multa) && $devolucion->multa > 0)
                                            <span class="badge bg-warning">Multa: ₲
                                                {{ number_format($devolucion->multa, 0, ',', '.') }}</span><br>
                                        @endif
                                        <strong>₲
                                            {{ number_format($devolucion->monto_devuelto_real ?? ($devolucion->monto_devuelto ?? 0), 0, ',', '.') }}</strong>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <!-- Ventas del Día -->
            @if (count($movimientos['detalles']['ventas']) > 0)
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="fas fa-shopping-cart me-2"></i>Ventas del Día
                                ({{ count($movimientos['detalles']['ventas']) }})
                            </h6>
                        </div>
                        <div class="card-body" style="max-height: 300px; overflow-y: auto;">
                            @foreach ($movimientos['detalles']['ventas'] as $venta)
                                <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                                    <div>
                                        <strong>{{ $venta->compra->nombre_producto ?? 'Producto' }}</strong><br>
                                        <small class="text-muted">Venta #{{ $venta->id }}</small>
                                    </div>
                                    <div class="text-end">
                                        <strong>₲ {{ number_format($venta->precio_total, 0, ',', '.') }}</strong>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
            <!-- Compras del Día -->
            @if (count($movimientos['detalles']['compras']) > 0)
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="fas fa-shopping-bag me-2"></i>Compras del Día
                                ({{ count($movimientos['detalles']['compras']) }})
                            </h6>
                        </div>
                        <div class="card-body" style="max-height: 300px; overflow-y: auto;">
                            @foreach ($movimientos['detalles']['compras'] as $compra)
                                <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                                    <div>
                                        <strong>{{ $compra->nombre_producto }}</strong><br>
                                        <small class="text-muted">
                                            {{ $compra->cantidad_total_calculada ?? $compra->cantidad_total }} unidades
                                            @if(isset($compra->precio_total_calculado))
                                                - Precio unitario: ₲{{ number_format($compra->precio_compra, 0, ',', '.') }}
                                            @endif
                                        </small> <br>
                                        <small class="text-muted">Compra #{{ $compra->id }}</small>
                                    </div>
                                    <div class="text-end">
                                        <strong>₲ {{ number_format($compra->precio_total_calculado ?? $compra->precio_compra, 0, ',', '.') }}</strong>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <!-- Gastos del Día -->
            @if (count($movimientos['detalles']['gastos']) > 0)
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="fas fa-receipt me-2"></i>Gastos Varios del Día
                                ({{ count($movimientos['detalles']['gastos']) }})
                            </h6>
                        </div>
                        <div class="card-body" style="max-height: 300px; overflow-y: auto;">
                            @foreach ($movimientos['detalles']['gastos'] as $gasto)
                                <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                                    <div>
                                        <strong>{{ $gasto->nombre_gasto }}</strong><br>
                                        @if ($gasto->observacion)
                                            <small class="text-muted">{{ $gasto->observacion }}</small>
                                        @endif
                                    </div>
                                    <div class="text-end">
                                        <strong>₲ {{ number_format($gasto->monto, 0, ',', '.') }}</strong>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection

@push('js')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-seleccionar la fecha de hoy si no hay fecha seleccionada
            const fechaInput = document.querySelector('input[name="fecha"]');
            if (!fechaInput.value) {
                fechaInput.value = new Date().toISOString().split('T')[0];
            }
        });
    </script>
@endpush
