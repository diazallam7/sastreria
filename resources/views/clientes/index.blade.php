@extends('template')

@section('title', 'Clientes')

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
    <h1 class="mt-4 text-center">Clientes</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item active">Clientes</li>
    </ol>
    <div class="mb-4">
        <a href="{{ route('clientes.create') }}" class="btn btn-primary">Añadir Nuevo Cliente</a>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-table me-4"></i> Tabla de Clientes
        </div>
        <div class="card-body">
            <table id="datatablesSimple" class="table table-striped">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Teléfono</th>
                        <th>Dirección</th>
                        <th>Numero de Cedula</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($clientes as $cliente)
                        <tr>
                            <td>{{ $cliente->nombre }}</td>
                            <td>{{ $cliente->telefono }}</td>
                            <td>{{ $cliente->direccion }}</td>
                            <td>{{ $cliente->correo }}</td>
                            <td>
                                <span class="badge {{ $cliente->estado ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $cliente->estado ? 'Activo' : 'Eliminado' }}
                                </span>
                            </td>
                            <td>
                                <div class="btn-group" role="group" aria-label="Basic actions">
                                    <!-- Botón para editar -->
                                    <form action="{{ route('clientes.edit', ['cliente' => $cliente]) }}" method="get" class="me-1">
                                        <button type="submit" class="btn btn-primary">Editar</button>
                                    </form>
                            
                                    <form action="{{ route('clientes.clientes.historial', ['cliente' => $cliente]) }}" method="get" class="me-1"> <button type="submit" class="btn btn-info">Historial</button>
                            
                                    <!-- Botón para eliminar definitivamente -->
                                    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#eliminarModal-{{ $cliente->id }}">
                                        Eliminar
                                    </button>
                                </div>
                            

                                <!-- Modal para eliminar definitivamente -->
                                <div class="modal fade" id="eliminarModal-{{ $cliente->id }}" tabindex="-1" aria-labelledby="eliminarModalLabel-{{ $cliente->id }}" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="eliminarModalLabel-{{ $cliente->id }}">Eliminar Cliente</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p>¿Estás seguro de que deseas eliminar definitivamente este cliente? Esta acción no se puede deshacer.</p>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                <form action="{{ route('clientes.destroy', $cliente->id) }}" method="POST">
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
