@extends('template')

@section('title', 'Historial de Cliente')

@push('css')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .info-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .medidas-card {
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            border-left: 4px solid #007bff;
        }
        .medida-item {
            background-color: white;
            border-radius: 8px;
            padding: 8px 12px;
            margin-bottom: 8px;
            border: 1px solid #e9ecef;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .medida-label {
            font-weight: 600;
            color: #495057;
            min-width: 100px;
        }
        .medida-valor {
            color: #007bff;
            font-weight: 500;
        }
        .sin-medidas {
            text-align: center;
            color: #6c757d;
            font-style: italic;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 10px;
            border: 2px dashed #dee2e6;
        }
        .prenda-list {
            margin-bottom: 0;
        }
        .activity-card {
            border-left: 4px solid #007bff;
            margin-bottom: 15px;
        }
        .activity-card.alquiler {
            border-left-color: #28a745;
        }
        .activity-card.reserva {
            border-left-color: #ffc107;
        }
        .activity-card.venta {
            border-left-color: #dc3545;
        }
        .badge-custom {
            font-size: 0.9rem;
            padding: 8px 12px;
        }
        .timeline-item {
            border-left: 2px solid #e9ecef;
            padding-left: 20px;
            margin-bottom: 20px;
            position: relative;
        }
        .timeline-item::before {
            content: '';
            position: absolute;
            left: -6px;
            top: 10px;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background-color: #007bff;
        }
        .timeline-item.alquiler::before {
            background-color: #28a745;
        }
        .timeline-item.reserva::before {
            background-color: #ffc107;
        }
        .timeline-item.venta::before {
            background-color: #dc3545;
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
            timer: 1500
        });
    </script>
@endif

<div class="container-fluid px-4">
    <h1 class="mt-4 text-center">Historial de {{ $cliente->nombre }}</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item"><a href="{{ route('clientes.index') }}">Clientes</a></li>
        <li class="breadcrumb-item active">Historial de {{ $cliente->nombre }}</li>
    </ol>
    <!-- Sección de Medidas -->
    <div class="row mb-4">
        <div class="col-12">
            <h3 class="text-primary mb-3">
                <i class="fas fa-ruler-combined me-2"></i>Medidas del Cliente
            </h3>
        </div>
        
        <!-- Medidas Básicas -->
        <div class="col-md-6">
            <div class="medidas-card">
                <h5 class="text-success mb-3">
                    <i class="fas fa-ruler me-2"></i>Medidas Básicas (Alquiler/Reserva)
                </h5>
                
                @if($cliente->tieneMedidasBasicas())
                    @if($cliente->medida_saco_basica)
                        <div class="medida-item">
                            <span class="medida-label">Saco:</span>
                            <span class="medida-valor">{{ $cliente->medida_saco_basica }}</span>
                        </div>
                    @endif
                    
                    @if($cliente->medida_pantalon_basica)
                        <div class="medida-item">
                            <span class="medida-label">Pantalón:</span>
                            <span class="medida-valor">{{ $cliente->medida_pantalon_basica }}</span>
                        </div>
                    @endif
                @else
                    <div class="sin-medidas">
                        <i class="fas fa-ruler-combined fa-2x mb-2 text-muted"></i>
                        <p class="mb-0">No hay medidas básicas registradas</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Medidas Completas -->
        <div class="col-md-6">
            <div class="medidas-card">
                <h5 class="text-warning mb-3">
                    <i class="fas fa-cut me-2"></i>Medidas Completas (Confección)
                </h5>
                
                @if($cliente->tieneMedidasCompletas())
                    <div class="alert alert-info mb-3">
                        <i class="fas fa-info-circle me-2"></i>
                        Cliente con medidas completas para confección
                    </div>
                    
                    <!-- Mostrar algunas medidas principales -->
                    @php 
                        $medidasSaco = $cliente->getMedidasSacoCompletas();
                        $medidasPantalon = $cliente->getMedidasPantalonCompletas();
                        $medidasChaleco = $cliente->getMedidasChalecoCompletas();
                    @endphp
                    
                    @if($medidasSaco['talle'])
                        <div class="medida-item">
                            <span class="medida-label">Saco - Talle:</span>
                            <span class="medida-valor">{{ $medidasSaco['talle'] }} cm</span>
                        </div>
                    @endif
                    
                    @if($medidasPantalon['largo'])
                        <div class="medida-item">
                            <span class="medida-label">Pantalón - Largo:</span>
                            <span class="medida-valor">{{ $medidasPantalon['largo'] }} cm</span>
                        </div>
                    @endif
                    
                    @if($medidasChaleco['talle'])
                        <div class="medida-item">
                            <span class="medida-label">Chaleco - Talle:</span>
                            <span class="medida-valor">{{ $medidasChaleco['talle'] }} cm</span>
                        </div>
                    @endif
                    
                    <button class="btn btn-outline-primary btn-sm w-100 mt-2" type="button" data-bs-toggle="collapse" 
                            data-bs-target="#medidasDetalladas" aria-expanded="false">
                        <i class="fas fa-eye me-2"></i>Ver Todas las Medidas
                    </button>
                @else
                    <div class="sin-medidas">
                        <i class="fas fa-cut fa-2x mb-2 text-muted"></i>
                        <p class="mb-0">No hay medidas completas registradas</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Medidas Detalladas (Colapsable) -->
    @if($cliente->tieneMedidasCompletas())
        <div class="collapse mb-4" id="medidasDetalladas">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-list-ul me-2"></i>Medidas Detalladas para Confección</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Medidas de Saco -->
                        @php $medidasSaco = $cliente->getMedidasSacoCompletas(); @endphp
                        @if(array_filter($medidasSaco))
                            <div class="col-md-4">
                                <h6 class="text-secondary mb-3">
                                    <i class="fas fa-vest me-2"></i>SACO
                                </h6>
                                @foreach($medidasSaco as $key => $valor)
                                    @if($valor)
                                        <div class="medida-item">
                                            <span class="medida-label">{{ ucfirst(str_replace('_', ' ', $key)) }}:</span>
                                            <span class="medida-valor">{{ $valor }} cm</span>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        @endif

                        <!-- Medidas de Pantalón -->
                        @php $medidasPantalon = $cliente->getMedidasPantalonCompletas(); @endphp
                        @if(array_filter($medidasPantalon))
                            <div class="col-md-4">
                                <h6 class="text-secondary mb-3">
                                    <i class="fas fa-user-tie me-2"></i>PANTALÓN
                                </h6>
                                @foreach($medidasPantalon as $key => $valor)
                                    @if($valor)
                                        <div class="medida-item">
                                            <span class="medida-label">{{ ucfirst(str_replace('_', ' ', $key)) }}:</span>
                                            <span class="medida-valor">{{ $valor }} cm</span>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        @endif

                        <!-- Medidas de Chaleco -->
                        @php $medidasChaleco = $cliente->getMedidasChalecoCompletas(); @endphp
                        @if(array_filter($medidasChaleco))
                            <div class="col-md-4">
                                <h6 class="text-secondary mb-3">
                                    <i class="fas fa-tshirt me-2"></i>CHALECO
                                </h6>
                                @foreach($medidasChaleco as $key => $valor)
                                    @if($valor)
                                        <div class="medida-item">
                                            <span class="medida-label">{{ ucfirst(str_replace('_', ' ', $key)) }}:</span>
                                            <span class="medida-valor">{{ $valor }} cm</span>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <!-- Observaciones -->
                    @if($cliente->observaciones_medidas)
                        <div class="mt-4">
                            <h6><i class="fas fa-sticky-note me-2"></i>Observaciones:</h6>
                            <div class="alert alert-light">
                                {{ $cliente->observaciones_medidas }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif

    <!-- Historial de Actividades -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <i class="fas fa-history me-2"></i>Historial de Actividades
        </div>
        <div class="card-body">
            @php
                // Crear un array con todas las actividades para ordenarlas cronológicamente
                $actividades = collect();
                
                // Agregar alquileres
                foreach($cliente->alquileres as $alquiler) {
                    $actividades->push([
                        'tipo' => 'alquiler',
                        'fecha' => $alquiler->fecha_inicio,
                        'data' => $alquiler
                    ]);
                }
                
                // Agregar reservas
                foreach($cliente->reservas as $reserva) {
                    $actividades->push([
                        'tipo' => 'reserva',
                        'fecha' => $reserva->fecha_reserva,
                        'data' => $reserva
                    ]);
                }
                
                // Agregar ventas
                foreach($cliente->ventas as $venta) {
                    $actividades->push([
                        'tipo' => 'venta',
                        'fecha' => $venta->fecha_venta,
                        'data' => $venta
                    ]);
                }
                
                // Ordenar por fecha descendente (más reciente primero)
                $actividades = $actividades->sortByDesc('fecha');
            @endphp

            @if($actividades->count() > 0)
                <div class="timeline">
                    @foreach($actividades as $actividad)
                        <div class="timeline-item {{ $actividad['tipo'] }}">
                            @if($actividad['tipo'] == 'alquiler')
                                @php $alquiler = $actividad['data']; @endphp
                                <div class="card activity-card alquiler">
                                    <div class="card-header bg-success text-white">
                                        <i class="fas fa-handshake me-2"></i>
                                        <strong>Alquiler</strong>
                                        <span class="float-end">{{ \Carbon\Carbon::parse($alquiler->fecha_inicio)->format('d/m/Y') }}</span>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <h6>Prendas Alquiladas:</h6>
                                                <ul class="list-unstyled">
                                                    @foreach($alquiler->stockItems as $stockItem)
                                                        <li>
                                                            <i class="fas fa-tshirt me-2 text-success"></i>
                                                            {{ $stockItem->nombre ?? 'Prenda no disponible' }}
                                                            @if($stockItem->pivot && $stockItem->pivot->talle_id)
                                                                - Talle: {{ optional(\App\Models\TalleStock::find($stockItem->pivot->talle_id))->talle ?? 'N/A' }}
                                                            @endif
                                                            - Cant: {{ $stockItem->pivot->cantidad ?? 1 }}
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                            <div class="col-md-6">
                                                <p><strong>Fecha Inicio:</strong> {{ \Carbon\Carbon::parse($alquiler->fecha_inicio)->format('d/m/Y') }}</p>
                                                <p><strong>Fecha Fin:</strong> {{ \Carbon\Carbon::parse($alquiler->fecha_fin)->format('d/m/Y') }}</p>
                                                <p><strong>Costo Total:</strong> ₲ {{ number_format($alquiler->costo_total, 0, ',', '.') }}</p>
                                                <p><strong>Garantía:</strong> ₲ {{ number_format($alquiler->garantia, 0, ',', '.') }}</p>
                                                <span class="badge {{ $alquiler->estado == 1 ? 'bg-primary' : 'bg-success' }}">
                                                    {{ $alquiler->estado == 1 ? 'Activo' : 'Completado' }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            @elseif($actividad['tipo'] == 'reserva')
                                @php $reserva = $actividad['data']; @endphp
                                <div class="card activity-card reserva">
                                    <div class="card-header bg-warning text-dark">
                                        <i class="fas fa-calendar-check me-2"></i>
                                        <strong>Reserva</strong>
                                        <span class="float-end">{{ \Carbon\Carbon::parse($reserva->fecha_reserva)->format('d/m/Y') }}</span>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <h6>Prendas Reservadas:</h6>
                                                <ul class="list-unstyled">
                                                    @foreach($reserva->stockItems as $stockItem)
                                                        <li>
                                                            <i class="fas fa-tshirt me-2 text-warning"></i>
                                                            {{ $stockItem->nombre ?? 'Prenda no disponible' }}
                                                            @if($stockItem->pivot && $stockItem->pivot->talle_id)
                                                                - Talle: {{ optional(\App\Models\TalleStock::find($stockItem->pivot->talle_id))->talle ?? 'N/A' }}
                                                            @endif
                                                            - Cant: {{ $stockItem->pivot->cantidad ?? 1 }}
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                            <div class="col-md-6">
                                                <p><strong>Fecha Reserva:</strong> {{ \Carbon\Carbon::parse($reserva->fecha_reserva)->format('d/m/Y') }}</p>
                                                <p><strong>Fecha Entrega:</strong> {{ \Carbon\Carbon::parse($reserva->fecha_entrega_programada)->format('d/m/Y') }}</p>
                                                <p><strong>Fecha Devolución:</strong> {{ \Carbon\Carbon::parse($reserva->fecha_devolucion_programada)->format('d/m/Y') }}</p>
                                                <p><strong>Monto Total:</strong> ₲ {{ number_format($reserva->monto_total, 0, ',', '.') }}</p>
                                                <p><strong>Seña Pagada:</strong> ₲ {{ number_format($reserva->seña_garantia + $reserva->seña_alquiler, 0, ',', '.') }}</p>
                                                <span class="badge bg-warning text-dark">
                                                    {{ $reserva->estado == 'pendiente' ? 'Pendiente' : ucfirst($reserva->estado) }}
                                                </span>
                                            </div>
                                        </div>
                                        @if($reserva->observaciones)
                                            <div class="mt-2">
                                                <small class="text-muted"><strong>Observaciones:</strong> {{ $reserva->observaciones }}</small>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                            @elseif($actividad['tipo'] == 'venta')
                                @php $venta = $actividad['data']; @endphp
                                <div class="card activity-card venta">
                                    <div class="card-header bg-danger text-white">
                                        <i class="fas fa-shopping-cart me-2"></i>
                                        <strong>Venta</strong>
                                        <span class="float-end">{{ \Carbon\Carbon::parse($venta->fecha_venta)->format('d/m/Y') }}</span>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <h6>Productos Vendidos:</h6>
                                                @if($venta->detalles && $venta->detalles->count() > 0)
                                                    <ul class="list-unstyled">
                                                        @foreach($venta->detalles as $detalle)
                                                            <li>
                                                                <i class="fas fa-tshirt me-2 text-danger"></i>
                                                                {{ $detalle->producto_nombre ?? 'Producto no disponible' }}
                                                                - Cant: {{ $detalle->cantidad ?? 1 }}
                                                                - ₲ {{ number_format($detalle->precio_unitario ?? 0, 0, ',', '.') }}
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                @else
                                                    <p class="text-muted">Información de productos no disponible</p>
                                                @endif
                                            </div>
                                            <div class="col-md-6">
                                                <p><strong>Fecha Venta:</strong> {{ \Carbon\Carbon::parse($venta->fecha_venta)->format('d/m/Y') }}</p>
                                                <p><strong>Precio Total:</strong> ₲ {{ number_format($venta->precio_total, 0, ',', '.') }}</p>
                                                <span class="badge bg-success">Completada</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No hay actividades registradas</h5>
                    <p class="text-muted">Este cliente aún no tiene alquileres, reservas o ventas registradas.</p>
                </div>
            @endif

            <!-- Botón Volver -->
            <div class="text-center mt-4">
                <a href="{{ route('clientes.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Volver a Clientes
                </a>
                <a href="{{ route('clientes.edit', $cliente) }}" class="btn btn-primary ms-2">
                    <i class="fas fa-edit me-1"></i> Editar Cliente
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
@endpush
