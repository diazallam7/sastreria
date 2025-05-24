@extends('template')

@section('title', 'Historial de Cliente')

@push('css')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .prenda-list {
            margin-bottom: 0;
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
        <li class="breadcrumb-item active">Historial</li>
    </ol>

    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <i class="fas fa-history me-1"></i> Historial de Actividades
        </div>
        <div class="card-body">
            <div class="accordion" id="accordionHistorial">
                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingAlquileres">
                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseAlquileres" aria-expanded="true" aria-controls="collapseAlquileres">
                            Alquileres
                        </button>
                    </h2>
                    <div id="collapseAlquileres" class="accordion-collapse collapse show" aria-labelledby="headingAlquileres" data-bs-parent="#accordionHistorial">
                        <div class="accordion-body">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Prendas</th>
                                        <th>Fecha Inicio</th>
                                        <th>Fecha Fin</th>
                                        <th>Costo Total</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($cliente->alquileres as $alquiler)
                                        <tr>
                                            <td>
                                                <ul class="list-unstyled prenda-list">
                                                    @foreach($alquiler->prendas as $prenda)
                                                        <li>{{ $prenda->nombre }} ({{ $prenda->categoria }})</li>
                                                    @endforeach
                                                </ul>
                                            </td>
                                            <td>{{ \Carbon\Carbon::parse($alquiler->fecha_inicio)->format('d/m/Y') }}</td>
                                            <td>{{ \Carbon\Carbon::parse($alquiler->fecha_fin)->format('d/m/Y') }}</td>
                                            <td>₲ {{ number_format($alquiler->costo_total, 0, ',', '.') }}</td>
                                            <td>
                                                @if($alquiler->estado == 1)
                                                    <span class="badge bg-primary">Activo</span>
                                                @else
                                                    <span class="badge bg-success">Completado</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingVentas">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseVentas" aria-expanded="false" aria-controls="collapseVentas">
                            Ventas
                        </button>
                    </h2>
                    <div id="collapseVentas" class="accordion-collapse collapse" aria-labelledby="headingVentas" data-bs-parent="#accordionHistorial">
                        <div class="accordion-body">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Prendas</th>
                                        <th>Fecha Venta</th>
                                        <th>Precio Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($cliente->ventas as $venta)
                                        <tr>
                                            <td>
                                                @if(isset($venta->vestido))
                                                    {{ $venta->vestido->nombre }}
                                                @elseif(isset($venta->prendas))
                                                    <ul class="list-unstyled prenda-list">
                                                        @foreach($venta->prendas as $prenda)
                                                            <li>{{ $prenda->nombre }} ({{ $prenda->categoria }})</li>
                                                        @endforeach
                                                    </ul>
                                                @else
                                                    <span class="text-muted">No disponible</span>
                                                @endif
                                            </td>
                                            <td>{{ \Carbon\Carbon::parse($venta->fecha_venta)->format('d/m/Y') }}</td>
                                            <td>₲ {{ number_format($venta->precio_total, 0, ',', '.') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Botón Volver -->
            <div class="text-center mt-4">
                <a href="{{ route('clientes.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Volver
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
@endpush