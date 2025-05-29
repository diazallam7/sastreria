{{-- Archivo: resources/views/productos-venta/edit.blade.php --}}
@extends('template')

@section('title', 'Editar Producto de Venta')

@push('css')
<style>
    .talle-container {
        border: 1px solid #dee2e6;
        border-radius: 0.25rem;
        padding: 15px;
        margin-bottom: 15px;
        background-color: #f8f9fa;
    }
    .remove-talle {
        cursor: pointer;
        color: #dc3545;
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4 text-center">Editar Producto de Venta</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item"><a href="{{ route('productos-venta.index') }}">Productos de Venta</a></li>
        <li class="breadcrumb-item active">Editar Producto</li>
    </ol>

    <div class="card">
        <div class="card-header">
            <i class="fas fa-edit me-2"></i>Formulario de Edición
        </div>
        <div class="card-body">
            <form action="{{ route('productos-venta.update', $producto->id) }}" method="POST" id="productoForm">
                @csrf
                @method('PUT')
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="nombre_producto" class="form-label">Nombre del Producto</label>
                        <input type="text" class="form-control @error('nombre_producto') is-invalid @enderror" 
                               name="nombre_producto" id="nombre_producto" value="{{ old('nombre_producto', $producto->nombre_producto) }}" required>
                        @error('nombre_producto')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="precio_venta" class="form-label">Precio de Venta (₲)</label>
                        <input type="number" step="0.01" class="form-control @error('precio_venta') is-invalid @enderror" 
                               name="precio_venta" id="precio_venta" value="{{ old('precio_venta', $producto->precio_venta) }}" required>
                        @error('precio_venta')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label for="observacion" class="form-label">Observación (opcional)</label>
                    <textarea class="form-control @error('observacion') is-invalid @enderror" 
                              name="observacion" id="observacion" rows="3">{{ old('observacion', $producto->observacion) }}</textarea>
                    @error('observacion')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Talles y Cantidades</label>
                    <div id="tallesContainer">
                        @foreach($producto->talles as $index => $talle)
                            <div class="talle-container">
                                <div class="row">
                                    <div class="col-md-6">
                                        <label class="form-label">Talle</label>
                                        <input type="text" class="form-control" name="talles[{{ $index }}][talle]" value="{{ $talle->talle }}" required>
                                        <input type="hidden" name="talles[{{ $index }}][id]" value="{{ $talle->id }}">
                                    </div>
                                    <div class="col-md-5">
                                        <label class="form-label">Cantidad Total</label>
                                        <input type="number" class="form-control" name="talles[{{ $index }}][cantidad]" value="{{ $talle->cantidad_total }}" min="0" required>
                                    </div>
                                    <div class="col-md-1 d-flex align-items-end">
                                        <button type="button" class="btn btn-danger remove-talle">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="row mt-2">
                                    <div class="col-md-6">
                                        <small class="text-muted">Disponibles: {{ $talle->cantidad_disponible }}</small>
                                    </div>
                                    <div class="col-md-6">
                                        <small class="text-muted">Vendidos: {{ $talle->cantidad_vendida }}</small>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <button type="button" class="btn btn-success mt-2" id="addTalle">
                        <i class="fas fa-plus"></i> Agregar Talle
                    </button>
                </div>

                <div class="text-center">
                    <button type="submit" class="btn btn-primary">Actualizar Producto</button>
                    <a href="{{ route('productos-venta.index') }}" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        let talleIndex = {{ count($producto->talles) }};
        
        // Agregar nuevo talle
        document.getElementById('addTalle').addEventListener('click', function() {
            const container = document.createElement('div');
            container.className = 'talle-container';
            container.innerHTML = `
                <div class="row">
                    <div class="col-md-6">
                        <label class="form-label">Talle</label>
                        <input type="text" class="form-control" name="talles[${talleIndex}][talle]" required>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label">Cantidad</label>
                        <input type="number" class="form-control" name="talles[${talleIndex}][cantidad]" value="0" min="0" required>
                    </div>
                    <div class="col-md-1 d-flex align-items-end">
                        <button type="button" class="btn btn-danger remove-talle">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            `;
            
            document.getElementById('tallesContainer').appendChild(container);
            talleIndex++;
            
            // Agregar evento para eliminar el talle recién creado
            container.querySelector('.remove-talle').addEventListener('click', function() {
                container.remove();
            });
        });
        
        // Eliminar talle (para los que ya existen en la carga inicial)
        document.querySelectorAll('.remove-talle').forEach(button => {
            button.addEventListener('click', function() {
                const container = this.closest('.talle-container');
                container.remove();
            });
        });
        
        // Validar que haya al menos un talle antes de enviar el formulario
        document.getElementById('productoForm').addEventListener('submit', function(e) {
            const talles = document.querySelectorAll('.talle-container');
            if (talles.length === 0) {
                e.preventDefault();
                alert('Debe agregar al menos un talle');
            }
        });
    });
</script>
@endpush