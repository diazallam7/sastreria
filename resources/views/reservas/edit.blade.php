{{-- Archivo: resources/views/reservas/edit.blade.php --}}
@extends('template')

@section('title', 'Editar Reserva')

@push('css')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
    .info-actual {
        background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
        color: white;
        border-radius: 10px;
        padding: 15px;
        margin-bottom: 20px;
    }
</style>
@endpush

@section('content')

@if (session('error'))
    <script>
        Swal.fire({
            icon: 'error',
            title: '{{ session('error') }}',
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000
        });
    </script>
@endif

<div class="container-fluid px-4">
    <h1 class="mt-4 text-center">Editar Reserva #{{ $reserva->id }}</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item"><a href="{{ route('reservas.index') }}">Reservas</a></li>
        <li class="breadcrumb-item active">Editar</li>
    </ol>

    <div class="info-actual">
        <h6 class="mb-2"><i class="fas fa-info-circle me-2"></i>Información Actual</h6>
        <div class="row">
            <div class="col-md-6">
                <strong>Cliente:</strong> {{ $reserva->cliente->nombre }}<br>
                <strong>Estado:</strong> {{ ucfirst($reserva->estado) }}<br>
                <strong>Reserva:</strong> {{ $reserva->fecha_reserva->format('d/m/Y') }}
            </div>
            <div class="col-md-6">
                <strong>Monto Total:</strong> ₲ {{ number_format($reserva->monto_total, 0, ',', '.') }}<br>
                <strong>Por Cobrar:</strong> ₲ {{ number_format($reserva->total_a_cobrar, 0, ',', '.') }}<br>
                <strong>Creada:</strong> {{ $reserva->created_at->format('d/m/Y H:i') }}
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <i class="fas fa-edit me-2"></i>Formulario de Edición
        </div>
        <div class="card-body">
            <form action="{{ route('reservas.update', $reserva->id) }}" method="POST" id="reservaForm">
                @csrf
                @method('PUT')

                <div class="row mb-4">
                    <div class="col-md-6">
                        <label for="cliente_id" class="form-label">Cliente</label>
                        <select class="form-select @error('cliente_id') is-invalid @enderror" id="cliente_id" name="cliente_id" required>
                            <option value="" disabled>Seleccione un cliente</option>
                            @foreach ($clientes as $cliente)
                                <option value="{{ $cliente->id }}" {{ ($reserva->cliente_id == $cliente->id || old('cliente_id') == $cliente->id) ? 'selected' : '' }}>
                                    {{ $cliente->nombre }}
                                </option>
                            @endforeach
                        </select>
                        @error('cliente_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-4">
                        <label for="fecha_reserva-" class="form-label">Fecha de Reserva</label>
                        <input type="date" class="form-control @error('fecha_reserva-') is-invalid @enderror" 
                               id="fecha_reserva-" name="fecha_reserva-" 
                               value="{{ old('fecha_reserva-', $reserva->fecha_reserva->format('Y-m-d')) }}" required>
                        @error('fecha_reserva-')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label for="fecha_entrega_programada" class="form-label">Fecha de Entrega</label>
                        <input type="date" class="form-control @error('fecha_entrega_programada') is-invalid @enderror" 
                               id="fecha_entrega_programada" name="fecha_entrega_programada" 
                               value="{{ old('fecha_entrega_programada', $reserva->fecha_entrega_programada->format('Y-m-d')) }}" required>
                        @error('fecha_entrega_programada')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label for="fecha_devolucion_programada" class="form-label">Fecha de Devolución</label>
                        <input type="date" class="form-control @error('fecha_devolucion_programada') is-invalid @enderror" 
                               id="fecha_devolucion_programada" name="fecha_devolucion_programada" 
                               value="{{ old('fecha_devolucion_programada', $reserva->fecha_devolucion_programada->format('Y-m-d')) }}" required>
                        @error('fecha_devolucion_programada')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-4">
                    <h6>Prendas Actualmente Reservadas</h6>
                    <div class="alert alert-info">
                        @foreach($reserva->stockItems as $item)
                            @php
                                $talle = \App\Models\TalleStock::find($item->pivot->talle_id);
                                $talleName = $talle ? $talle->talle : 'N/A';
                            @endphp
                            <div>• {{ $item->nombre }} - Talle: {{ $talleName }} - Cantidad: {{ $item->pivot->cantidad }}</div>
                        @endforeach
                    </div>
                    <p class="text-warning"><strong>Nota:</strong> Para cambiar las prendas, cancele esta reserva y cree una nueva.</p>
                </div>

                <div class="row mb-4">
                    <div class="col-md-3">
                        <label for="monto_total" class="form-label">Monto Total del Alquiler (₲)</label>
                        <input type="number" class="form-control @error('monto_total') is-invalid @enderror" 
                               id="monto_total" name="monto_total" 
                               value="{{ old('monto_total', $reserva->monto_total) }}" 
                               step="1000" min="0" required>
                        @error('monto_total')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-3">
                        <label for="garantia_total" class="form-label">Garantía Total (₲)</label>
                        <input type="number" class="form-control @error('garantia_total') is-invalid @enderror" 
                               id="garantia_total" name="garantia_total" 
                               value="{{ old('garantia_total', $reserva->garantia_total) }}" 
                               step="1000" min="0" required>
                        @error('garantia_total')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-3">
                        <label for="seña_garantia" class="form-label">Seña de Garantía (₲) *</label>
                        <input type="number" class="form-control @error('seña_garantia') is-invalid @enderror" 
                               id="seña_garantia" name="seña_garantia" 
                               value="{{ old('seña_garantia', $reserva->seña_garantia) }}" 
                               step="1000" min="0" required>
                        @error('seña_garantia')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-3">
                        <label for="seña_alquiler" class="form-label">Seña de Alquiler (₲)</label>
                        <input type="number" class="form-control @error('seña_alquiler') is-invalid @enderror" 
                               id="seña_alquiler" name="seña_alquiler" 
                               value="{{ old('seña_alquiler', $reserva->seña_alquiler) }}" 
                               step="1000" min="0">
                        @error('seña_alquiler')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="alert alert-info" id="resumenFinanciero">
                    <h6>Resumen Financiero Actualizado</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Saldo Alquiler a Cobrar:</strong> <span id="saldoAlquiler">₲ 0</span></p>
                            <p><strong>Saldo Garantía a Cobrar:</strong> <span id="saldoGarantia">₲ 0</span></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>TOTAL A COBRAR EN ENTREGA:</strong> <span id="totalACobrar" class="text-success fw-bold">₲ 0</span></p>
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <label for="observaciones" class="form-label">Observaciones</label>
                    <textarea class="form-control @error('observaciones') is-invalid @enderror" 
                              id="observaciones" name="observaciones" rows="3" 
                              placeholder="Observaciones adicionales sobre la reserva">{{ old('observaciones', $reserva->observaciones) }}</textarea>
                    @error('observaciones')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex justify-content-end">
                    <a href="{{ route('reservas.show', $reserva->id) }}" class="btn btn-secondary me-2">Cancelar</a>
                    <button type="submit" class="btn btn-warning">Actualizar Reserva</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    function calcularResumen() {
        const montoTotal = parseFloat(document.getElementById('monto_total').value) || 0;
        const garantiaTotal = parseFloat(document.getElementById('garantia_total').value) || 0;
        const seniaAlquiler = parseFloat(document.getElementById('seña_alquiler').value) || 0;
        const seniaGarantia = parseFloat(document.getElementById('seña_garantia').value) || 0;

        const saldoAlquiler = montoTotal - seniaAlquiler;
        const saldoGarantia = garantiaTotal - seniaGarantia;
        const totalACobrar = saldoAlquiler + saldoGarantia;

        document.getElementById('saldoAlquiler').textContent = `₲ ${saldoAlquiler.toLocaleString()}`;
        document.getElementById('saldoGarantia').textContent = `₲ ${saldoGarantia.toLocaleString()}`;
        document.getElementById('totalACobrar').textContent = `₲ ${totalACobrar.toLocaleString()}`;
    }

    ['monto_total', 'garantia_total', 'seña_alquiler', 'seña_garantia'].forEach(id => {
        document.getElementById(id).addEventListener('input', calcularResumen);
    });

    calcularResumen();

    document.getElementById('reservaForm').addEventListener('submit', function(e) {
        const seniaGarantia = parseFloat(document.getElementById('seña_garantia').value) || 0;
        const garantiaTotal = parseFloat(document.getElementById('garantia_total').value) || 0;
        
        if (seniaGarantia > garantiaTotal) {
            e.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'Seña de garantía inválida',
                text: 'La seña de garantía no puede ser mayor al total de la garantía.',
                confirmButtonText: 'Entendido'
            });
            return false;
        }

        const seniaAlquiler = parseFloat(document.getElementById('seña_alquiler').value) || 0;
        const montoTotal = parseFloat(document.getElementById('monto_total').value) || 0;
        
        if (seniaAlquiler > montoTotal) {
            e.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'Seña de alquiler inválida',
                text: 'La seña de alquiler no puede ser mayor al monto total.',
                confirmButtonText: 'Entendido'
            });
            return false;
        }
    });
});
</script>
@endpush