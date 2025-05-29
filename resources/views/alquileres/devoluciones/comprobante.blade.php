{{-- Archivo: resources/views/alquileres/devoluciones/comprobante.blade.php --}}
@extends('template')

@section('title', 'Comprobante de Devolución')

@push('css')
<style>
    .comprobante {
        max-width: 800px;
        margin: 0 auto;
        background: white;
        border: 2px solid #dee2e6;
        border-radius: 10px;
        padding: 30px;
    }
    .header-comprobante {
        text-align: center;
        border-bottom: 2px solid #007bff;
        padding-bottom: 20px;
        margin-bottom: 30px;
    }
    .info-section {
        margin-bottom: 25px;
    }
    .info-section h6 {
        color: #007bff;
        border-bottom: 1px solid #dee2e6;
        padding-bottom: 5px;
        margin-bottom: 15px;
    }
    .amount-highlight {
        background: #f8f9fa;
        border: 2px solid #28a745;
        border-radius: 8px;
        padding: 15px;
        text-align: center;
    }
    .amount-highlight.negative {
        border-color: #dc3545;
        background: #fff5f5;
    }
    .print-section {
        text-align: center;
        margin-top: 30px;
        border-top: 1px solid #dee2e6;
        padding-top: 20px;
    }
    @media print {
        .no-print {
            display: none !important;
        }
        .comprobante {
            border: none;
            box-shadow: none;
        }
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-4 no-print">
    <h1 class="mt-4 text-center">Comprobante de Devolución</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item"><a href="{{ route('devoluciones.index') }}">Devoluciones</a></li>
        <li class="breadcrumb-item active">Comprobante</li>
    </ol>
</div>

<div class="comprobante">
    <div class="header-comprobante">
        <h2>COMPROBANTE DE DEVOLUCIÓN</h2>
        <p class="mb-0">Nº {{ str_pad($devolucion->id, 6, '0', STR_PAD_LEFT) }}</p>
        <p class="text-muted">{{ $devolucion->fecha_devolucion->format('d/m/Y H:i') }}</p>
    </div>

    <div class="info-section">
        <h6><i class="fas fa-user me-2"></i>Información del Cliente</h6>
        <div class="row">
            <div class="col-md-6">
                <p><strong>Nombre:</strong> {{ $devolucion->alquiler->cliente->nombre }}</p>
                <p><strong>Teléfono:</strong> {{ $devolucion->alquiler->cliente->telefono ?? 'No registrado' }}</p>
            </div>
            <div class="col-md-6">
                <p><strong>Cedula:</strong> {{ $devolucion->alquiler->cliente->correo ?? 'No registrado' }}</p>
                <p><strong>Dirección:</strong> {{ $devolucion->alquiler->cliente->direccion ?? 'No registrada' }}</p>
            </div>
        </div>
    </div>

    <div class="info-section">
        <h6><i class="fas fa-calendar me-2"></i>Información del Alquiler</h6>
        <div class="row">
            <div class="col-md-6">
                <p><strong>Fecha de Inicio:</strong> {{ $devolucion->alquiler->fecha_inicio->format('d/m/Y') }}</p>
                <p><strong>Fecha de Fin:</strong> {{ $devolucion->alquiler->fecha_fin->format('d/m/Y') }}</p>
            </div>
            <div class="col-md-6">
                <p><strong>Fecha de Devolución:</strong> {{ $devolucion->fecha_devolucion->format('d/m/Y') }}</p>
                <p><strong>Días de Retraso:</strong> {{ $devolucion->dias_retraso }}</p>
            </div>
        </div>
    </div>

    <div class="info-section">
        <h6><i class="fas fa-tshirt me-2"></i>Prendas Devueltas</h6>
        <div class="table-responsive">
            <table class="table table-sm">
                <thead class="table-light">
                    <tr>
                        <th>Prenda</th>
                        <th>Categoría</th>
                        <th>Talle</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($devolucion->alquiler->stockItems as $prenda)
                    <tr>
                        <td>{{ $prenda->nombre }}</td>
                        <td>{{ $prenda->codigo }}</td>
                        <td>{{ $prenda->talle ?? 'N/A' }}</td>
                        <td><span class="badge bg-success">Devuelto</span></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="info-section">
        <h6><i class="fas fa-calculator me-2"></i>Detalle de Cálculos</h6>
        <div class="table-responsive">
            <table class="table table-bordered">
                <tbody>
                    <tr>
                        <td><strong>Garantía Original:</strong></td>
                        <td class="text-end">₲ {{ number_format($devolucion->garantia_original, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td><strong>Días de Retraso:</strong></td>
                        <td class="text-end">{{ $devolucion->dias_retraso }} días</td>
                    </tr>
                    <tr>
                        <td><strong>Multa Aplicada:</strong></td>
                        <td class="text-end text-danger">- ₲ {{ number_format($devolucion->multa_aplicada, 0, ',', '.') }}</td>
                    </tr>
                    <tr class="table-active">
                        <td><strong>MONTO A DEVOLVER:</strong></td>
                        <td class="text-end">
                            <strong class="{{ $devolucion->monto_devuelto > 0 ? 'text-success' : 'text-danger' }}">
                                ₲ {{ number_format($devolucion->monto_devuelto, 0, ',', '.') }}
                            </strong>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    @if($devolucion->monto_devuelto > 0)
        <div class="amount-highlight">
            <h4 class="text-success mb-0">MONTO A DEVOLVER AL CLIENTE</h4>
            <h2 class="text-success">₲ {{ number_format($devolucion->monto_devuelto, 0, ',', '.') }}</h2>
        </div>
    @elseif($devolucion->monto_devuelto == 0)
        <div class="amount-highlight">
            <h4 class="text-warning mb-0">NO HAY MONTO A DEVOLVER</h4>
            <p class="mb-0">La multa cubre exactamente la garantía depositada</p>
        </div>
    @else
        <div class="amount-highlight negative">
            <h4 class="text-danger mb-0">MONTO ADICIONAL A COBRAR</h4>
            <h2 class="text-danger">₲ {{ number_format(abs($devolucion->monto_devuelto), 0, ',', '.') }}</h2>
        </div>
    @endif

    @if($devolucion->observaciones)
        <div class="info-section">
            <h6><i class="fas fa-sticky-note me-2"></i>Observaciones</h6>
            <p class="text-muted">{{ $devolucion->observaciones }}</p>
        </div>
    @endif

    <div class="print-section">
        <p class="text-muted small">
            Comprobante generado el {{ now()->format('d/m/Y H:i:s') }}<br>
            Sistema de Gestión de Alquileres
        </p>
    </div>
</div>

<div class="text-center mt-4 no-print">
    <button onclick="window.print()" class="btn btn-primary me-2">
        <i class="fas fa-print me-2"></i>Imprimir
    </button>
    <a href="{{ route('devoluciones.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-2"></i>Volver a Devoluciones
    </a>
    <a href="{{ route('devoluciones.historial') }}" class="btn btn-info">
        <i class="fas fa-history me-2"></i>Ver Historial
    </a>
</div>

@endsection