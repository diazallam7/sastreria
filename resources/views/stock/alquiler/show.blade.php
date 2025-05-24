{{-- Archivo: resources/views/stock/alquiler/show.blade.php --}}
@extends('template')

@section('title', 'Detalle de Prenda')

@push('css')
<style>
    .talle-card {
        border: 1px solid #dee2e6;
        border-radius: 0.25rem;
        padding: 15px;
        margin-bottom: 15px;
        background-color: #f8f9fa;
    }
    .progress {
        height: 20px;
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4 text-center">Detalle de Prenda: {{ $item->nombre }} ({{ $item->codigo }})</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item"><a href="{{ route('stock.alquiler.index') }}">Stock de Alquiler</a></li>
        <li class="breadcrumb-item active">Detalle de Prenda</li>
    </ol>

    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-list-alt me-2"></i>Detalle por Talle
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($item->talles as $talle)
                            <div class="col-md-6">
                                <div class="talle-card">
                                    <h5>Talle: {{ $talle->talle }}</h5>
                                    <div class="row mb-2">
                                        <div class="col-md-6">Cantidad Total:</div>
                                        <div class="col-md-6">{{ $talle->cantidad_total }}</div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-md-6">Disponibles:</div>
                                        <div class="col-md-6">
                                            <span class="badge bg-success">{{ $talle->cantidad_disponible }}</span>
                                        </div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-md-6">Alquilados:</div>
                                        <div class="col-md-6">
                                            <span class="badge bg-primary">{{ $talle->cantidad_alquilada }}</span>
                                        </div>
                                    </div>
                                    
                                    <div class="progress mt-3">
                                        @php
                                            $porcentajeDisponible = $talle->cantidad_total > 0 ? ($talle->cantidad_disponible / $talle->cantidad_total) * 100 : 0;
                                            $porcentajeAlquilado = $talle->cantidad_total > 0 ? ($talle->cantidad_alquilada / $talle->cantidad_total) * 100 : 0;
                                        @endphp
                                        <div class="progress-bar bg-success" role="progressbar" style="width: {{ $porcentajeDisponible }}%" 
                                             aria-valuenow="{{ $porcentajeDisponible }}" aria-valuemin="0" aria-valuemax="100">
                                            {{ round($porcentajeDisponible) }}%
                                        </div>
                                        <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $porcentajeAlquilado }}%" 
                                             aria-valuenow="{{ $porcentajeAlquilado }}" aria-valuemin="0" aria-valuemax="100">
                                            {{ round($porcentajeAlquilado) }}%
                                        </div>
                                    </div>
                                    <div class="mt-2 d-flex justify-content-between">
                                        <small><span class="badge bg-success"></span> Disponible</small>
                                        <small><span class="badge bg-primary"></span> Alquilado</small>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="text-center mb-4">
        <a href="{{ route('stock.alquiler.edit', $item->id) }}" class="btn btn-warning">
            <i class="fas fa-edit"></i> Editar Prenda
        </a>
        <a href="{{ route('stock.alquiler.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver al Listado
        </a>
    </div>
</div>
@endsection