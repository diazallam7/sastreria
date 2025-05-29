{{-- Archivo: resources/views/alquileres/devoluciones/multas.blade.php --}}
@extends('template')

@section('title', 'Cálculo de Devolución')

@push('css')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
    .calculation-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 15px;
        padding: 20px;
        margin-bottom: 20px;
    }
    .result-card {
        border: 2px solid #28a745;
        border-radius: 10px;
        background: #f8fff9;
    }
    .warning-card {
        border: 2px solid #ffc107;
        border-radius: 10px;
        background: #fffdf0;
    }
    .danger-card {
        border: 2px solid #dc3545;
        border-radius: 10px;
        background: #fff5f5;
    }
    .amount-display {
        font-size: 1.5rem;
        font-weight: bold;
    }
    .date-comparison {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 15px;
        margin: 15px 0;
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4 text-center">Cálculo de Devolución</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item"><a href="{{ route('devoluciones.index') }}">Devoluciones</a></li>
        <li class="breadcrumb-item active">Cálculo de Devolución</li>
    </ol>

    <div class="row justify-content-center">
        <div class="col-md-10">
            <!-- Información del Alquiler -->
            <div class="calculation-card">
                <h5><i class="fas fa-info-circle me-2"></i>Información del Alquiler</h5>
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Cliente:</strong> {{ $alquiler->cliente->nombre }}</p>
                        <p><strong>Garantía Depositada:</strong> ₲ {{ number_format($garantiaOriginal, 0, ',', '.') }}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Prendas Alquiladas:</strong></p>
                        <ul class="mb-0">
                            @foreach($alquiler->stockItems as $prenda)
                                <li>{{ $prenda->nombre }} ({{ $prenda->codigo }})</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Comparación de Fechas -->
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-calendar-alt me-2"></i>Comparación de Fechas</h5>
                </div>
                <div class="card-body">
                    <div class="date-comparison">
                        <div class="row text-center">
                            <div class="col-md-4">
                                <h6>Fecha de Vencimiento</h6>
                                <div class="badge bg-primary fs-6 p-2">{{ $fechaFin }}</div>
                            </div>
                            <div class="col-md-4">
                                <h6>Fecha de Devolución</h6>
                                <div class="badge bg-secondary fs-6 p-2">{{ $fechaActual }}</div>
                            </div>
                            <div class="col-md-4">
                                <h6>Días de Diferencia</h6>
                                <div class="badge {{ $diasRetraso > 0 ? 'bg-danger' : 'bg-success' }} fs-6 p-2">
                                    {{ $diasRetraso }} días
                                </div>
                            </div>
                        </div>
                        
                        @if($diasRetraso > 0)
                            <div class="alert alert-warning mt-3 mb-0">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <strong>Retraso detectado:</strong> El cliente se retrasó {{ $diasRetraso }} día{{ $diasRetraso > 1 ? 's' : '' }} en la devolución.
                            </div>
                        @else
                            <div class="alert alert-success mt-3 mb-0">
                                <i class="fas fa-check-circle me-2"></i>
                                <strong>Devolución a tiempo:</strong> No hay retraso en la devolución.
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Cálculo de Multas -->
            <div class="card mb-4">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="fas fa-calculator me-2"></i>Cálculo de Multas</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Concepto</th>
                                    <th>Cantidad</th>
                                    <th>Valor por Día</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>Días de Retraso</strong></td>
                                    <td class="text-center">
                                        <span class="badge {{ $diasRetraso > 0 ? 'bg-danger' : 'bg-success' }} fs-6">
                                            {{ $diasRetraso }}
                                        </span>
                                    </td>
                                    <td>₲ {{ number_format($multaDiaria, 0, ',', '.') }}</td>
                                    <td class="fw-bold {{ $multaTotal > 0 ? 'text-danger' : 'text-success' }}">
                                        ₲ {{ number_format($multaTotal, 0, ',', '.') }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    @if($diasRetraso > 0)
                        <div class="alert alert-info">
                            <strong>Cálculo:</strong> {{ $diasRetraso }} día{{ $diasRetraso > 1 ? 's' : '' }} × ₲ {{ number_format($multaDiaria, 0, ',', '.') }} = ₲ {{ number_format($multaTotal, 0, ',', '.') }}
                        </div>
                    @endif
                </div>
            </div>

            <!-- Resumen de Devolución -->
            <div class="card mb-4 {{ $montoDevolver > 0 ? 'result-card' : ($montoDevolver == 0 ? 'warning-card' : 'danger-card') }}">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-money-bill-wave me-2"></i>Resumen de Devolución
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 text-center">
                            <h6>Garantía Original</h6>
                            <div class="amount-display text-primary">
                                ₲ {{ number_format($garantiaOriginal, 0, ',', '.') }}
                            </div>
                        </div>
                        <div class="col-md-4 text-center">
                            <h6>Multa Aplicada</h6>
                            <div class="amount-display text-danger">
                                 ₲ {{ number_format($multaTotal, 0, ',', '.') }}
                            </div>
                        </div>
                        <div class="col-md-4 text-center">
                            <h6>Monto a Devolver</h6>
                            <div class="amount-display {{ $montoDevolver > 0 ? 'text-success' : 'text-danger' }}">
                                ₲ {{ number_format($montoDevolver, 0, ',', '.') }}
                            </div>
                        </div>
                    </div>
                    
                    @if($montoDevolver <= 0)
                        <div class="alert alert-warning mt-3">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Atención:</strong> La multa es igual o superior a la garantía. 
                            @if($multaTotal > $garantiaOriginal)
                                El cliente debe pagar un adicional de ₲ {{ number_format($multaTotal - $garantiaOriginal, 0, ',', '.') }}.
                            @else
                                No se devuelve dinero al cliente.
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            <!-- Formulario para procesar devolución -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-clipboard-check me-2"></i>Procesar Devolución</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('devoluciones.procesar', $alquiler->id) }}" method="POST" id="devolucionForm">
                        @csrf
                        
                        <div class="mb-3">
                            <label for="observaciones" class="form-label">Observaciones</label>
                            <textarea class="form-control" id="observaciones" name="observaciones" rows="3" 
                                placeholder="Ingrese cualquier observación sobre la devolución (opcional)"></textarea>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('devoluciones.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Volver
                            </a>
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fas fa-check me-2"></i>Confirmar Devolución
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('devolucionForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const montoDevolver = {{ $montoDevolver }};
        const multaTotal = {{ $multaTotal }};
        const garantiaOriginal = {{ $garantiaOriginal }};
        const diasRetraso = {{ $diasRetraso }};
        
        let mensaje = '¿Confirma procesar esta devolución?';
        let detalles = '';
        
        if (diasRetraso === 0) {
            detalles = 'Devolución a tiempo. Se devuelve la garantía completa.';
        } else if (montoDevolver > 0) {
            detalles = `Retraso de ${diasRetraso} día${diasRetraso > 1 ? 's' : ''}. Se devolverá ₲ ${montoDevolver.toLocaleString()} al cliente.`;
        } else if (montoDevolver === 0) {
            detalles = `Retraso de ${diasRetraso} día${diasRetraso > 1 ? 's' : ''}. No se devolverá dinero (multa igual a garantía).`;
        } else {
            const adicional = multaTotal - garantiaOriginal;
            detalles = `Retraso de ${diasRetraso} día${diasRetraso > 1 ? 's' : ''}. El cliente debe pagar un adicional de ₲ ${adicional.toLocaleString()}.`;
        }
        
        Swal.fire({
            title: 'Confirmar Devolución',
            html: `${mensaje}<br><br><strong>${detalles}</strong>`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, procesar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                this.submit();
            }
        });
    });
});
</script>
@endpush