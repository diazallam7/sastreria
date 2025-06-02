{{-- Archivo: resources/views/alquileres/index.blade.php --}}
@extends('template')

@section('title', 'Alquileres')

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
                                <td>{{ $alquiler->cliente->nombre }}</td>
                                <td>{{ \Carbon\Carbon::parse($alquiler->fecha_inicio)->format('d/m/Y') }}</td>
                                <td>{{ \Carbon\Carbon::parse($alquiler->fecha_fin)->format('d/m/Y') }}</td>
                                <td>₲ {{ number_format($alquiler->costo_total, 0, ',', '.') }}</td>
                                <td>₲ {{ number_format($alquiler->garantia, 0, ',', '.') }}</td>
                                <td>
                                    @if($alquiler->estado == 'activo')
                                        <span class="badge bg-success">Activo</span>
                                    @elseif($alquiler->estado == 'completado')
                                        <span class="badge bg-primary">Completado</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('alquileres.show', $alquiler->id) }}" class="btn btn-sm btn-info"
                                            title="Ver detalles">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @can('editar-alquiler')
                                            <a href="{{ route('alquileres.edit', $alquiler->id) }}" class="btn btn-sm btn-warning"
                                                title="Editar">
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

                                    <!-- Modal de confirmación para eliminar -->
                                    <div class="modal fade" id="eliminarModal{{ $alquiler->id }}" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Confirmar Eliminación</h5>
                                                    <button type="button" class="btn-close"
                                                        data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    ¿Está seguro que desea eliminar este alquiler? Esta acción liberará todas las prendas reservadas.
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary"
                                                        data-bs-dismiss="modal">Cancelar</button>
                                                    <form action="{{ route('alquileres.destroy', $alquiler->id) }}" method="POST"
                                                        style="display: inline;">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger">Eliminar</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </td>
                            </tr>

                            <!-- Modal para eliminar (duplicado para mantener consistencia) -->
                            <div class="modal fade" id="eliminarModal-{{ $alquiler->id }}" tabindex="-1"
                                aria-labelledby="eliminarModalLabel-{{ $alquiler->id }}" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="eliminarModalLabel-{{ $alquiler->id }}">Eliminar
                                                Alquiler</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p>¿Estás seguro de que deseas eliminar este alquiler? Esta acción no se puede
                                                deshacer.</p>
                                            <p><strong>Cliente:</strong> {{ $alquiler->cliente->nombre }}</p>
                                            <p><strong>Fecha:</strong> {{ \Carbon\Carbon::parse($alquiler->fecha_inicio)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($alquiler->fecha_fin)->format('d/m/Y') }}</p>
                                            <p><strong>Total:</strong> ₲ {{ number_format($alquiler->costo_total, 0, ',', '.') }}</p>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary"
                                                data-bs-dismiss="modal">Cancelar</button>
                                            <form action="{{ route('alquileres.destroy', $alquiler->id) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger">Eliminar</button>
                                            </form>
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
    <script src="https://cdn.jsdelivr.net/npm/simple-datatables@latest" type="text/javascript"></script>
    <script src="{{ asset('js/datatables-simple-demo.js') }}"></script>
@endpush