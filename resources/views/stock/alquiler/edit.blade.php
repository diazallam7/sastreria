{{-- Archivo: resources/views/stock/alquiler/edit.blade.php --}}
@extends('template')

@section('title', 'Editar Prenda del Stock')

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
    <h1 class="mt-4 text-center">Editar Prenda del Stock de Alquiler</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item"><a href="{{ route('stock.alquiler.index') }}">Stock de Alquiler</a></li>
        <li class="breadcrumb-item active">Editar Prenda</li>
    </ol>

    <div class="card">
        <div class="card-header">
            <i class="fas fa-edit me-2"></i>Formulario de Edición
        </div>
        <div class="card-body">
            <form action="{{ route('stock.alquiler.update', $item->id) }}" method="POST" id="stockForm">
                @csrf
                @method('PUT')
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="codigo" class="form-label">Código</label>
                        <input type="text" class="form-control @error('codigo') is-invalid @enderror" 
                               name="codigo" id="codigo" value="{{ old('codigo', $item->codigo) }}" required>
                        @error('codigo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="nombre" class="form-label">Nombre de la Prenda</label>
                        <input type="text" class="form-control @error('nombre') is-invalid @enderror" 
                               name="nombre" id="nombre" value="{{ old('nombre', $item->nombre) }}" required>
                        @error('nombre')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="precio_alquiler" class="form-label">Precio de Alquiler (₲)</label>
                        <input type="number" step="0.01" class="form-control @error('precio_alquiler') is-invalid @enderror" 
                               name="precio_alquiler" id="precio_alquiler" value="{{ old('precio_alquiler', $item->precio_alquiler) }}" required>
                        @error('precio_alquiler')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label for="descripcion" class="form-label">Descripción (opcional)</label>
                    <textarea class="form-control @error('descripcion') is-invalid @enderror" 
                              name="descripcion" id="descripcion" rows="3">{{ old('descripcion', $item->descripcion) }}</textarea>
                    @error('descripcion')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Talles y Cantidades</label>
                    <div id="tallesContainer">
                        <!-- Aquí se mostrarán los talles existentes -->
                        @foreach($item->talles as $index => $talle)
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
                                    <div class="col-md-4">
                                        <small class="text-muted">Disponibles: {{ $talle->cantidad_disponible }}</small>
                                    </div>
                                    <div class="col-md-4">
                                        <small class="text-muted">Alquilados: {{ $talle->cantidad_alquilada }}</small>
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
                    <button type="submit" class="btn btn-primary">Actualizar Prenda</button>
                    <a href="{{ route('stock.alquiler.index') }}" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        let talleIndex = {{ count($item->talles) }};
        
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
        document.getElementById('stockForm').addEventListener('submit', function(e) {
            const talles = document.querySelectorAll('.talle-container');
            if (talles.length === 0) {
                e.preventDefault();
                alert('Debe agregar al menos un talle');
            }
        });
    });
</script>
@endpush