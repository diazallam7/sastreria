@extends('template')

@section('title', 'Reportes y Estadísticas')

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4 text-center">Reportes y Estadísticas</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item active">Reportes</li>
    </ol>

    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <h5>Alquileres</h5>
                    <a href="{{ route('reportes.alquileres') }}" class="btn btn-primary">Ver Reporte</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <h5>Ventas</h5>
                    <a href="{{ route('reportes.ventas') }}" class="btn btn-primary">Ver Reporte</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
