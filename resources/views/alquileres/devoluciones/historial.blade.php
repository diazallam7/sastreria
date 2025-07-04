{{-- Archivo: resources/views/alquileres/devoluciones/historial.blade.php --}}
@extends('template')

@section('title', 'Historial de Devoluciones')

@push('css')
    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" rel="stylesheet" type="text/css">
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
                        <th>ID</th>
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
                        @endphp
                        <tr>
                            <td>{{ $devolucion->id }}</td>
                            <td>{{ $devolucion->fecha_devolucion->format('d/m/Y') }}</td>
                            <td>{{ $devolucion->alquiler->cliente->nombre }}</td>
                            <td>
                                @if($diasAdelanto > 0)
                                    {{ $diasAdelanto }} día{{ $diasAdelanto > 1 ? 's' : '' }} antes
                                @elseif($diasRetraso > 0)
                                    {{ $diasRetraso }} día{{ $diasRetraso > 1 ? 's' : '' }} de retraso
                                @else
                                    A tiempo
                                @endif
                            </td>
                            <td>
                                @if($multaCalculada > 0)
                                    ₲ {{ number_format($multaCalculada, 0, ',', '.') }}
                                @else
                                    Sin multa
                                @endif
                            </td>
                            <td>
                                @if($multaAplicada > 0)
                                    ₲ {{ number_format($multaAplicada, 0, ',', '.') }}
                                @else
                                    ₲ 0
                                @endif
                            </td>
                            <td>₲ {{ number_format($montoDevuelto, 0, ',', '.') }}</td>
                            <td>
                                @if($diasRetraso > 0 && $ajusteMulta != 0)
                                    {{ $ajusteMulta > 0 ? '+' : '' }}₲ {{ number_format($ajusteMulta, 0, ',', '.') }}
                                @else
                                    Sin ajuste
                                @endif
                            </td>
                            <td>
                                @if($devolucion->motivo_ajuste)
                                    {{ ucfirst(str_replace('_', ' ', $devolucion->motivo_ajuste)) }}
                                @elseif($diasAdelanto > 0)
                                    Devolución anticipada
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('devoluciones.comprobante', $devolucion->id) }}" 
                                   class="btn btn-sm btn-info" title="Ver comprobante">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Información -->
    <div class="card">
        <div class="card-header">
            <h6><i class="fas fa-info-circle me-2"></i>Información</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-12">
                    <ul class="list-unstyled">
                        <li><strong>Devolución anticipada:</strong> Cliente devolvió antes de la fecha programada (sin penalización)</li>
                        <li><strong>A tiempo:</strong> Cliente devolvió en la fecha exacta programada</li>
                        <li><strong>Con retraso:</strong> Cliente devolvió después de la fecha programada (multa de ₲10.000 por día)</li>
                        <li><strong>Ajuste:</strong> Diferencia entre la multa calculada automáticamente y la multa realmente aplicada</li>
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
