@extends('template')

@section('title', 'Añadir Prenda')

@push('css')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endpush

@section('content')

@if ($errors->any())
    <script>
        Swal.fire({
            icon: 'error',
            title: '¡Error!',
            html: `
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            `,
            confirmButtonText: 'Aceptar',
        });
    </script>
@endif

<div class="container-fluid px-4">
    <h1 class="mt-4 text-center">Nueva Prenda</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item"><a href="{{ route('vestidos.index') }}">Prendas</a></li>
        <li class="breadcrumb-item active">Añadir</li>
    </ol>

    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-plus me-2"></i>Formulario para Añadir Prenda
        </div>
        <div class="card-body">
            <form action="{{ route('vestidos.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="nombre" class="form-label">Nombre de la Prenda</label>
                    <input type="text" class="form-control @error('nombre') is-invalid @enderror" id="nombre" name="nombre" value="{{ old('nombre') }}" required>
                    @error('nombre')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="mb-3">
                    <label for="descripcion" class="form-label">Descripción</label>
                    <textarea class="form-control @error('descripcion') is-invalid @enderror" id="descripcion" name="descripcion" rows="3">{{ old('descripcion') }}</textarea>
                    @error('descripcion')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="mb-3">
                    <label for="talla" class="form-label">Talla</label>
                    <input type="text" class="form-control @error('talla') is-invalid @enderror" id="talla" name="talla" value="{{ old('talla') }}" required>
                    @error('talla')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="mb-3">
                    <label for="color" class="form-label">Color</label>
                    <input type="text" class="form-control @error('color') is-invalid @enderror" id="color" name="color" value="{{ old('color') }}" required>
                    @error('color')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="mb-3">
                    <label for="categoria" class="form-label">Categoría</label>
                    <select class="form-control @error('categoria') is-invalid @enderror" id="categoria" name="categoria" required>
                        <option value="">Seleccione una categoría</option>
                        <option value="Camisa" {{ old('categoria') == 'Camisa' ? 'selected' : '' }}>Camisa</option>
                        <option value="Pantalón" {{ old('categoria') == 'Pantalón' ? 'selected' : '' }}>Pantalón</option>
                        <option value="Saco" {{ old('categoria') == 'Saco' ? 'selected' : '' }}>Saco</option>
                        <option value="Chaleco" {{ old('categoria') == 'Chaleco' ? 'selected' : '' }}>Chaleco</option>
                        <option value="Corbata" {{ old('categoria') == 'Corbata' ? 'selected' : '' }}>Corbata</option>
                        <option value="Zapatos" {{ old('categoria') == 'Zapatos' ? 'selected' : '' }}>Zapatos</option>
                        <option value="Otro" {{ old('categoria') == 'Otro' ? 'selected' : '' }}>Otro</option>
                    </select>
                    @error('categoria')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="mb-3">
                    <label for="precio_alquiler" class="form-label">Precio de Alquiler (₲)</label>
                    <input type="number" class="form-control @error('precio_alquiler') is-invalid @enderror" id="precio_alquiler" name="precio_alquiler" value="{{ old('precio_alquiler') }}" step="0.01" min="0">
                    @error('precio_alquiler')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="mb-3">
                    <label for="precio_venta" class="form-label">Precio de Venta (₲)</label>
                    <input type="number" class="form-control @error('precio_venta') is-invalid @enderror" id="precio_venta" name="precio_venta" value="{{ old('precio_venta') }}" step="0.01" min="0">
                    @error('precio_venta')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-success">Guardar</button>
                <a href="{{ route('vestidos.index') }}" class="btn btn-secondary">Cancelar</a>
            </form>
        </div>
    </div>
</div>
@endsection