@extends('template')

@section('title', 'Detalles del Alquiler')

@push('css')
<style>
    .info-box {
        background-color: #f8f9fa;
        border-left: 4px solid #0d6efd;
        padding: 15px;
        margin-bottom: 20px;
    }
    .info-label {
        font-weight: bold;
        color: #495057;
    }
    .info-value {
        color: #212529;
    }
    .badge-lg {
        font-size: 0.9rem;
        padding: 0.5rem 0.7rem;
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4 text-center">Detalles del Alquiler</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item"><a href="{{ route('alquileres.index') }}">Alquileres</a></li>
        <li class="breadcrumb-item active">Detalles del Alquiler #{{ $alquiler->id }}</li>
    </ol>

    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle me-2"></i>Información del Alquiler
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-box">
                                <div class="row mb-2">
                                    <div class="col-sm-4 info-label">Cliente:</div>
                                    <div class="col-sm-8 info-value">{{ $alquiler->cliente->nombre }}</div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-sm-4 info-label">Teléfono:</div>
                                    <div class="col-sm-8 info-value">{{ $alquiler->cliente->telefono ?? 'No disponible' }}</div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-sm-4 info-label">Cedula:</div>
                                    <div class="col-sm-8 info-value">{{ $alquiler->cliente->correo ?? 'No disponible' }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-box">
                                <div class="row mb-2">
                                    <div class="col-sm-4 info-label">Estado:</div>
                                    <div class="col-sm-8">
                                        @if($alquiler->estado == 'activo')
                                            <span class="badge bg-success badge-lg">Activo</span>
                                        @elseif($alquiler->estado == 'completado')
                                            <span class="badge bg-primary badge-lg">Completado</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-sm-4 info-label">Fecha de Creación:</div>
                                    <div class="col-sm-8 info-value">{{ \Carbon\Carbon::parse($alquiler->created_at)->format('d/m/Y H:i') }}</div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-sm-4 info-label">Última Actualización:</div>
                                    <div class="col-sm-8 info-value">{{ \Carbon\Carbon::parse($alquiler->updated_at)->format('d/m/Y H:i') }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-6">
                            <div class="info-box">
                                <div class="row mb-2">
                                    <div class="col-sm-4 info-label">Fecha de Inicio:</div>
                                    <div class="col-sm-8 info-value">{{ \Carbon\Carbon::parse($alquiler->fecha_inicio)->format('d/m/Y') }}</div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-sm-4 info-label">Fecha de Fin:</div>
                                    <div class="col-sm-8 info-value">{{ \Carbon\Carbon::parse($alquiler->fecha_fin)->format('d/m/Y') }}</div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-sm-4 info-label">Duración:</div>
                                    <div class="col-sm-8 info-value">{{ $alquiler->dias }} días</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-box">
                                <div class="row mb-2">
                                    <div class="col-sm-4 info-label">Costo Total:</div>
                                    <div class="col-sm-8 info-value">₲ {{ number_format($alquiler->costo_total, 0, ',', '.') }}</div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-sm-4 info-label">Garantía:</div>
                                    <div class="col-sm-8 info-value">₲ {{ number_format($alquiler->garantia, 0, ',', '.') }}</div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-sm-4 info-label">Total a Pagar:</div>
                                    <div class="col-sm-8 info-value fw-bold">₲ {{ number_format($alquiler->costo_total + $alquiler->garantia, 0, ',', '.') }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Prendas -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">
                <i class="fas fa-tshirt me-2"></i>Prendas del Alquiler
            </h5>
        </div>
        <div class="card-body table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Prenda</th>
                        <th>Descripción</th>
                        <th>Talle</th>
                        <th>Cantidad</th>
                        <th>Precio por Día</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($alquiler->stockItems as $item)
                        @php
                            $talle = $item->talles->where('id', $item->pivot->talle_id)->first();
                            $subtotal = $item->pivot->cantidad * $item->precio_alquiler * $alquiler->dias;
                        @endphp
                        <tr>
                            <td>{{ $item->nombre_del_producto }}</td>
                            <td>{{ Str::limit($item->descripcion, 50) }}</td>
                            <td>{{ $talle ? $talle->talle : 'N/A' }}</td>
                            <td>{{ $item->pivot->cantidad }}</td>
                            <td>₲ {{ number_format($item->precio_alquiler, 0, ',', '.') }}</td>
                            <td>₲ {{ number_format($subtotal, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="table-dark">
                    <tr>
                        <th colspan="5" class="text-end">Total:</th>
                        <th>₲ {{ number_format($alquiler->costo_total, 0, ',', '.') }}</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- Botones de Acción -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <a href="{{ route('alquileres.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Volver a la Lista
                    </a>
                    
                    @if($alquiler->estado == 'activo')
                        @can('editar-alquiler')
                        <a href="{{ route('alquileres.edit', $alquiler) }}" class="btn btn-warning">
                            <i class="fas fa-edit me-1"></i> Editar Alquiler
                        </a>
                        @endcan
                        
                        @can('eliminar-alquiler')
                        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#modalEliminar">
                            <i class="fas fa-trash me-1"></i> Eliminar Alquiler
                        </button>
                        @endcan
                        
                        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalCompletar">
                            <i class="fas fa-check-circle me-1"></i> Completar Alquiler
                        </button>
                    @endif
                    
                    <button class="btn btn-info" onclick="window.print()">
                        <i class="fas fa-print me-1"></i> Imprimir
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de confirmación para eliminar -->
<div class="modal fade" id="modalEliminar" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Confirmar Eliminación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>¿Está seguro que desea eliminar este alquiler?</p>
                <p class="fw-bold">Esta acción no se puede deshacer y liberará todas las prendas reservadas.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <form action="{{ route('alquileres.destroy', $alquiler) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Eliminar</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal de confirmación para completar -->
<div class="modal fade" id="modalCompletar" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">Completar Alquiler</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>¿Está seguro que desea marcar este alquiler como completado?</p>
                <p>Esta acción liberará todas las prendas para que estén disponibles nuevamente.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <form action="{{ route('alquileres.alquileres.devolver', $alquiler) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-success">Completar Alquiler</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
    $(document).ready(function() {
        // Inicializar tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });
    });
</script>
@endpush