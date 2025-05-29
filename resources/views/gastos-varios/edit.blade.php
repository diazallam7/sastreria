{{-- Archivo: resources/views/gastos-varios/edit.blade.php --}}
@extends('template')

@section('title', 'Editar Gasto')

@push('css')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
    .form-container {
        max-width: 800px;
        margin: 0 auto;
    }
    .form-section {
        background: #f8f9fa;
        border-radius: 10px;
        padding: 25px;
        margin-bottom: 20px;
        border-left: 4px solid #ffc107;
    }
    .form-section h6 {
        color: #ffc107;
        margin-bottom: 20px;
        font-weight: 600;
    }
    .btn-submit {
        background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%);
        border: none;
        padding: 12px 30px;
        font-weight: 600;
        color: #000;
    }
    .info-card {
        background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
        color: white;
        border-radius: 10px;
        padding: 15px;
        margin-bottom: 20px;
    }
</style>
@endpush

@section('content')

<div class="container-fluid px-4">
    <h1 class="mt-4 text-center">Editar Gasto #{{ $gasto->id }}</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item"><a href="{{ route('gastos-varios.index') }}">Gastos Varios</a></li>
        <li class="breadcrumb-item active">Editar Gasto</li>
    </ol>

    <div class="form-container">
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- Información actual del gasto -->
        <div class="info-card">
            <h6 class="mb-2"><i class="fas fa-info-circle me-2"></i>Información Actual</h6>
            <div class="row">
                <div class="col-md-6">
                    <strong>Gasto:</strong> {{ $gasto->nombre_gasto }}<br>
                    <strong>Fecha:</strong> {{ $gasto->fecha->format('d/m/Y') }}
                </div>
                <div class="col-md-6">
                    <strong>Monto:</strong> {{ $gasto->monto_formateado }}<br>
                    <strong>Registrado:</strong> {{ $gasto->created_at->format('d/m/Y H:i') }}
                </div>
            </div>
        </div>

        <div class="card shadow">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0"><i class="fas fa-edit me-2"></i>Formulario de Edición</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('gastos-varios.update', $gasto->id) }}" method="POST" id="gastoForm">
                    @csrf
                    @method('PUT')

                    <div class="form-section">
                        <h6><i class="fas fa-edit me-2"></i>Modificar Información del Gasto</h6>
                        
                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label for="nombre_gasto" class="form-label">Nombre del Gasto <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control @error('nombre_gasto') is-invalid @enderror" 
                                       id="nombre_gasto" 
                                       name="nombre_gasto" 
                                       value="{{ old('nombre_gasto', $gasto->nombre_gasto) }}" 
                                       placeholder="Ej: Compra de materiales, Pago de servicios, etc."
                                       required>
                                @error('nombre_gasto')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="fecha" class="form-label">Fecha <span class="text-danger">*</span></label>
                                <input type="date" 
                                       class="form-control @error('fecha') is-invalid @enderror" 
                                       id="fecha" 
                                       name="fecha" 
                                       value="{{ old('fecha', $gasto->fecha->format('Y-m-d')) }}" 
                                       required>
                                @error('fecha')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="monto" class="form-label">Monto (₲) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">₲</span>
                                    <input type="number" 
                                           class="form-control @error('monto') is-invalid @enderror" 
                                           id="monto" 
                                           name="monto" 
                                           value="{{ old('monto', $gasto->monto) }}" 
                                           step="0.01" 
                                           min="0" 
                                           placeholder="0.00"
                                           required>
                                    @error('monto')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <small class="text-muted">Ingrese el monto sin puntos ni comas</small>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="observacion" class="form-label">Observación</label>
                            <textarea class="form-control @error('observacion') is-invalid @enderror" 
                                      id="observacion" 
                                      name="observacion" 
                                      rows="4" 
                                      placeholder="Detalles adicionales sobre el gasto (opcional)">{{ old('observacion', $gasto->observacion) }}</textarea>
                            @error('observacion')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Máximo 1000 caracteres</small>
                        </div>
                    </div>

                    <!-- Botones -->
                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('gastos-varios.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times me-2"></i>Cancelar
                        </a>
                        <button type="submit" class="btn btn-warning btn-submit">
                            <i class="fas fa-save me-2"></i>Actualizar Gasto
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Formatear el input de monto mientras se escribe
    const montoInput = document.getElementById('monto');
    
    montoInput.addEventListener('input', function() {
        // Remover caracteres no numéricos excepto punto decimal
        let value = this.value.replace(/[^0-9.]/g, '');
        
        // Asegurar solo un punto decimal
        const parts = value.split('.');
        if (parts.length > 2) {
            value = parts[0] + '.' + parts.slice(1).join('');
        }
        
        this.value = value;
    });

    // Validación del formulario
    document.getElementById('gastoForm').addEventListener('submit', function(e) {
        const monto = parseFloat(document.getElementById('monto').value);
        
        if (monto <= 0) {
            e.preventDefault();
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Monto inválido',
                    text: 'El monto debe ser mayor a 0.',
                    confirmButtonText: 'Entendido'
                });
            } else {
                alert('El monto debe ser mayor a 0.');
            }
            return false;
        }
    });

    // Contador de caracteres para observación
    const observacionTextarea = document.getElementById('observacion');
    const maxLength = 1000;
    
    // Crear elemento para mostrar contador
    const counterElement = document.createElement('small');
    counterElement.className = 'text-muted float-end';
    observacionTextarea.parentNode.appendChild(counterElement);
    
    function updateCounter() {
        const remaining = maxLength - observacionTextarea.value.length;
        counterElement.textContent = `${observacionTextarea.value.length}/${maxLength} caracteres`;
        
        if (remaining < 100) {
            counterElement.className = 'text-warning float-end';
        } else if (remaining < 50) {
            counterElement.className = 'text-danger float-end';
        } else {
            counterElement.className = 'text-muted float-end';
        }
    }
    
    observacionTextarea.addEventListener('input', updateCounter);
    updateCounter(); // Inicializar contador
});
</script>
@endpush