@extends('template')

@section('title', 'Reporte de Alquileres')

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4 text-center">Reporte de Alquileres</h1>
    <form method="GET" action="{{ route('reportes.alquileres') }}">
        <div class="row mb-3">
            <div class="col-md-5">
                <label for="fecha_inicio" class="form-label">Fecha Inicio:</label>
                <input type="date" name="fecha_inicio" class="form-control" value="{{ $fechaInicio ?? '' }}">
            </div>
            <div class="col-md-5">
                <label for="fecha_fin" class="form-label">Fecha Fin:</label>
                <input type="date" name="fecha_fin" class="form-control" value="{{ $fechaFin ?? '' }}">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary">Filtrar</button>
            </div>
        </div>
    </form>

    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-table me-1"></i> Detalles de Alquileres
        </div>
        <div class="card-body">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Cliente</th>
                        <th>Vestido</th>
                        <th>Fecha Evento</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($alquileres as $alquiler)
                        <tr>
                            <td>{{ $alquiler->cliente->nombre }}</td>
                            <td>{{ $alquiler->vestido->nombre }}</td>
                            <td>{{ $alquiler->fecha_evento }}</td>
                            <td>{{ ucfirst($alquiler->estado) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
