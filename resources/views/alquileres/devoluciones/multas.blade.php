@extends('template')

@section('title', 'Cálculo de Multas')

@push('css')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
@endpush

@section('content')
    <div class="container-fluid px-4">
        <h1 class="mt-4 text-center">Cálculo de Multas</h1>
        <ol class="breadcrumb mb-4">
            <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
            <li class="breadcrumb-item"><a href="{{ route('devoluciones.index') }}">Devoluciones</a></li>
            <li class="breadcrumb-item active">Cálculo de Multas</li>
        </ol>

        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-calculator me-1"></i> Detalles de la Multa
                    </div>
                    <div class="card-body">
                        <div class="mb-4">
                            <h5>Información del Alquiler</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Cliente:</strong> {{ $alquiler->cliente->nombre }}</p>
                                    <p><strong>Fecha de Fin:</strong> {{ $fechaFin }}</p>
                                    <p><strong>Fecha Actual:</strong> {{ $fechaActual }}</p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Prendas:</strong></p>
                                    <ul>
                                        @foreach($alquiler->prendas as $prenda)
                                            <li>{{ $prenda->nombre }} ({{ $prenda->categoria }})</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <h5>Cálculo de la Multa</h5>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Días de Retraso</th>
                                            <th>Multa Diaria</th>
                                            <th>Multa Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>{{ $diasRetraso }}</td>
                                            <td>₲ {{ number_format($multaDiaria, 0, ',', '.') }}</td>
                                            <td class="fw-bold">₲ {{ number_format($multaTotal, 0, ',', '.') }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('devoluciones.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i> Volver
                            </a>
                            <form action="{{ route('devoluciones.actualizar-estado', $alquiler->id) }}" method="POST">
                                @csrf
                                <input type="hidden" name="estado" value="3">
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-check me-1"></i> Confirmar Devolución
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
@endpush