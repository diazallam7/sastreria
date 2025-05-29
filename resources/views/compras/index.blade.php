{{-- Archivo: resources/views/compras/index.blade.php --}}
@extends('template')

@section('title', 'Compras')

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
    <h1 class="mt-4 text-center">Gestión de Compras</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item active">Compras</li>
    </ol>
    <div class="mb-4">
        <a href="{{ route('compras.create') }}" class="btn btn-primary">Registrar Nueva Compra</a>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-table me-2"></i> Listado de Compras
        </div>
        <div class="card-body">
            <table id="datatablesSimple" class="table table-striped">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Fecha de Compra</th>
                        <th>Precio Compra</th>
                        <th>Precio Venta</th>
                        <th>Talles</th>
                        <th>Estado Venta</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($compras as $compra)
                        <tr>
                            <td>{{ $compra->nombre_producto }}</td>
                            <td>{{ $compra->fecha_compra->format('d/m/Y') }}</td>
                            <td>₲ {{ number_format($compra->precio_compra, 0, ',', '.') }}</td>
                            <td>₲ {{ number_format($compra->precio_venta, 0, ',', '.') }}</td>
                            <td>
                                @foreach($compra->talles as $talle)
                                    <span class="badge bg-secondary">
                                        {{ $talle->talle }}
                                        <span class="badge bg-light text-dark badge-count">{{ $talle->cantidad_disponible }}/{{ $talle->cantidad_total }}</span>
                                    </span>
                                @endforeach
                            </td>
                            <td>
                                @if ($compra->activo_para_venta)
                                    <span class="badge bg-success">Activo para Venta</span>
                                @else
                                    <span class="badge bg-warning">Inactivo</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('compras.show', $compra->id) }}" class="btn btn-info btn-sm">
                                        <i class="fas fa-eye"></i> Ver
                                    </a>
                                    <a href="{{ route('compras.edit', $compra->id) }}" class="btn btn-warning btn-sm">
                                        <i class="fas fa-edit"></i> Editar
                                    </a>
                                    
                                    @if ($compra->activo_para_venta)
                                        <form action="{{ route('compras.desactivar', $compra->id) }}" method="POST" style="display: inline;">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-secondary btn-sm">
                                                <i class="fas fa-toggle-off"></i> Desactivar
                                            </button>
                                        </form>
                                    @else
                                        <form action="{{ route('compras.activar', $compra->id) }}" method="POST" style="display: inline;">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-success btn-sm">
                                                <i class="fas fa-toggle-on"></i> Activar para Venta
                                            </button>
                                        </form>
                                    @endif
                                    
                                    <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#eliminarModal-{{ $compra->id }}">
                                        <i class="fas fa-trash"></i> Eliminar
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <!-- Modal para eliminar -->
                        <div class="modal fade" id="eliminarModal-{{ $compra->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Eliminar Compra</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <p>¿Estás seguro de que deseas eliminar esta compra?</p>
                                        <p><strong>Producto:</strong> {{ $compra->nombre_producto }}</p>
                                        <p><strong>Fecha:</strong> {{ $compra->fecha_compra->format('d/m/Y') }}</p>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                        <form action="{{ route('compras.destroy', $compra->id) }}" method="POST">
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