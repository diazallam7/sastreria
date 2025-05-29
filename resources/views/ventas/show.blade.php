{{-- Archivo: resources/views/ventas/show.blade.php --}}
@extends('template')

@section('title', 'Detalle de Venta')

@push('css')
    <style>
        .info-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .detalle-card {
            border: 1px solid #dee2e6;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 15px;
            transition: all 0.3s ease;
        }
        .detalle-card:hover {
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }
        .badge-tipo {
            font-size: 0.8rem;
            padding: 5px 10px;
        }
        .precio-total {
            font-size: 1.5rem;
            font-weight: bold;
            color: #28a745;
        }
    </style>
@endpush

@section('content')

<div class="container-fluid px-4">
    <h1 class="mt-4 text-center">Detalle de Venta #{{ $venta->id }}</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item"><a href="{{ route('ventas.index') }}">Ventas</a></li>
        <li class="breadcrumb-item active">Detalle de Venta</li>
    </ol>

    <div class="row">
        <!-- Información General -->
        <div class="col-md-4">
            <div class="info-card">
                <h5 class="mb-3"><i class="fas fa-info-circle me-2"></i>Información General</h5>
                <div class="mb-2">
                    <strong>Cliente:</strong><br>
                    {{ $venta->cliente->nombre }}
                </div>
                <div class="mb-2">
                    <strong>Fecha de Venta:</strong><br>
                    {{ $venta->fecha_venta->format('d/m/Y') }}
                </div>
                <div class="mb-2">
                    <strong>Total de Productos:</strong><br>
                    {{ $venta->detalles->count() }} productos
                </div>
                <div class="mb-2">
                    <strong>Cantidad Total:</strong><br>
                    {{ $venta->detalles->sum('cantidad') }} unidades
                </div>
                <hr class="my-3" style="border-color: rgba(255,255,255,0.3);">
                <div class="precio-total text-center">
                    ₲ {{ number_format($venta->precio_total, 0, ',', '.') }}
                </div>
            </div>

            <!-- Botones de Acción -->
            <div class="d-grid gap-2">
                <a href="{{ route('ventas.edit', $venta->id) }}" class="btn btn-warning">
                    <i class="fas fa-edit me-2"></i>Editar Venta
                </a>
                <a href="{{ route('ventas.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Volver al Listado
                </a>
                <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#eliminarModal">
                    <i class="fas fa-trash me-2"></i>Eliminar Venta
                </button>
            </div>
        </div>

        <!-- Detalles de Productos -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-shopping-cart me-2"></i>Productos Vendidos</h5>
                </div>
                <div class="card-body">
                    @foreach($detallesConProductos as $item)
                        <div class="detalle-card">
                            <div class="row align-items-center">
                                <div class="col-md-6">
                                    <h6 class="mb-1">{{ $item['producto']->nombre_producto }}</h6>
                                    <span class="badge badge-tipo {{ $item['detalle']->tipo_producto === 'compra' ? 'bg-primary' : 'bg-success' }}">
                                        {{ $item['detalle']->tipo_producto === 'compra' ? 'Producto de Compra' : 'Producto Manual' }}
                                    </span>
                                </div>
                                <div class="col-md-2 text-center">
                                    <small class="text-muted">Talle</small><br>
                                    <strong>{{ $item['talle']->talle }}</strong>
                                </div>
                                <div class="col-md-2 text-center">
                                    <small class="text-muted">Cantidad</small><br>
                                    <strong>{{ $item['detalle']->cantidad }}</strong>
                                </div>
                                <div class="col-md-2 text-end">
                                    <small class="text-muted">Subtotal</small><br>
                                    <strong class="text-primary">₲ {{ number_format($item['detalle']->subtotal, 0, ',', '.') }}</strong>
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-12">
                                    <small class="text-muted">
                                        Precio unitario: ₲ {{ number_format($item['detalle']->precio_unitario, 0, ',', '.') }}
                                    </small>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Confirmación para Eliminar -->
<div class="modal fade" id="eliminarModal" tabindex="-1" aria-labelledby="eliminarModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="eliminarModalLabel">Confirmar Eliminación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>¿Está seguro que desea eliminar esta venta?</p>
                <p><strong>Esta acción no se puede deshacer y restaurará el stock de los productos.</strong></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <form action="{{ route('ventas.destroy', $venta->id) }}" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Eliminar Venta</button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection