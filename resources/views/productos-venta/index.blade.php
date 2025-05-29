{{-- Archivo: resources/views/productos-venta/index.blade.php --}}
@extends('template')

@section('title', 'Productos de Venta')

@push('css')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" rel="stylesheet" type="text/css">
    <style>
        .badge-count {
            font-size: 0.8rem;
            padding: 0.25rem 0.5rem;
            margin-left: 0.5rem;
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
    <h1 class="mt-4 text-center">Productos de Venta Manuales</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item active">Productos de Venta</li>
    </ol>
    <div class="mb-4">
        <a href="{{ route('productos-venta.create') }}" class="btn btn-primary">Agregar Nuevo Producto</a>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-table me-2"></i> Listado de Productos de Venta
        </div>
        <div class="card-body">
            <table id="datatablesSimple" class="table table-striped">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Precio de Venta</th>
                        <th>Talles</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($productos as $producto)
                        <tr>
                            <td>{{ $producto->nombre_producto }}</td>
                            <td>₲ {{ number_format($producto->precio_venta, 0, ',', '.') }}</td>
                            <td>
                                @foreach($producto->talles as $talle)
                                    <span class="badge bg-secondary">
                                        {{ $talle->talle }}
                                        <span class="badge bg-light text-dark badge-count">{{ $talle->cantidad_disponible }}/{{ $talle->cantidad_total }}</span>
                                    </span>
                                @endforeach
                            </td>
                            <td>
                                @if ($producto->disponible)
                                    <span class="badge bg-success">Disponible</span>
                                @else
                                    <span class="badge bg-danger">Sin Stock</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('productos-venta.show', $producto->id) }}" class="btn btn-info btn-sm">
                                        <i class="fas fa-eye"></i> Ver
                                    </a>
                                    <a href="{{ route('productos-venta.edit', $producto->id) }}" class="btn btn-warning btn-sm">
                                        <i class="fas fa-edit"></i> Editar
                                    </a>
                                    <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#eliminarModal-{{ $producto->id }}">
                                        <i class="fas fa-trash"></i> Eliminar
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <!-- Modal para eliminar -->
                        <div class="modal fade" id="eliminarModal-{{ $producto->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Eliminar Producto</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <p>¿Estás seguro de que deseas eliminar este producto?</p>
                                        <p><strong>Producto:</strong> {{ $producto->nombre_producto }}</p>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                        <form action="{{ route('productos-venta.destroy', $producto->id) }}" method="POST">
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