@extends('template')

@section('title', 'Reporte de Ventas')

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4 text-center">Reporte de Ventas</h1>
    <form method="GET" action="{{ route('reportes.ventas') }}">
        <div class="row mb-3">
            <div class="col-md-6">
                <label for="intervalo" class="form-label">Intervalo:</label>
                <select name="intervalo" class="form-control">
                    <option value="mensual" {{ $intervalo == 'mensual' ? 'selected' : '' }}>Mensual</option>
                    <option value="anual" {{ $intervalo == 'anual' ? 'selected' : '' }}>Anual</option>
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary">Filtrar</button>
            </div>
        </div>
    </form>

    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-table me-1"></i> Resumen de Ventas
        </div>
        <div class="card-body">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Periodo</th>
                        <th>Total de Ventas</th>
                        <th>Ingresos Generados</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($ventas as $venta)
                        <tr>
                            <td>{{ $venta->periodo }}</td>
                            <td>{{ $venta->total_ventas }}</td>
                            <td>{{ number_format($venta->ingresos, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
