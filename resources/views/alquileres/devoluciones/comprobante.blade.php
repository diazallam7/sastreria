{{-- Archivo: resources/views/alquileres/devoluciones/comprobante.blade.php --}}
@extends('template')

@section('title', 'Comprobante de Devolución')

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4 text-center">Comprobante de Devolución</h1>
    
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-receipt me-2"></i>Devolución Procesada</h5>
                </div>
                <div class="card-body">
                    <!-- Información del cliente y alquiler -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6>Información del Cliente</h6>
                            <p><strong>Cliente:</strong> {{ $devolucion->alquiler->cliente->nombre }}</p>
                            <p><strong>Fecha de devolución:</strong> {{ $devolucion->fecha_devolucion->format('d/m/Y') }}</p>
                            <p><strong>Días de retraso:</strong> {{ $devolucion->dias_retraso }}</p>
                        </div>
                        <div class="col-md-6">
                            <h6>Prendas Devueltas</h6>
                            <ul class="list-unstyled">
                                @foreach($devolucion->alquiler->stockItems as $prenda)
                                    <li>• {{ $prenda->nombre }} ({{ $prenda->codigo }})</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>

                    <!-- Detalle financiero -->
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Concepto</th>
                                    <th>Monto Calculado</th>
                                    <th>Monto Aplicado</th>
                                    <th>Diferencia</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>Garantía Original</strong></td>
                                    <td colspan="3" class="text-center">₲ {{ number_format($devolucion->garantia_original, 0, ',', '.') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Multa por Retraso</strong></td>
                                    <td>₲ {{ number_format($devolucion->multa_calculada ?? $devolucion->multa, 0, ',', '.') }}</td>
                                    <td>₲ {{ number_format($devolucion->multa_aplicada_real ?? $devolucion->multa_aplicada, 0, ',', '.') }}</td>
                                    <td class="{{ ($devolucion->multa_calculada ?? $devolucion->multa) > ($devolucion->multa_aplicada_real ?? $devolucion->multa_aplicada) ? 'text-success' : 'text-danger' }}">
                                        ₲ {{ number_format(($devolucion->multa_calculada ?? $devolucion->multa) - ($devolucion->multa_aplicada_real ?? $devolucion->multa_aplicada), 0, ',', '.') }}
                                    </td>
                                </tr>
                                <tr class="table-success">
                                    <td><strong>Monto Devuelto</strong></td>
                                    <td>₲ {{ number_format($devolucion->monto_devuelto, 0, ',', '.') }}</td>
                                    <td><strong>₲ {{ number_format($devolucion->monto_devuelto_real ?? $devolucion->monto_devuelto, 0, ',', '.') }}</strong></td>
                                    <td class="{{ ($devolucion->monto_devuelto_real ?? $devolucion->monto_devuelto) > $devolucion->monto_devuelto ? 'text-success' : 'text-danger' }}">
                                        ₲ {{ number_format(($devolucion->monto_devuelto_real ?? $devolucion->monto_devuelto) - $devolucion->monto_devuelto, 0, ',', '.') }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    @if($devolucion->motivo_ajuste)
                        <div class="alert alert-info">
                            <h6><i class="fas fa-info-circle me-2"></i>Motivo del Ajuste</h6>
                            <p class="mb-0">
                                <strong>{{ ucfirst(str_replace('_', ' ', $devolucion->motivo_ajuste)) }}</strong>
                                @if($devolucion->observaciones)
                                    <br>{{ $devolucion->observaciones }}
                                @endif
                            </p>
                        </div>
                    @endif

                    @if($devolucion->observaciones && !$devolucion->motivo_ajuste)
                        <div class="alert alert-secondary">
                            <h6><i class="fas fa-comment me-2"></i>Observaciones</h6>
                            <p class="mb-0">{{ $devolucion->observaciones }}</p>
                        </div>
                    @endif

                    <div class="text-center mt-4">
                        <a href="{{ route('devoluciones.index') }}" class="btn btn-primary me-2">
                            <i class="fas fa-arrow-left me-2"></i>Volver a Devoluciones
                        </a>
                        <button onclick="window.print()" class="btn btn-secondary">
                            <i class="fas fa-print me-2"></i>Imprimir
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection