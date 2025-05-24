@extends('template')

@section('title', 'Devoluciones')

@push('css')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
                timer: 2000
            });
        </script>
    @endif

    <div class="container-fluid px-4">
        <h1 class="mt-4 text-center">Devoluciones</h1>
        <ol class="breadcrumb mb-4">
            <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
            <li class="breadcrumb-item active">Devoluciones</li>
        </ol>

        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-table me-1"></i> Pendientes de Devolución
            </div>
            <div class="card-body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Cliente</th>
                            <th>Prendas</th>
                            <th>Fecha</th>
                            <th>Tipo</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($registros as $registro)
                            <tr>
                                <td>{{ $registro->cliente->nombre }}</td>
                                <td>
                                    <ul class="list-unstyled prenda-list">
                                        @foreach($registro->prendas as $prenda)
                                            <li>{{ $prenda->nombre }} ({{ $prenda->categoria }})</li>
                                        @endforeach
                                    </ul>
                                </td>
                                <td>
                                    @if ($registro->tipo === 'alquiler')
                                        {{ \Carbon\Carbon::parse($registro->fecha_fin)->format('d/m/Y') }}
                                    @else
                                        {{ \Carbon\Carbon::parse($registro->fecha_evento)->format('d/m/Y') }}
                                    @endif
                                </td>
                                <td>
                                    @if ($registro->tipo === 'alquiler')
                                        <span class="badge bg-warning">Alquiler</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($registro->estado === 'activo')
                                        <span class="badge bg-success">Activo</span>
                                    @else
                                        <span class="badge bg-secondary">Completado</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        @if ($registro->tipo === 'alquiler')
                                            <a href="{{ route('devoluciones.calcular-multas', $registro->id) }}"
                                                class="btn btn-warning">
                                                Ver Multa
                                            </a>
                                        @endif
                                        <!-- Botón que activa el modal -->
                                        <button type="button" class="btn btn-success" data-bs-toggle="modal"
                                            data-bs-target="#confirmModal-{{ $registro->id }}">
                                            Marcar como Entregado
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <!-- Modal de confirmación -->
                            <div class="modal fade" id="confirmModal-{{ $registro->id }}" tabindex="-1"
                                aria-labelledby="confirmModalLabel-{{ $registro->id }}" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="confirmModalLabel-{{ $registro->id }}">
                                                Confirmación
                                            </h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p>¿Está seguro de que desea marcar este {{ $registro->tipo === 'alquiler' ? 'alquiler' : 'reserva' }} como "Entregado"?</p>
                                            <p>Las siguientes prendas serán marcadas como disponibles:</p>
                                            <ul>
                                                @foreach($registro->prendas as $prenda)
                                                    <li>{{ $prenda->nombre }} ({{ $prenda->categoria }})</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                                Cancelar
                                            </button>
                                            <!-- Formulario que se ejecuta al confirmar -->
                                            <form
                                                action="{{ route('devoluciones.actualizar-estado', $registro->id) }}"
                                                method="POST">
                                                @csrf
                                                <input type="hidden" name="estado"
                                                    value="{{ $registro->tipo === 'alquiler' ? 3 : 2 }}">
                                                <button type="submit" class="btn btn-success">Confirmar</button>
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
@endpush