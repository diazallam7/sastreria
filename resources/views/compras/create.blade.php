{{-- Archivo: resources/views/compras/create.blade.php --}}
@extends('template')

@section('title', 'Registrar Compra')

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
    <h1 class="mt-4 text-center">Registrar Nueva Compra</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item"><a href="{{ route('compras.index') }}">Compras</a></li>
        <li class="breadcrumb-item active">Registrar Compra</li>
    </ol>

    <div class="card">
        <div class="card-header">
            <i class="fas fa-plus-circle me-2"></i>Formulario de Registro
        </div>
        <div class="card-body">
            <form action="{{ route('compras.store') }}" method="POST" id="compraForm">
                @csrf
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="nombre_producto" class="form-label">Nombre del Producto</label>
                        <input type="text" class="form-control @error('nombre_producto') is-invalid @enderror" 
                               name="nombre_producto" id="nombre_producto" value="{{ old('nombre_producto') }}" required>
                        @error('nombre_producto')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="fecha_compra" class="form-label">Fecha de Compra</label>
                        <input type="date" class="form-control @error('fecha_compra') is-invalid @enderror" 
                               name="fecha_compra" id="fecha_compra" value="{{ old('fecha_compra', date('Y-m-d')) }}" required>
                        @error('fecha_compra')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="precio_compra" class="form-label">Precio de Compra (₲)</label>
                        <input type="text" class="form-control money-input @error('precio_compra') is-invalid @enderror" 
                               name="precio_compra" id="precio_compra" value="{{ old('precio_compra') }}" required>
                        @error('precio_compra')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="precio_venta" class="form-label">Precio de Venta (₲)</label>
                        <input type="text" class="form-control money-input @error('precio_venta') is-invalid @enderror" 
                               name="precio_venta" id="precio_venta" value="{{ old('precio_venta') }}" required>
                        @error('precio_venta')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label for="observacion" class="form-label">Observación (opcional)</label>
                    <textarea class="form-control @error('observacion') is-invalid @enderror" 
                              name="observacion" id="observacion" rows="3">{{ old('observacion') }}</textarea>
                    @error('observacion')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Talles y Cantidades</label>
                    <div id="tallesContainer">
                        @if(old('talles'))
                            @foreach(old('talles') as $index => $talle)
                                <div class="talle-container">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label class="form-label">Talle</label>
                                            <input type="text" class="form-control" name="talles[{{ $index }}][talle]" value="{{ $talle['talle'] ?? '' }}" required>
                                        </div>
                                        <div class="col-md-5">
                                            <label class="form-label">Cantidad</label>
                                            <input type="number" class="form-control" name="talles[{{ $index }}][cantidad]" value="{{ $talle['cantidad'] ?? 0 }}" min="0" required>
                                        </div>
                                        <div class="col-md-1 d-flex align-items-end">
                                            <button type="button" class="btn btn-danger remove-talle">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="talle-container">
                                <div class="row">
                                    <div class="col-md-6">
                                        <label class="form-label">Talle</label>
                                        <input type="text" class="form-control" name="talles[0][talle]" required>
                                    </div>
                                    <div class="col-md-5">
                                        <label class="form-label">Cantidad</label>
                                        <input type="number" class="form-control" name="talles[0][cantidad]" value="0" min="0" required>
                                    </div>
                                    <div class="col-md-1 d-flex align-items-end">
                                        <button type="button" class="btn btn-danger remove-talle">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                    <button type="button" class="btn btn-success mt-2" id="addTalle">
                        <i class="fas fa-plus"></i> Agregar Talle
                    </button>
                </div>

                <div class="text-center">
                    <button type="submit" class="btn btn-primary">Registrar Compra</button>
                    <a href="{{ route('compras.index') }}" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        let talleIndex = document.querySelectorAll('.talle-container').length;
        
        // Funciones para formateo de números
        function formatNumber(num) {
            return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        }
        
        function unformatNumber(str) {
            return str.replace(/\./g, '');
        }
        
        // Aplicar formateo a campos de dinero
        function setupMoneyInput(input) {
            input.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, ''); // Solo números
                if (value) {
                    e.target.value = formatNumber(value);
                }
            });
            
            input.addEventListener('blur', function(e) {
                let value = unformatNumber(e.target.value);
                if (value) {
                    e.target.value = formatNumber(value);
                }
            });
        }
        
        // Inicializar campos de dinero existentes
        document.querySelectorAll('.money-input').forEach(setupMoneyInput);
        
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
        document.getElementById('compraForm').addEventListener('submit', function(e) {
            // Convertir campos de dinero a números sin formato antes de enviar
            document.querySelectorAll('.money-input').forEach(input => {
                input.value = unformatNumber(input.value);
            });
            
            const talles = document.querySelectorAll('.talle-container');
            if (talles.length === 0) {
                e.preventDefault();
                alert('Debe agregar al menos un talle');
            }
        });
    });
</script>
@endpush
