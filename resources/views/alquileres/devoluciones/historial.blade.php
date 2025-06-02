{{-- Archivo: resources/views/alquileres/devoluciones/historial.blade.php --}}
@extends('template')

@section('title', 'Historial de Devoluciones')

@push('css')
    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" rel="stylesheet" type="text/css">
    <style>
        .ajuste-positivo { background-color: #d4edda; }
        .ajuste-negativo { background-color: #f8d7da; }
        .con-multa { background-color: #f8d7da; }
    </style>
@endpush

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4 text-center">Historial de Devoluciones</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item"><a href="{{ route('devoluciones.index') }}">Devoluciones</a></li>
        <li class="breadcrumb-item active">Historial</li>
    </ol>

    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-table me-2"></i> Historial Detallado de Devoluciones
        </div>
        <div class="card-body">
            <table id="datatablesSimple" class="table table-striped">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Cliente</th>
                        <th>Estado Devolución</th>
                        <th>Multa Calculada</th>
                        <th>Multa Aplicada</th>
                        <th>Monto Devuelto</th>
                        <th>Ajuste</th>
                        <th>Motivo</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($devoluciones as $devolucion)
                        @php
                            // Calcular días de diferencia (positivo = retraso, negativo = adelanto)
                            $fechaDevolucionReal = $devolucion->fecha_devolucion;
                            $fechaDevolucionProgramada = $devolucion->alquiler->fecha_fin;
                            $diasDiferencia = $fechaDevolucionReal->diffInDays($fechaDevolucionProgramada, false);
                            
                            // Si es negativo, significa que se devolvió después (retraso)
                            // Si es positivo, significa que se devolvió antes (adelanto)
                            $diasRetraso = $diasDiferencia < 0 ? abs($diasDiferencia) : 0;
                            $diasAdelanto = $diasDiferencia > 0 ? $diasDiferencia : 0;
                            
                            // Multa solo se calcula si hay retraso
                            $multaCalculada = $diasRetraso > 0 ? ($diasRetraso * 10000) : 0;
                            $multaAplicada = $devolucion->multa_aplicada_real ?? $devolucion->multa_aplicada ?? 0;
                            $montoDevuelto = $devolucion->monto_devuelto_real ?? $devolucion->monto_devuelto ?? 0;
                            $ajusteMulta = $multaCalculada - $multaAplicada;
                            
                            // Determinar clase CSS
                            $ajusteClase = '';
                            
                            // Marcar en rojo si tiene días de retraso O multa aplicada
                            if ($diasRetraso > 0 || $multaAplicada > 0) {
                                $ajusteClase = 'con-multa';
                            }
                            
                            // Si además tiene ajuste, aplicar el color específico del ajuste
                            if ($diasRetraso > 0 && $ajusteMulta > 0) {
                                $ajusteClase = 'ajuste-positivo';
                            } elseif ($diasRetraso > 0 && $ajusteMulta < 0) {
                                $ajusteClase = 'ajuste-negativo';
                            }
                        @endphp
                        <tr class="{{ $ajusteClase }}">
                            <td>{{ $devolucion->fecha_devolucion->format('d/m/Y') }}</td>
                            <td>{{ $devolucion->alquiler->cliente->nombre }}</td>
                            <td>
                                @if($diasAdelanto > 0)
                                    <span class="badge bg-info">
                                        <i class="fas fa-clock"></i> {{ $diasAdelanto }} día{{ $diasAdelanto > 1 ? 's' : '' }} antes
                                    </span>
                                @elseif($diasRetraso > 0)
                                    <span class="badge bg-danger">
                                        <i class="fas fa-exclamation-triangle"></i> {{ $diasRetraso }} día{{ $diasRetraso > 1 ? 's' : '' }} de retraso
                                    </span>
                                @else
                                    <span class="badge bg-success">
                                        <i class="fas fa-check"></i> A tiempo
                                    </span>
                                @endif
                            </td>
                            <td>
                                @if($multaCalculada > 0)
                                    ₲ {{ number_format($multaCalculada, 0, ',', '.') }}
                                @else
                                    <span class="text-muted">Sin multa</span>
                                @endif
                            </td>
                            <td>
                                @if($multaAplicada > 0)
                                    ₲ {{ number_format($multaAplicada, 0, ',', '.') }}
                                @else
                                    <span class="text-muted">₲ 0</span>
                                @endif
                            </td>
                            <td>₲ {{ number_format($montoDevuelto, 0, ',', '.') }}</td>
                            <td>
                                @if($diasRetraso > 0 && $ajusteMulta != 0)
                                    <span class="badge {{ $ajusteMulta > 0 ? 'bg-success' : 'bg-danger' }}">
                                        {{ $ajusteMulta > 0 ? '+' : '' }}₲ {{ number_format($ajusteMulta, 0, ',', '.') }}
                                    </span>
                                @else
                                    <span class="badge bg-secondary">Sin ajuste</span>
                                @endif
                            </td>
                            <td>
                                @if($devolucion->motivo_ajuste)
                                    <small class="text-muted">{{ ucfirst(str_replace('_', ' ', $devolucion->motivo_ajuste)) }}</small>
                                @elseif($diasAdelanto > 0)
                                    <small class="text-info">Devolución anticipada</small>
                                @else
                                    <small class="text-muted">-</small>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('devoluciones.comprobante', $devolucion->id) }}" 
                                   class="btn btn-sm btn-info" title="Ver comprobante">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @if($diasAdelanto > 0)
                                    <span class="badge bg-info ms-1" title="Devolución anticipada">
                                        <i class="fas fa-star"></i>
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Leyenda -->
    <div class="card">
        <div class="card-header">
            <h6><i class="fas fa-info-circle me-2"></i>Leyenda</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="d-flex align-items-center mb-2">
                        <div class="con-multa" style="width: 20px; height: 20px; margin-right: 10px; border: 1px solid #f5c6cb;"></div>
                        <span>Devolución con retraso o multa aplicada</span>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="d-flex align-items-center mb-2">
                        <div class="ajuste-positivo" style="width: 20px; height: 20px; margin-right: 10px; border: 1px solid #c3e6cb;"></div>
                        <span>Ajuste favorable al cliente (multa reducida)</span>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="d-flex align-items-center mb-2">
                        <div class="ajuste-negativo" style="width: 20px; height: 20px; margin-right: 10px; border: 1px solid #f5c6cb;"></div>
                        <span>Ajuste desfavorable al cliente (multa aumentada)</span>
                    </div>
                </div>
            </div>
            <hr>
            <div class="row">
                <div class="col-12">
                    <h6>Información adicional:</h6>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-info-circle text-info me-2"></i><strong>Devolución anticipada:</strong> Cliente devolvió antes de la fecha programada (sin penalización)</li>
                        <li><i class="fas fa-check text-success me-2"></i><strong>A tiempo:</strong> Cliente devolvió en la fecha exacta programada</li>
                        <li><i class="fas fa-exclamation-triangle text-danger me-2"></i><strong>Con retraso:</strong> Cliente devolvió después de la fecha programada (multa de ₲10.000 por día)</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/simple-datatables@latest" type="text/javascript"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const dataTable = new simpleDatatables.DataTable("#datatablesSimple", {
                searchable: true,
                sortable: true,
                perPage: 25,
                perPageSelect: [10, 25, 50, 100],
                labels: {
                    placeholder: "Buscar...",
                    perPage: "entradas por página",
                    noRows: "No se encontraron registros",
                    info: "Mostrando {start} a {end} de {rows} entradas"
                }
            });
        });
    </script>
@endpush