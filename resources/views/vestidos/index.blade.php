@extends('template')

@section('title', 'Prendas')

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
    <h1 class="mt-4 text-center">Prendas</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item active">Prendas</li>
    </ol>
    <div class="mb-4">
        <a href="{{ route('vestidos.create') }}" class="btn btn-primary">Añadir Nueva Prenda</a>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-table me-4"></i> Tabla de Prendas
        </div>
        <div class="card-body">
            <table id="datatablesSimple" class="table table-striped">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Talla</th>
                        <th>Color</th>
                        <th>Categoría</th>
                        <th>Estado</th>
                        <th>Precio Alquiler</th>
                        <th>Precio Venta</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($vestidos as $vestido)
                        <tr>
                            <td>{{ $vestido->nombre }}</td>
                            <td>{{ $vestido->talla }}</td>
                            <td>{{ $vestido->color }}</td>
                            <td>{{ $vestido->categoria }}</td>
                            <td>
                                @if ($vestido->estado === 1)
                                    <span class="badge bg-primary">Disponible</span>
                                @elseif ($vestido->estado === 3)
                                    <span class="badge bg-danger">Alquilado</span>
                                @elseif ($vestido->estado === 4)
                                    <span class="badge bg-success">Vendido</span>
                                @else
                                    <span class="badge bg-secondary">Desconocido</span>
                                @endif
                            </td>
                            
                            <td>{{ $vestido->precio_alquiler ? '₲' . number_format($vestido->precio_alquiler, 0, ',', '.') : '-' }}</td>
                            <td>{{ $vestido->precio_venta ? '₲' . number_format($vestido->precio_venta, 0, ',', '.') : '-' }}</td>
                            <td>
                                <div class="btn-group" role="group">
                                    <!-- Botón para editar -->
                                    <form action="{{ route('vestidos.edit', ['vestido' => $vestido->id]) }}" method="get" class="me-1">
                                        <button type="submit" class="btn btn-primary">Editar</button>
                                    </form>

                                    <!-- Botón para eliminar -->
                                    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#eliminarModal-{{ $vestido->id }}">
                                        Eliminar
                                    </button>
                                </div>

                                <!-- Modal para eliminar -->
                                <div class="modal fade" id="eliminarModal-{{ $vestido->id }}" tabindex="-1" aria-labelledby="eliminarModalLabel-{{ $vestido->id }}" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="eliminarModalLabel-{{ $vestido->id }}">Eliminar Vestido</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                ¿Estás seguro de que deseas eliminar este vestido? Esta acción no se puede deshacer.
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                <form action="{{ route('vestidos.destroy', ['vestido' => $vestido->id]) }}" method="POST">
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
