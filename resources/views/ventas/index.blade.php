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
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-table me-4"></i> Tabla de Ventas
        </div>
        <div class="card-body">
            <table id="datatablesSimple" class="table table-striped">
                <thead>
                    <tr>
                        <th>Cliente</th>
                        <th>Prenda</th>
                        <th>Fecha de Venta</th>
                        <th>Precio Total</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($ventas as $venta)
                        <tr>
                            <td>{{ $venta->cliente->nombre }}</td>
                            <td>{{ $venta->vestido->nombre }}</td>
                            <td>{{ $venta->fecha_venta }}</td>
                            <td>{{ number_format($venta->precio_total, 2) }} Gs</td>
                            <td>
                                <div class="btn-group" role="group" aria-label="Acciones">
                                    <!-- Botón para ver detalles -->
                                    <button type="button" class="btn btn-success" data-bs-toggle="modal"
                                        data-bs-target="#verModal-{{ $venta->id }}">Ver</button>

                                    <!-- Botón para eliminar -->
                                    <button type="button" class="btn btn-danger" data-bs-toggle="modal"
                                        data-bs-target="#eliminarModal-{{ $venta->id }}">Eliminar</button>
                                    </div><a href="{{ route('factura.venta', $venta->id) }}" class="btn btn-primary">Comprobante</a>

                                </div>

                                <!-- Modal para ver detalles -->
                                <div class="modal fade" id="verModal-{{ $venta->id }}" tabindex="-1" aria-labelledby="verModalLabel-{{ $venta->id }}" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="verModalLabel-{{ $venta->id }}">Detalles de la Venta</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p><strong>Cliente:</strong> {{ $venta->cliente->nombre }}</p>
                                                <p><strong>Prenda:</strong> {{ $venta->vestido->nombre }}</p>
                                                <p><strong>Fecha de Venta:</strong> {{ $venta->fecha_venta }}</p>
                                                <p><strong>Precio Total:</strong> {{ number_format($venta->precio_total, 2) }} Gs</p>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Modal para eliminar -->
                                <div class="modal fade" id="eliminarModal-{{ $venta->id }}" tabindex="-1" aria-labelledby="eliminarModalLabel-{{ $venta->id }}" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="eliminarModalLabel-{{ $venta->id }}">Eliminar Venta</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p>¿Estás seguro de que deseas eliminar esta venta? Esta acción no se puede deshacer.</p>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                <form action="{{ route('ventas.destroy', $venta->id) }}" method="POST">
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
