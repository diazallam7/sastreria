@extends('template')

@section('title', 'Devoluciones')

@push('css')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .prenda-list {
            margin-bottom: 0;
        }
    </style>
@endpush

@section('content')
    @if (session('success'))
        <script>
            Swal.fire({
                icon: 'success',
                title: '{{ session('success') }}',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 2000
            });
        </script>
    @endif

    <div class="container-fluid px-4">
        <h1 class="mt-4 text-center">Devoluciones</h1>
        <ol class="breadcrumb mb-4">
            <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
            <li class="breadcrumb-item active">Devoluciones</li>
        </ol>

        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-table me-1"></i> Pendientes de Devolución
            </div>
            <div class="card-body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Cliente</th>
                            <th>Prendas</th>
                            <th>Fecha de Devolución</th>
                            <th>Tipo</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($registros as $registro)
                            @php
                                // Calcular si hay retraso
                                $fechaFin = $registro->tipo === 'alquiler' 
                                    ? \Carbon\Carbon::parse($registro->fecha_fin)->startOfDay()
                                    : \Carbon\Carbon::parse($registro->fecha_evento)->startOfDay();
                                $fechaActual = \Carbon\Carbon::now()->startOfDay();
                                $diasRetraso = $fechaActual->gt($fechaFin) ? $fechaFin->diffInDays($fechaActual) : 0;
                            @endphp
                            <tr>
                                <td>{{ $registro->cliente->nombre }}</td>
                                <td>
                                    <ul class="list-unstyled prenda-list">
                                        @foreach ($registro->stockItems as $prenda)
                                            <li>{{ $prenda->nombre }} ({{ $prenda->codigo }})</li>
                                        @endforeach
                                    </ul>
                                </td>
                                <td>
                                    @if ($registro->tipo === 'alquiler')
                                        {{ \Carbon\Carbon::parse($registro->fecha_fin)->format('d/m/Y') }}
                                        @if ($diasRetraso > 0)
                                            <br><small class="text-danger">
                                                <i class="fas fa-exclamation-triangle"></i>
                                                {{ $diasRetraso }} día{{ $diasRetraso > 1 ? 's' : '' }} de retraso
                                            </small>
                                        @endif
                                    @else
                                        {{ \Carbon\Carbon::parse($registro->fecha_evento)->format('d/m/Y') }}
                                    @endif
                                </td>
                                <td>
                                    @if ($registro->tipo === 'alquiler')
                                        <span class="badge bg-warning">Alquiler</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($registro->estado === 'activo' || $registro->estado === 1)
                                        <span class="badge bg-success">Activo</span>
                                    @else
                                        <span class="badge bg-secondary">Completado</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        @if ($registro->tipo === 'alquiler')
                                            <a href="{{ route('devoluciones.calcular-multas', $registro->id) }}"
                                                class="btn btn-warning">
                                                Ver Multa
                                            </a>
                                        @endif

                                        <!-- Botón que verifica retraso antes de mostrar modal -->
                                        <button type="button" class="btn btn-success" 
                                            onclick="verificarRetrasoYProcesar({{ $registro->id }}, {{ $diasRetraso }}, {{ $registro->tipo === 'alquiler' ? 3 : 2 }})">
                                            Marcar como Entregado
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <!-- Modal de confirmación normal -->
                            <div class="modal fade" id="confirmModal-{{ $registro->id }}" tabindex="-1"
                                aria-labelledby="confirmModalLabel-{{ $registro->id }}" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="confirmModalLabel-{{ $registro->id }}">
                                                Confirmación
                                            </h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p>¿Está seguro de que desea marcar este
                                                {{ $registro->tipo === 'alquiler' ? 'alquiler' : 'reserva' }} como
                                                "Entregado"?</p>
                                            <p>Las siguientes prendas serán marcadas como disponibles:</p>
                                            <ul>
                                                @foreach ($registro->stockItems as $prenda)
                                                    <li>{{ $prenda->nombre }} ({{ $prenda->codigo }})</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                                Cancelar
                                            </button>
                                            <button type="button" class="btn btn-success"
                                                onclick="enviarFormulario({{ $registro->id }}, {{ $registro->tipo === 'alquiler' ? 3 : 2 }})">
                                                Confirmar
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Modal de advertencia por retraso -->
                            <div class="modal fade" id="retrasoModal-{{ $registro->id }}" tabindex="-1"
                                aria-labelledby="retrasoModalLabel-{{ $registro->id }}" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header bg-warning text-dark">
                                            <h5 class="modal-title" id="retrasoModalLabel-{{ $registro->id }}">
                                                <i class="fas fa-exclamation-triangle me-2"></i>
                                                Prenda con Retraso Detectado
                                            </h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="alert alert-warning">
                                                <strong>¡Atención!</strong> Esta prenda tiene un retraso de 
                                                <strong>{{ $diasRetraso }} día{{ $diasRetraso > 1 ? 's' : '' }}</strong> 
                                                en su devolución.
                                            </div>
                                            <p>
                                                <strong>Cliente:</strong> {{ $registro->cliente->nombre }}<br>
                                                <strong>Fecha de vencimiento:</strong> {{ \Carbon\Carbon::parse($registro->fecha_fin)->format('d/m/Y') }}<br>
                                                <strong>Fecha actual:</strong> {{ \Carbon\Carbon::now()->format('d/m/Y') }}
                                            </p>
                                            <p>Se recomienda revisar las multas aplicables antes de procesar la devolución.</p>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                                Cancelar
                                            </button>
                                            <a href="{{ route('devoluciones.calcular-multas', $registro->id) }}" 
                                               class="btn btn-warning">
                                                <i class="fas fa-calculator me-2"></i>Ver Multa
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
<script>
// Función principal que verifica si hay retraso antes de procesar
function verificarRetrasoYProcesar(id, diasRetraso, estado) {
    console.log('Verificando retraso para ID:', id, 'Días de retraso:', diasRetraso);
    
    if (diasRetraso > 0) {
        // Hay retraso, mostrar modal de advertencia
        const retrasoModal = new bootstrap.Modal(document.getElementById('retrasoModal-' + id));
        retrasoModal.show();
    } else {
        // No hay retraso, mostrar modal de confirmación normal
        const confirmModal = new bootstrap.Modal(document.getElementById('confirmModal-' + id));
        confirmModal.show();
    }
}

// Función para procesar cuando hay retraso (desde el modal de advertencia)
function procesarConRetraso(id, estado) {
    // Cerrar modal de retraso
    const retrasoModal = bootstrap.Modal.getInstance(document.getElementById('retrasoModal-' + id));
    if (retrasoModal) {
        retrasoModal.hide();
    }
    
    // Mostrar confirmación adicional
    Swal.fire({
        title: '¿Procesar con retraso?',
        text: 'Se aplicarán las multas correspondientes por el retraso en la devolución.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, procesar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            enviarFormulario(id, estado);
        }
    });
}

// Función original para enviar el formulario
async function enviarFormulario(id, estado) {
    console.log('=== INICIO DEBUG ===');
    console.log('ID:', id, 'Estado:', estado);
    
    try {
        // Cerrar cualquier modal abierto
        const confirmModal = bootstrap.Modal.getInstance(document.getElementById('confirmModal-' + id));
        if (confirmModal) {
            confirmModal.hide();
        }
        
        const retrasoModal = bootstrap.Modal.getInstance(document.getElementById('retrasoModal-' + id));
        if (retrasoModal) {
            retrasoModal.hide();
        }
        
        // Construir URL
        const url = `/devoluciones/actualizar-estado/${id}`;
        console.log('URL:', url);
        
        // Datos a enviar
        const data = {
            estado: estado,
            _token: '{{ csrf_token() }}'
        };
        console.log('Datos a enviar:', data);
        
        // Mostrar loading
        Swal.fire({
            title: 'Procesando...',
            text: 'Marcando como entregado',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        // Hacer la petición
        const response = await fetch(url, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(data)
        });
        
        console.log('Response status:', response.status);
        console.log('Response headers:', response.headers);
        
        const responseText = await response.text();
        console.log('Response text:', responseText);
        
        let responseData;
        try {
            responseData = JSON.parse(responseText);
        } catch (e) {
            console.error('No se pudo parsear JSON:', e);
            throw new Error('Respuesta no válida del servidor');
        }
        
        console.log('Response data:', responseData);
        
        if (response.ok && responseData.success) {
            Swal.fire({
                icon: 'success',
                title: 'Éxito',
                text: responseData.message || 'Prenda marcada como entregada correctamente',
                timer: 2000,
                showConfirmButton: false
            }).then(() => {
                window.location.reload();
            });
        } else {
            throw new Error(responseData.message || 'Error desconocido');
        }
        
    } catch (error) {
        console.error('=== ERROR ===');
        console.error('Error completo:', error);
        console.error('Stack trace:', error.stack);
        
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: error.message || 'Hubo un problema al procesar la devolución'
        });
    }
    
    console.log('=== FIN DEBUG ===');
}
</script> 
@endpush
