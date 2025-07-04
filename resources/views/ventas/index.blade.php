{{-- Archivo: resources/views/ventas/index.blade.php --}}
@extends('template')

@section('title', 'Ventas')

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

    <div class="container-fluid px-4">
        <h1 class="mt-4 text-center">Ventas</h1>
        <ol class="breadcrumb mb-4">
            <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
            <li class="breadcrumb-item active">Ventas</li>
        </ol>
        <div class="mb-4">
            <a href="{{ route('ventas.create') }}" class="btn btn-primary">Registrar Nueva Venta</a>
            <a href="{{ route('productos-venta.index') }}" class="btn btn-success">Gestionar Productos Manuales</a>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-table me-2"></i> Listado de Ventas
            </div>
            <div class="card-body">
                <table id="datatablesSimple" class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Cliente</th>
                            <th>Fecha de Venta</th>
                            <th>Precio Total</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($ventas as $venta)
                            <tr>
                                <td>{{ $venta->id }}</td>
                                <td>{{ $venta->cliente->nombre }}</td>
                                <td>{{ $venta->fecha_venta->format('d/m/Y') }}</td>
                                <td>₲ {{ number_format($venta->precio_total, 0, ',', '.') }}</td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('ventas.show', $venta->id) }}" class="btn btn-sm btn-info"
                                            title="Ver detalles">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @can('editar-venta')
                                            <a href="{{ route('ventas.edit', $venta->id) }}" class="btn btn-sm btn-warning"
                                                title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            
                                        @endcan
                                        @can('eliminar-venta')
                                            <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal"
                                                data-bs-target="#eliminarModal{{ $venta->id }}" title="Eliminar">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                            
                                        @endcan
                                    </div>

                                    <!-- Modal de confirmación para eliminar -->
                                    <div class="modal fade" id="eliminarModal{{ $venta->id }}" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Confirmar Eliminación</h5>
                                                    <button type="button" class="btn-close"
                                                        data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    ¿Está seguro que desea eliminar esta venta? Esta acción restaurará el
                                                    stock de los productos.
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary"
                                                        data-bs-dismiss="modal">Cancelar</button>
                                                    <form action="{{ route('ventas.destroy', $venta->id) }}" method="POST"
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

                            <!-- Modal para eliminar -->
                            <div class="modal fade" id="eliminarModal-{{ $venta->id }}" tabindex="-1"
                                aria-labelledby="eliminarModalLabel-{{ $venta->id }}" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="eliminarModalLabel-{{ $venta->id }}">Eliminar
                                                Venta</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p>¿Estás seguro de que deseas eliminar esta venta? Esta acción no se puede
                                                deshacer.</p>
                                            <p><strong>Producto:</strong> {{ $venta->nombre_producto }}</p>
                                            <p><strong>Cliente:</strong> {{ $venta->cliente->nombre }}</p>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary"
                                                data-bs-dismiss="modal">Cancelar</button>
                                            <form action="{{ route('ventas.destroy', $venta->id) }}" method="POST">
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
