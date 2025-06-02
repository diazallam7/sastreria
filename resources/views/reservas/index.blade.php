{{-- Archivo: resources/views/reservas/index.blade.php --}}
@extends('template')

@section('title', 'Reservas')

@push('css')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" rel="stylesheet" type="text/css">
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
                timer: 1500
            });
        </script>
    @endif

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
        <h1 class="mt-4 text-center">Reservas</h1>
        <ol class="breadcrumb mb-4">
            <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
            <li class="breadcrumb-item active">Reservas</li>
        </ol>
        <div class="mb-4">
            <div class="row">
                <div class="col-md-8">
                    <a href="{{ route('reservas.create') }}" class="btn btn-primary">Nueva Reserva</a>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-table me-2"></i> Listado de Reservas
            </div>
            <div class="card-body">
                <table id="datatablesSimple" class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Cliente</th>
                            <th>Prendas</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($reservas as $reserva)
                            <tr
                                class="{{ $reserva->fecha_entrega_programada <= now() && $reserva->estado === 'confirmada' ? 'table-warning' : '' }}">
                                <td>{{ $reserva->id }}</td>
                                <td>{{ $reserva->cliente->nombre }}</td>
                                <td>
                                    <ul class="list-unstyled mb-0">
                                        @foreach ($reserva->stockItems as $prenda)
                                            @php
                                                $talleId = $prenda->pivot->talle_id;
                                                $talle = \App\Models\TalleStock::find($talleId);
                                                $talleName = $talle ? $talle->talle : 'N/A';
                                            @endphp
                                            <li>{{ $prenda->nombre }} ({{ $prenda->codigo }}) - Talle: {{ $talleName }}
                                            </li>
                                        @endforeach
                                    </ul>
                                </td>

                                <td>
                                    @switch($reserva->estado)
                                        @case('pendiente')
                                            <span class="badge bg-warning">Pendiente</span>
                                        @break

                                        @case('confirmada')
                                            <span class="badge bg-success">Activo</span>
                                        @break

                                        @case('entregada')
                                            <span class="badge bg-primary">Completado</span>
                                        @break

                                        @case('cancelada')
                                            <span class="badge bg-danger">Cancelada</span>
                                        @break
                                    @endswitch
                                </td>
                                <td>
                                    <div class="btn-group" role="group" aria-label="Basic actions">
                                        @if($reserva->estado === 'cancelada')
                                            {{-- Botón para ver detalles de cancelación --}}
                                            <button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal"
                                                data-bs-target="#verCancelacionModal-{{ $reserva->id }}">
                                                <i class="fas fa-eye me-1"></i>Ver Cancelación
                                            </button>
                                        @else
                                            <a href="{{ route('reservas.show', $reserva->id) }}" class="btn btn-info btn-sm">Ver</a>
                                            
                                            @if($reserva->estado === 'confirmada')
                                                <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal"
                                                    data-bs-target="#entregarModal-{{ $reserva->id }}">Alquilar</button>
                                                
                                                <a href="{{ route('reservas.edit', $reserva->id) }}"
                                                    class="btn btn-warning btn-sm">Editar</a>
                                                
                                                <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal"
                                                    data-bs-target="#cancelarModal-{{ $reserva->id }}">Cancelar</button>
                                            @endif
                                        @endif
                                    </div>
                                </td>
                            </tr>

                            {{-- Modal para ver detalles de cancelación --}}
                            @if($reserva->estado === 'cancelada')
                                <div class="modal fade" id="verCancelacionModal-{{ $reserva->id }}" tabindex="-1"
                                    aria-labelledby="verCancelacionModalLabel-{{ $reserva->id }}" aria-hidden="true">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header bg-danger text-white">
                                                <h5 class="modal-title" id="verCancelacionModalLabel-{{ $reserva->id }}">
                                                    <i class="fas fa-times-circle me-2"></i>Detalles de Cancelación - Reserva #{{ $reserva->id }}
                                                </h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <!-- Información básica -->
                                                <div class="row mb-4">
                                                    <div class="col-md-6">
                                                        <h6><i class="fas fa-user me-2"></i>Información del Cliente</h6>
                                                        <p><strong>Cliente:</strong> {{ $reserva->cliente->nombre }}</p>
                                                        <p><strong>Fecha de reserva:</strong> {{ $reserva->fecha_reserva->format('d/m/Y') }}</p>
                                                        <p><strong>Fecha de cancelación:</strong> {{ $reserva->updated_at->format('d/m/Y H:i') }}</p>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <h6><i class="fas fa-tshirt me-2"></i>Prendas Reservadas</h6>
                                                        <ul class="list-unstyled">
                                                            @foreach($reserva->stockItems as $item)
                                                                @php
                                                                    $talle = \App\Models\TalleStock::find($item->pivot->talle_id);
                                                                @endphp
                                                                <li>• {{ $item->nombre }} - Talle: {{ $talle ? $talle->talle : 'N/A' }} (Cant: {{ $item->pivot->cantidad }})</li>
                                                            @endforeach
                                                        </ul>
                                                    </div>
                                                </div>

                                                <!-- Resumen financiero -->
                                                <div class="card border-info mb-3">
                                                    <div class="card-header bg-info text-white">
                                                        <h6 class="mb-0"><i class="fas fa-money-bill-wave me-2"></i>Resumen Financiero</h6>
                                                    </div>
                                                    <div class="card-body">
                                                        @php
                                                            $totalRecibido = $reserva->seña_garantia + $reserva->seña_alquiler;
                                                            $montoDevuelto = $reserva->seña_devuelta ?? 0;
                                                            $ingresoNeto = $totalRecibido - $montoDevuelto;
                                                        @endphp
                                                        <div class="row">
                                                            <div class="col-md-4 text-center">
                                                                <h6>Total Recibido</h6>
                                                                <div class="h5 text-primary">₲ {{ number_format($totalRecibido, 0, ',', '.') }}</div>
                                                                <small class="text-muted">
                                                                    Seña garantía: ₲ {{ number_format($reserva->seña_garantia, 0, ',', '.') }}<br>
                                                                    Seña alquiler: ₲ {{ number_format($reserva->seña_alquiler, 0, ',', '.') }}
                                                                </small>
                                                            </div>
                                                            <div class="col-md-4 text-center">
                                                                <h6>Monto Devuelto</h6>
                                                                <div class="h5 text-warning">₲ {{ number_format($montoDevuelto, 0, ',', '.') }}</div>
                                                                @if($montoDevuelto < $totalRecibido)
                                                                    <small class="text-success">Devolución parcial</small>
                                                                @elseif($montoDevuelto == $totalRecibido)
                                                                    <small class="text-info">Devolución completa</small>
                                                                @else
                                                                    <small class="text-danger">Error en cálculo</small>
                                                                @endif
                                                            </div>
                                                            <div class="col-md-4 text-center">
                                                                <h6>Ingreso Neto</h6>
                                                                <div class="h5 {{ $ingresoNeto > 0 ? 'text-success' : ($ingresoNeto == 0 ? 'text-secondary' : 'text-danger') }}">
                                                                    ₲ {{ number_format($ingresoNeto, 0, ',', '.') }}
                                                                </div>
                                                                @if($ingresoNeto > 0)
                                                                    <small class="text-success">Ganancia retenida</small>
                                                                @elseif($ingresoNeto == 0)
                                                                    <small class="text-secondary">Sin ganancia</small>
                                                                @else
                                                                    <small class="text-danger">Pérdida</small>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Motivo y observaciones -->
                                                @if($reserva->motivo_devolucion || $reserva->observaciones)
                                                    <div class="card border-secondary">
                                                        <div class="card-header">
                                                            <h6 class="mb-0"><i class="fas fa-comment-alt me-2"></i>Detalles de la Cancelación</h6>
                                                        </div>
                                                        <div class="card-body">
                                                            @if($reserva->motivo_devolucion)
                                                                <p><strong>Motivo:</strong> 
                                                                    <span class="badge bg-secondary">{{ ucfirst(str_replace('_', ' ', $reserva->motivo_devolucion)) }}</span>
                                                                </p>
                                                            @endif
                                                            
                                                            @if($reserva->observaciones)
                                                                <p><strong>Observaciones:</strong></p>
                                                                <div class="bg-light p-3 rounded">
                                                                    {{ $reserva->observaciones }}
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @if ($reserva->estado === 'confirmada')
                                <div class="modal fade" id="entregarModal-{{ $reserva->id }}" tabindex="-1"
                                    aria-labelledby="entregarModalLabel-{{ $reserva->id }}" aria-hidden="true">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="entregarModalLabel-{{ $reserva->id }}">
                                                    Confirmar Entrega de Reserva
                                                </h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                    aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="row mb-3">
                                                    <div class="col-md-6">
                                                        <h6>Información del Cliente</h6>
                                                        <p><strong>Cliente:</strong> {{ $reserva->cliente->nombre }}</p>
                                                        <p><strong>Reserva:</strong>
                                                            {{ $reserva->fecha_reserva->format('d/m/Y') }}</p>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <h6>Prendas Reservadas</h6>
                                                        <ul class="list-unstyled">
                                                            @foreach ($reserva->stockItems as $item)
                                                                <li>• {{ $item->nombre }} ({{ $item->pivot->cantidad }})
                                                                </li>
                                                            @endforeach
                                                        </ul>
                                                    </div>
                                                </div>

                                                <div class="alert alert-info">
                                                    <h6><i class="fas fa-money-bill me-2"></i>Resumen Financiero</h6>
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <p><strong>Total alquiler:</strong> ₲
                                                                {{ number_format($reserva->monto_total, 0, ',', '.') }}</p>
                                                            <p><strong>Seña alquiler:</strong> ₲
                                                                {{ number_format($reserva->seña_alquiler, 0, ',', '.') }}
                                                            </p>
                                                            <p><strong>Saldo alquiler:</strong> ₲
                                                                {{ number_format($reserva->saldo_alquiler, 0, ',', '.') }}
                                                            </p>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <p><strong>Garantía total:</strong> ₲
                                                                {{ number_format($reserva->garantia_total, 0, ',', '.') }}
                                                            </p>
                                                            <p><strong>Seña garantía:</strong> ₲
                                                                {{ number_format($reserva->seña_garantia, 0, ',', '.') }}
                                                            </p>
                                                            <p><strong>Saldo garantía:</strong> ₲
                                                                {{ number_format($reserva->saldo_garantia, 0, ',', '.') }}
                                                            </p>
                                                        </div>
                                                    </div>
                                                    <hr>
                                                    <h5 class="text-success"><strong>TOTAL A COBRAR HOY: ₲
                                                            {{ number_format($reserva->total_a_cobrar, 0, ',', '.') }}</strong>
                                                    </h5>
                                                </div>

                                                <form action="{{ route('reservas.convertir-alquiler', $reserva->id) }}"
                                                    method="POST">
                                                    @csrf
                                                    <div class="mb-3">
                                                        <label for="fecha_devolucion{{ $reserva->id }}"
                                                            class="form-label">Fecha de Devolución</label>
                                                        <input type="date" class="form-control"
                                                            id="fecha_devolucion{{ $reserva->id }}"
                                                            name="fecha_devolucion"
                                                            value="{{ $reserva->fecha_devolucion_programada->format('Y-m-d') }}"
                                                            required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="observaciones_entrega{{ $reserva->id }}"
                                                            class="form-label">Observaciones de Entrega</label>
                                                        <textarea class="form-control" id="observaciones_entrega{{ $reserva->id }}" name="observaciones_entrega"
                                                            rows="3" placeholder="Observaciones adicionales (opcional)"></textarea>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary"
                                                            data-bs-dismiss="modal">Cancelar</button>
                                                        <button type="submit" class="btn btn-success">Confirmar
                                                            Entrega</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Reemplazar el modal de cancelación existente --}}
                                <div class="modal fade" id="cancelarModal-{{ $reserva->id }}" tabindex="-1"
                                    aria-labelledby="cancelarModalLabel-{{ $reserva->id }}" aria-hidden="true">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="cancelarModalLabel-{{ $reserva->id }}">
                                                    Cancelar Reserva</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                    aria-label="Close"></button>
                                            </div>
                                            <form action="{{ route('reservas.cancelar', $reserva->id) }}" method="POST">
                                                @csrf
                                                @method('PATCH')
                                                <div class="modal-body">
                                                    <div class="alert alert-warning">
                                                        <h6><i class="fas fa-exclamation-triangle me-2"></i>Información de
                                                            la Reserva</h6>
                                                        <p><strong>Cliente:</strong> {{ $reserva->cliente->nombre }}</p>
                                                        <p><strong>Seña garantía recibida:</strong> ₲
                                                            {{ number_format($reserva->seña_garantia, 0, ',', '.') }}</p>
                                                        <p><strong>Seña alquiler recibida:</strong> ₲
                                                            {{ number_format($reserva->seña_alquiler, 0, ',', '.') }}</p>
                                                        <p><strong>Total recibido:</strong> ₲
                                                            {{ number_format($reserva->seña_garantia + $reserva->seña_alquiler, 0, ',', '.') }}
                                                        </p>
                                                    </div>

                                                    <!-- NUEVO: Sección para editar devolución -->
                                                    <div class="card border-info">
                                                        <div class="card-header bg-info text-white">
                                                            <h6 class="mb-0"><i
                                                                    class="fas fa-money-bill me-2"></i>Devolución de Señas
                                                            </h6>
                                                        </div>
                                                        <div class="card-body">
                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <label for="seña_devuelta_{{ $reserva->id }}"
                                                                        class="form-label">Monto Total a Devolver</label>
                                                                    <div class="input-group">
                                                                        <span class="input-group-text">₲</span>
                                                                        <input type="number" class="form-control"
                                                                            id="seña_devuelta_{{ $reserva->id }}"
                                                                            name="seña_devuelta"
                                                                            value="{{ $reserva->seña_garantia + $reserva->seña_alquiler }}"
                                                                            min="0" step="1000">
                                                                    </div>
                                                                    <small class="text-muted">Máximo: ₲
                                                                        {{ number_format($reserva->seña_garantia + $reserva->seña_alquiler, 0, ',', '.') }}</small>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <label for="motivo_devolucion_{{ $reserva->id }}"
                                                                        class="form-label">Motivo de la Devolución</label>
                                                                    <select class="form-select"
                                                                        id="motivo_devolucion_{{ $reserva->id }}"
                                                                        name="motivo_devolucion" required>
                                                                        <option value="">Seleccionar motivo</option>
                                                                        <option value="cancelacion_cliente">Cancelación por
                                                                            cliente</option>
                                                                        <option value="falta_stock">Falta de stock</option>
                                                                        <option value="cortesia">Cortesía de la casa
                                                                        </option>
                                                                        <option value="error_reserva">Error en reserva
                                                                        </option>
                                                                        <option value="devolucion_parcial">Devolución
                                                                            parcial</option>
                                                                        <option value="no_devolucion">No se devuelve
                                                                            (política)</option>
                                                                        <option value="otro">Otro motivo</option>
                                                                    </select>
                                                                </div>
                                                            </div>

                                                            <div class="row mt-3">
                                                                <div class="col-md-12">
                                                                    <label
                                                                        for="observaciones_cancelacion_{{ $reserva->id }}"
                                                                        class="form-label">Observaciones</label>
                                                                    <textarea class="form-control" id="observaciones_cancelacion_{{ $reserva->id }}" name="observaciones_cancelacion"
                                                                        rows="2" placeholder="Detalles adicionales sobre la cancelación"></textarea>
                                                                </div>
                                                            </div>

                                                            <!-- Resumen de la operación -->
                                                            <div class="alert alert-info mt-3 mb-0">
                                                                <h6>Resumen de la Cancelación:</h6>
                                                                <p class="mb-1"><strong>Total recibido:</strong> ₲
                                                                    {{ number_format($reserva->seña_garantia + $reserva->seña_alquiler, 0, ',', '.') }}
                                                                </p>
                                                                <p class="mb-1"><strong>Total a devolver:</strong> <span
                                                                        id="resumen_devolver_{{ $reserva->id }}">₲
                                                                        {{ number_format($reserva->seña_garantia + $reserva->seña_alquiler, 0, ',', '.') }}</span>
                                                                </p>
                                                                <p class="mb-0"><strong>Diferencia (ingreso
                                                                        neto):</strong> <span
                                                                        id="resumen_diferencia_{{ $reserva->id }}">₲
                                                                        0</span></p>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <p class="text-danger mt-3"><strong>Esta acción liberará las prendas
                                                            reservadas y no se puede deshacer.</strong></p>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary"
                                                        data-bs-dismiss="modal">Cancelar</button>
                                                    <button type="submit" class="btn btn-danger">Confirmar
                                                        Cancelación</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/simple-datatables@latest" type="text/javascript"></script>
    <script src="{{ asset('js/datatables-simple-demo.js') }}"></script>


    <!-- JavaScript para cálculo automático en el modal -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Agregar event listener para cada modal de cancelación
            @foreach ($reservas as $reserva)
                @if ($reserva->estado === 'confirmada')
                    (function() {
                        const reservaId = {{ $reserva->id }};
                        const totalRecibido = {{ $reserva->seña_garantia + $reserva->seña_alquiler }};
                        const inputDevolver = document.getElementById('seña_devuelta_' + reservaId);
                        const resumenDevolver = document.getElementById('resumen_devolver_' + reservaId);
                        const resumenDiferencia = document.getElementById('resumen_diferencia_' + reservaId);

                        if (inputDevolver) {
                            inputDevolver.addEventListener('input', function() {
                                const montoDevolver = parseFloat(this.value) || 0;
                                const diferencia = totalRecibido - montoDevolver;

                                resumenDevolver.textContent = '₲ ' + montoDevolver.toLocaleString();
                                resumenDiferencia.textContent = '₲ ' + diferencia.toLocaleString();
                                resumenDiferencia.className = diferencia >= 0 ? 'text-success' :
                                    'text-danger';
                            });
                        }
                    })();
                @endif
            @endforeach
        });
    </script>
@endpush