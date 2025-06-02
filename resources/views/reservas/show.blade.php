{{-- Archivo: resources/views/reservas/show.blade.php --}}
@extends('template')

@section('title', 'Detalle de Reserva')

@push('css')
<style>
    .info-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 15px;
        padding: 20px;
        margin-bottom: 20px;
    }
    .detail-card {
        border: 1px solid #dee2e6;
        border-radius: 10px;
        padding: 15px;
        margin-bottom: 15px;
        background-color: #f8f9fa;
    }
    .badge-estado {
        font-size: 0.9rem;
        padding: 8px 12px;
    }
    .amount-display {
        font-size: 1.2rem;
        font-weight: bold;
    }
</style>
@endpush

@section('content')

<div class="container-fluid px-4">
    <h1 class="mt-4 text-center">Detalle de Reserva #{{ $reserva->id }}</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item"><a href="{{ route('reservas.index') }}">Reservas</a></li>
        <li class="breadcrumb-item active">Detalle</li>
    </ol>

    <div class="row">
        <div class="col-md-4">
            <div class="info-card">
                <h5 class="mb-3"><i class="fas fa-info-circle me-2"></i>Información General</h5>
                <div class="mb-2">
                    <strong>Cliente:</strong><br>
                    {{ $reserva->cliente->nombre }}
                </div>
                <div class="mb-2">
                    <strong>Estado:</strong><br>
                    @switch($reserva->estado)
                        @case('pendiente')
                            <span class="badge bg-warning badge-estado">Pendiente</span>
                            @break
                        @case('confirmada')
                            <span class="badge bg-success badge-estado">Confirmada</span>
                            @break
                        @case('entregada')
                            <span class="badge bg-primary badge-estado">Entregada</span>
                            @break
                        @case('cancelada')
                            <span class="badge bg-danger badge-estado">Cancelada</span>
                            @break
                    @endswitch
                </div>
                <div class="mb-2">
                    <strong>Fecha de Reserva:</strong><br>
                    {{ $reserva->fecha_reserva->format('d/m/Y') }}
                </div>
                @if($reserva->alquiler)
                    <div class="mb-2">
                        <strong>Alquiler Generado:</strong><br>
                        <a href="{{ route('alquileres.show', $reserva->alquiler->id) }}" class="text-white">
                            Ver Alquiler #{{ $reserva->alquiler->id }}
                        </a>
                    </div>
                @endif
            </div>

            <div class="d-grid gap-2">
                @if($reserva->estado === 'confirmada')
                    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#entregarModal">
                        <i class="fas fa-handshake me-2"></i>Entregar/Alquilar
                    </button>
                    <a href="{{ route('reservas.edit', $reserva->id) }}" class="btn btn-warning">
                        <i class="fas fa-edit me-2"></i>Editar Reserva
                    </a>
                    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#cancelarModal">
                        <i class="fas fa-times me-2"></i>Cancelar Reserva
                    </button>
                @endif
                <a href="{{ route('reservas.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Volver al Listado
                </a>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-calendar-alt me-2"></i>Fechas Importantes</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 text-center">
                            <h6>Fecha de Reserva</h6>
                            <div class="badge bg-info fs-6 p-2">{{ $reserva->fecha_reserva->format('d/m/Y') }}</div>
                        </div>
                        <div class="col-md-4 text-center">
                            <h6>Fecha de Entrega</h6>
                            <div class="badge {{ $reserva->fecha_entrega_programada <= now() && $reserva->estado === 'confirmada' ? 'bg-danger' : 'bg-secondary' }} fs-6 p-2">
                                {{ $reserva->fecha_entrega_programada->format('d/m/Y') }}
                            </div>
                        </div>
                        <div class="col-md-4 text-center">
                            <h6>Fecha de Devolución</h6>
                            <div class="badge bg-primary fs-6 p-2">{{ $reserva->fecha_devolucion_programada->format('d/m/Y') }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-tshirt me-2"></i>Prendas Reservadas</h5>
                </div>
                <div class="card-body">
                    @foreach($reserva->stockItems as $item)
                        @php
                            $talle = \App\Models\TalleStock::find($item->pivot->talle_id);
                            $talleName = $talle ? $talle->talle : 'N/A';
                        @endphp
                        <div class="detail-card">
                            <div class="row align-items-center">
                                <div class="col-md-6">
                                    <h6 class="mb-1">{{ $item->nombre }}</h6>
                                    <small class="text-muted">{{ $item->codigo }}</small>
                                </div>
                                <div class="col-md-3 text-center">
                                    <small class="text-muted">Talle</small><br>
                                    <strong>{{ $talleName }}</strong>
                                </div>
                                <div class="col-md-3 text-center">
                                    <small class="text-muted">Cantidad</small><br>
                                    <strong>{{ $item->pivot->cantidad }}</strong>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-money-bill-wave me-2"></i>Información Financiera</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="detail-card">
                                <h6>Alquiler</h6>
                                <p><strong>Monto Total:</strong> ₲ {{ number_format($reserva->monto_total, 0, ',', '.') }}</p>
                                <p><strong>Seña Pagada:</strong> ₲ {{ number_format($reserva->seña_alquiler, 0, ',', '.') }}</p>
                                <p><strong>Saldo:</strong> <span class="amount-display text-primary">₲ {{ number_format($reserva->saldo_alquiler, 0, ',', '.') }}</span></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="detail-card">
                                <h6>Garantía</h6>
                                <p><strong>Garantía Total:</strong> ₲ {{ number_format($reserva->garantia_total, 0, ',', '.') }}</p>
                                <p><strong>Seña Pagada:</strong> ₲ {{ number_format($reserva->seña_garantia, 0, ',', '.') }}</p>
                                <p><strong>Saldo:</strong> <span class="amount-display text-success">₲ {{ number_format($reserva->saldo_garantia, 0, ',', '.') }}</span></p>
                            </div>
                        </div>
                    </div>
                    
                    @if($reserva->estado === 'confirmada')
                        <div class="alert alert-info text-center">
                            <h5 class="mb-0">
                                <strong>TOTAL A COBRAR EN ENTREGA: ₲ {{ number_format($reserva->total_a_cobrar, 0, ',', '.') }}</strong>
                            </h5>
                        </div>
                    @endif
                </div>
            </div>

            @if($reserva->observaciones)
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-sticky-note me-2"></i>Observaciones</h5>
                    </div>
                    <div class="card-body">
                        <p class="mb-0">{{ $reserva->observaciones }}</p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

@if($reserva->estado === 'confirmada')
    <div class="modal fade" id="entregarModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmar Entrega de Reserva</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <h6><i class="fas fa-money-bill me-2"></i>Resumen de Cobro</h6>
                        <p><strong>TOTAL A COBRAR HOY: ₲ {{ number_format($reserva->total_a_cobrar, 0, ',', '.') }}</strong></p>
                    </div>

                    <form action="{{ route('reservas.convertir-alquiler', $reserva->id) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="fecha_devolucion" class="form-label">Fecha de Devolución</label>
                            <input type="date" class="form-control" id="fecha_devolucion" name="fecha_devolucion" 
                                   value="{{ $reserva->fecha_devolucion_programada->format('Y-m-d') }}" required>
                        </div>
                        <div class="mb-3">
                            <label for="observaciones_entrega" class="form-label">Observaciones de Entrega</label>
                            <textarea class="form-control" id="observaciones_entrega" name="observaciones_entrega" 
                                      rows="3" placeholder="Observaciones adicionales (opcional)"></textarea>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-success">Confirmar Entrega</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="cancelarModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Cancelar Reserva</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>¿Está seguro que desea cancelar esta reserva?</p>
                    <div class="alert alert-warning">
                        <strong>Esta acción liberará las prendas reservadas y no se puede deshacer.</strong>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No, mantener</button>
                    <form action="{{ route('reservas.cancelar', $reserva->id) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn btn-danger">Sí, cancelar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endif

@endsection