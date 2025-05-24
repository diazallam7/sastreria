@extends('template')

@section('title', 'Registrar Venta')

@push('css')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endpush

@section('content')

<div class="container-fluid px-4">
    <h1 class="mt-4 text-center">Registrar Venta</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item"><a href="{{ route('ventas.index') }}">Ventas</a></li>
        <li class="breadcrumb-item active">Registrar Venta</li>
    </ol>

    <div class="card">
        <div class="card-header">
            <i class="fas fa-plus me-2"></i>Formulario de Registro de Venta
        </div>
        <div class="card-body">
            <form action="{{ route('ventas.store') }}" method="POST">
                @csrf

                <!-- Selección de cliente -->
                <div class="mb-3">
                    <label for="cliente_id" class="form-label">Cliente</label>
                    <select class="form-select @error('cliente_id') is-invalid @enderror" id="cliente_id" name="cliente_id" required>
                        <option value="" selected disabled>Seleccione un cliente</option>
                        @foreach ($clientes as $cliente)
                            <option value="{{ $cliente->id }}" {{ old('cliente_id') == $cliente->id ? 'selected' : '' }}>
                                {{ $cliente->nombre }}
                            </option>
                        @endforeach
                    </select>
                    @error('cliente_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Selección de vestido -->
                <div class="mb-3">
                    <label for="vestido_id" class="form-label">Prenda</label>
                    <select class="form-select @error('vestido_id') is-invalid @enderror" id="vestido_id" name="vestido_id" onchange="updatePrecio()" required>
                        <option value="" selected disabled>Seleccione una Prenda</option>
                        @foreach ($vestidos as $vestido)
                            <option value="{{ $vestido->id }}" data-precio="{{ $vestido->precio_venta }}" {{ old('vestido_id') == $vestido->id ? 'selected' : '' }}>
                                {{ $vestido->nombre }} ({{ $vestido->categoria }}, {{ $vestido->color }})
                            </option>
                        @endforeach
                    </select>
                    @error('vestido_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Fecha de venta -->
                <div class="mb-3">
                    <label for="fecha_venta" class="form-label">Fecha de Venta</label>
                    <input type="date" class="form-control @error('fecha_venta') is-invalid @enderror" id="fecha_venta" name="fecha_venta" value="{{ old('fecha_venta') }}" required>
                    @error('fecha_venta')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Precio total -->
                <div class="mb-3">
                    <label for="precio_total" class="form-label">Precio Total (Gs)</label>
                    <input type="number" class="form-control @error('precio_total') is-invalid @enderror" id="precio_total" name="precio_total" value="{{ old('precio_total') }}" step="0.01" min="0" required>
                    @error('precio_total')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Botones -->
                <div class="d-flex justify-content-end">
                    <a href="{{ route('ventas.index') }}" class="btn btn-secondary me-2">Cancelar</a>
                    <button type="submit" class="btn btn-primary">Registrar Venta</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function updatePrecio() {
        const vestidoSelect = document.getElementById('vestido_id');
        const precioInput = document.getElementById('precio_total');
        const selectedOption = vestidoSelect.options[vestidoSelect.selectedIndex];

        if (selectedOption && selectedOption.dataset.precio) {
            precioInput.value = selectedOption.dataset.precio;
        }
    }
</script>

@endsection
