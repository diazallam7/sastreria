@extends('template')

@section('title', 'Detalles del Cliente')

@push('css')
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
            padding: 10px 15px;
            margin-bottom: 10px;
            border: 1px solid #e9ecef;
            display: flex;
            justify-content: between;
            align-items: center;
        }
        .medida-label {
            font-weight: 600;
            color: #495057;
            min-width: 120px;
        }
        .medida-valor {
            color: #007bff;
            font-weight: 500;
        }
        .sin-medidas {
            text-align: center;
            color: #6c757d;
            font-style: italic;
            padding: 30px;
            background-color: #f8f9fa;
            border-radius: 10px;
            border: 2px dashed #dee2e6;
        }
        .prenda-section {
            margin-bottom: 30px;
        }
        .prenda-title {
            color: #495057;
            border-bottom: 2px solid #007bff;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .badge-custom {
            font-size: 0.9rem;
            padding: 8px 12px;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid px-4">
        <h1 class="mt-4 text-center">Detalles del Cliente</h1>
        <ol class="breadcrumb mb-4">
            <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
            <li class="breadcrumb-item"><a href="{{ route('clientes.index') }}">Clientes</a></li>
            <li class="breadcrumb-item active">{{ $cliente->nombre }}</li>
        </ol>

        <!-- Información Principal del Cliente -->
        <div class="info-card">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2 class="mb-3">
                        <i class="fas fa-user-circle me-3"></i>{{ $cliente->nombre }}
                    </h2>
                    <div class="row">
                        <div class="col-md-6">
                            <p class="mb-2">
                                <i class="fas fa-phone me-2"></i>
                                <strong>Teléfono:</strong> {{ $cliente->telefono ?? 'No registrado' }}
                            </p>
                            <p class="mb-2">
                                <i class="fas fa-id-card me-2"></i>
                                <strong>Cédula:</strong> {{ $cliente->correo ?? 'No registrada' }}
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-2">
                                <i class="fas fa-map-marker-alt me-2"></i>
                                <strong>Dirección:</strong> {{ $cliente->direccion ?? 'No registrada' }}
                            </p>
                            <p class="mb-2">
                                <i class="fas fa-calendar me-2"></i>
                                <strong>Registrado:</strong> {{ $cliente->created_at->format('d/m/Y') }}
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 text-end">
                    <span class="badge {{ $cliente->estado ? 'bg-success' : 'bg-secondary' }} badge-custom">
                        <i class="fas {{ $cliente->estado ? 'fa-check-circle' : 'fa-times-circle' }} me-2"></i>
                        {{ $cliente->estado ? 'Cliente Activo' : 'Cliente Inactivo' }}
                    </span>
                    <div class="mt-3">
                        <a href="{{ route('clientes.edit', $cliente) }}" class="btn btn-light btn-sm me-2">
                            <i class="fas fa-edit me-1"></i>Editar
                        </a>
                        <a href="{{ route('clientes.index') }}" class="btn btn-outline-light btn-sm">
                            <i class="fas fa-arrow-left me-1"></i>Volver
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Medidas Básicas -->
            <div class="col-md-6">
                <div class="medidas-card">
                    <h4 class="text-success mb-4">
                        <i class="fas fa-ruler me-2"></i>Medidas Básicas (Alquiler/Reserva)
                    </h4>
                    
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
                            <i class="fas fa-ruler-combined fa-2x mb-3 text-muted"></i>
                            <p class="mb-0">No hay medidas básicas registradas</p>
                            <small>Útiles para alquileres y reservas rápidas</small>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Medidas Completas -->
            <div class="col-md-6">
                <div class="medidas-card">
                    <h4 class="text-warning mb-4">
                        <i class="fas fa-cut me-2"></i>Medidas Completas (Confección)
                    </h4>
                    
                    @if($cliente->tieneMedidasCompletas())
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            Cliente con medidas completas registradas para confección
                        </div>
                        
                        <!-- Botón para ver medidas detalladas -->
                        <button class="btn btn-outline-primary w-100" type="button" data-bs-toggle="collapse" 
                                data-bs-target="#medidasDetalladas" aria-expanded="false">
                            <i class="fas fa-eye me-2"></i>Ver Medidas Detalladas
                        </button>
                    @else
                        <div class="sin-medidas">
                            <i class="fas fa-cut fa-2x mb-3 text-muted"></i>
                            <p class="mb-0">No hay medidas completas registradas</p>
                            <small>Necesarias para confección de prendas</small>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Medidas Detalladas (Colapsable) -->
        @if($cliente->tieneMedidasCompletas())
            <div class="collapse" id="medidasDetalladas">
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
                                    <div class="prenda-section">
                                        <h5 class="prenda-title">
                                            <i class="fas fa-vest me-2"></i>SACO
                                        </h5>
                                        @foreach($medidasSaco as $key => $valor)
                                            @if($valor)
                                                <div class="medida-item">
                                                    <span class="medida-label">{{ ucfirst(str_replace('_', ' ', $key)) }}:</span>
                                                    <span class="medida-valor">{{ $valor }} cm</span>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <!-- Medidas de Pantalón -->
                            @php $medidasPantalon = $cliente->getMedidasPantalonCompletas(); @endphp
                            @if(array_filter($medidasPantalon))
                                <div class="col-md-4">
                                    <div class="prenda-section">
                                        <h5 class="prenda-title">
                                            <i class="fas fa-user-tie me-2"></i>PANTALÓN
                                        </h5>
                                        @foreach($medidasPantalon as $key => $valor)
                                            @if($valor)
                                                <div class="medida-item">
                                                    <span class="medida-label">{{ ucfirst(str_replace('_', ' ', $key)) }}:</span>
                                                    <span class="medida-valor">{{ $valor }} cm</span>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <!-- Medidas de Chaleco -->
                            @php $medidasChaleco = $cliente->getMedidasChalecoCompletas(); @endphp
                            @if(array_filter($medidasChaleco))
                                <div class="col-md-4">
                                    <div class="prenda-section">
                                        <h5 class="prenda-title">
                                            <i class="fas fa-tshirt me-2"></i>CHALECO
                                        </h5>
                                        @foreach($medidasChaleco as $key => $valor)
                                            @if($valor)
                                                <div class="medida-item">
                                                    <span class="medida-label">{{ ucfirst(str_replace('_', ' ', $key)) }}:</span>
                                                    <span class="medida-valor">{{ $valor }} cm</span>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
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

        <!-- Acciones -->
        <div class="row mt-4">
            <div class="col-12 text-center">
                <a href="{{ route('clientes.edit', $cliente) }}" class="btn btn-primary btn-lg me-3">
                    <i class="fas fa-edit me-2"></i>Editar Cliente
                </a>
                <a href="{{ route('clientes.index') }}" class="btn btn-secondary btn-lg">
                    <i class="fas fa-list me-2"></i>Ver Todos los Clientes
                </a>
            </div>
        </div>
    </div>
@endsection
