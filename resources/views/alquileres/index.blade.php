{{-- Archivo: resources/views/alquileres/index.blade.php --}}
@extends('template')

@section('title', 'Alquileres')

@push('css')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" rel="stylesheet" type="text/css">
    <style>
        .table-responsive {
            max-height: 250px;
            overflow-y: auto;
        }
        .card-header h6 {
            margin: 0;
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

    @if (session('error'))
        <script>
            Swal.fire({
                icon: 'error',
                title: '{{ session('error') }}',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 2000
            });
        </script>
    @endif

    <div class="container-fluid px-4">
        <h1 class="mt-4 text-center">Alquileres</h1>
        <ol class="breadcrumb mb-4">
            <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
            <li class="breadcrumb-item active">Alquileres</li>
        </ol>

        <div class="mb-4">
            @can('crear-alquiler')
                <a href="{{ route('alquileres.create') }}" class="btn btn-primary">Registrar Nuevo Alquiler</a>
            @endcan
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-table me-2"></i> Listado de Alquileres
            </div>
            <div class="card-body">
                <table id="datatablesSimple" class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Cliente</th>
                            <th>Fecha Inicio</th>
                            <th>Fecha Fin</th>
                            <th>Costo Total</th>
                            <th>Garantía</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($alquileres as $alquiler)
                            <tr>
                                <td>{{ $alquiler->id }}</td>
                                <td>{{ $alquiler->cliente->nombre }}</td>
                                <td>{{ \Carbon\Carbon::parse($alquiler->fecha_inicio)->format('d/m/Y') }}</td>
                                <td>{{ \Carbon\Carbon::parse($alquiler->fecha_fin)->format('d/m/Y') }}</td>
                                <td>₲ {{ number_format($alquiler->costo_total, 0, ',', '.') }}</td>
                                <td>₲ {{ number_format($alquiler->garantia, 0, ',', '.') }}</td>
                                <td>
                                    @if ($alquiler->estado == 'activo')
                                        <span class="badge bg-success">Activo</span>
                                    @elseif($alquiler->estado == 'completado')
                                        <span class="badge bg-primary">Completado</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal"
                                            data-bs-target="#verAlquilerModal-{{ $alquiler->id }}" title="Ver detalles">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        @can('editar-alquiler')
                                            <a href="{{ route('alquileres.edit', $alquiler->id) }}"
                                                class="btn btn-sm btn-warning" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        @endcan
                                        @can('eliminar-alquiler')
                                            <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal"
                                                data-bs-target="#eliminarModal{{ $alquiler->id }}" title="Eliminar">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- MODALES --}}
        @foreach ($alquileres as $alquiler)
            {{-- Modal para ver detalles del alquiler --}}
            <div class="modal fade" id="verAlquilerModal-{{ $alquiler->id }}" tabindex="-1" 
                aria-labelledby="verAlquilerModalLabel-{{ $alquiler->id }}" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header bg-info text-white">
                            <h5 class="modal-title" id="verAlquilerModalLabel-{{ $alquiler->id }}">
                                <i class="fas fa-info-circle me-2"></i>Detalles del Alquiler #{{ $alquiler->id }}
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <!-- Información del Cliente -->
                                <div class="col-md-6">
                                    <div class="card border-primary mb-3">
                                        <div class="card-header bg-primary text-white">
                                            <h6 class="mb-0"><i class="fas fa-user me-2"></i>Información del Cliente</h6>
                                        </div>
                                        <div class="card-body">
                                            <p><strong>Nombre:</strong> {{ $alquiler->cliente->nombre }}</p>
                                            <p><strong>Teléfono:</strong> {{ $alquiler->cliente->telefono ?? 'No especificado' }}</p>
                                            <p><strong>Cedula:</strong> {{ $alquiler->cliente->correo ?? 'No especificado' }}</p>
                                            @if($alquiler->cliente->documento)
                                                <p class="mb-0"><strong>Documento:</strong> {{ $alquiler->cliente->documento }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Prendas Alquiladas -->
                                <div class="col-md-6">
                                    <div class="card border-warning mb-3">
                                        <div class="card-header bg-warning text-dark">
                                            <h6 class="mb-0"><i class="fas fa-tshirt me-2"></i>Prendas Alquiladas</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="table-responsive">
                                                <table class="table table-sm table-striped">
                                                    <thead>
                                                        <tr>
                                                            <th>Prenda</th>
                                                            <th>Talle</th>
                                                            <th>Cant.</th>
                                                            <th>Precio</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($alquiler->stockItems as $item)
                                                            @php
                                                                $talleId = $item->pivot->talle_id;
                                                                $talle = \App\Models\TalleStock::find($talleId);
                                                                $talleName = $talle ? $talle->talle : 'N/A';
                                                                $cantidad = $item->pivot->cantidad;
                                                                $precioItem = $item->precio_alquiler ?? 0;
                                                            @endphp
                                                            <tr>
                                                                <td>
                                                                    {{ $item->nombre }}<br>
                                                                    <small class="text-muted">Cod: {{ $item->codigo }}</small>
                                                                </td>
                                                                <td>{{ $talleName }}</td>
                                                                <td>{{ $cantidad }}</td>
                                                                <td>₲ {{ number_format($precioItem, 0, ',', '.') }}</td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Resumen Financiero -->
                            <div class="card border-info">
                                <div class="card-header bg-info text-white">
                                    <h6 class="mb-0"><i class="fas fa-money-bill-wave me-2"></i>Resumen Financiero</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row text-center">
                                        <div class="col-md-4">
                                            <h6>Costo Total</h6>
                                            <div class="h5 text-primary">₲ {{ number_format($alquiler->costo_total, 0, ',', '.') }}</div>
                                        </div>
                                        <div class="col-md-4">
                                            <h6>Garantía</h6>
                                            <div class="h5 text-warning">₲ {{ number_format($alquiler->garantia, 0, ',', '.') }}</div>
                                        </div>
                                        <div class="col-md-4">
                                            <h6>Total Cobrado</h6>
                                            <div class="h5 text-success">₲ {{ number_format($alquiler->costo_total + $alquiler->garantia, 0, ',', '.') }}</div>
                                        </div>
                                    </div>
                                    
                                    @if($alquiler->reserva)
                                        <hr>
                                        <div class="alert alert-info mb-0">
                                            <h6><i class="fas fa-bookmark me-2"></i>Reserva Previa</h6>
                                            <p class="mb-1"><strong>Reserva ID:</strong> #{{ $alquiler->reserva->id }}</p>
                                            <p class="mb-0"><strong>Seña Pagada:</strong> ₲ {{ number_format(($alquiler->reserva->seña_alquiler + $alquiler->reserva->seña_garantia), 0, ',', '.') }}</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Modal para eliminar --}}
            <div class="modal fade" id="eliminarModal{{ $alquiler->id }}" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Confirmar Eliminación</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <p>¿Está seguro que desea eliminar este alquiler? Esta acción liberará todas las prendas reservadas.</p>
                            <p><strong>Cliente:</strong> {{ $alquiler->cliente->nombre }}</p>
                            <p><strong>Fecha:</strong>
                                {{ \Carbon\Carbon::parse($alquiler->fecha_inicio)->format('d/m/Y') }} -
                                {{ \Carbon\Carbon::parse($alquiler->fecha_fin)->format('d/m/Y') }}</p>
                            <p><strong>Total:</strong> ₲ {{ number_format($alquiler->costo_total, 0, ',', '.') }}</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <form action="{{ route('alquileres.destroy', $alquiler->id) }}" method="POST" style="display: inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger">Eliminar</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endsection

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/simple-datatables@latest" type="text/javascript"></script>
    <script src="{{ asset('js/datatables-simple-demo.js') }}"></script>
@endpush
