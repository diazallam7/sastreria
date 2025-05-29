{{-- Archivo: resources/views/gastos-varios/index.blade.php --}}
@extends('template')

@section('title', 'Gastos Varios')

@push('css')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
    .stats-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 15px;
        padding: 20px;
        margin-bottom: 20px;
    }
    .stats-card h3 {
        margin: 0;
        font-size: 1.8rem;
        font-weight: bold;
    }
    .stats-card p {
        margin: 5px 0 0 0;
        opacity: 0.9;
    }
    .table-responsive {
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 0 20px rgba(0,0,0,0.1);
    }
    .btn-action {
        margin: 0 2px;
    }
</style>
@endpush

@section('content')

<div class="container-fluid px-4">
    <h1 class="mt-4 text-center">Gastos Varios</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item active">Gastos Varios</li>
    </ol>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Estadísticas -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="stats-card">
                <h3>₲ {{ number_format($totalMesActual, 0, ',', '.') }}</h3>
                <p><i class="fas fa-calendar-alt me-2"></i>Total del Mes Actual</p>
            </div>
        </div>
        <div class="col-md-6">
            <div class="stats-card">
                <h3>₲ {{ number_format($totalGeneral, 0, ',', '.') }}</h3>
                <p><i class="fas fa-chart-line me-2"></i>Total General</p>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-receipt me-2"></i>Lista de Gastos</h5>
            <a href="{{ route('gastos-varios.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Nuevo Gasto
            </a>
        </div>
        <div class="card-body">
            @if($gastos->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Nombre del Gasto</th>
                                <th>Fecha</th>
                                <th>Monto</th>
                                <th>Observación</th>
                                <th width="120">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($gastos as $gasto)
                                <tr>
                                    <td>{{ $gasto->id }}</td>
                                    <td>
                                        <strong>{{ $gasto->nombre_gasto }}</strong>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">
                                            {{ $gasto->fecha->format('d/m/Y') }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="text-danger fw-bold">
                                            {{ $gasto->monto_formateado }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($gasto->observacion)
                                            <span class="text-muted" title="{{ $gasto->observacion }}">
                                                {{ Str::limit($gasto->observacion, 50) }}
                                            </span>
                                        @else
                                            <span class="text-muted fst-italic">Sin observaciones</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('gastos-varios.edit', $gasto->id) }}" 
                                               class="btn btn-sm btn-warning btn-action" 
                                               title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" 
                                                    class="btn btn-sm btn-danger btn-action" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#eliminarModal{{ $gasto->id }}" 
                                                    title="Eliminar">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>

                                        <!-- Modal de confirmación para eliminar -->
                                        <div class="modal fade" id="eliminarModal{{ $gasto->id }}" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Confirmar Eliminación</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p>¿Está seguro que desea eliminar este gasto?</p>
                                                        <div class="alert alert-info">
                                                            <strong>Gasto:</strong> {{ $gasto->nombre_gasto }}<br>
                                                            <strong>Monto:</strong> {{ $gasto->monto_formateado }}<br>
                                                            <strong>Fecha:</strong> {{ $gasto->fecha->format('d/m/Y') }}
                                                        </div>
                                                        <p class="text-danger"><strong>Esta acción no se puede deshacer.</strong></p>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                        <form action="{{ route('gastos-varios.destroy', $gasto->id) }}" method="POST" style="display: inline;">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-danger">Eliminar Gasto</button>
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

                <!-- Paginación -->
                <div class="d-flex justify-content-center mt-4">
                    {{ $gastos->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-receipt fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No hay gastos registrados</h5>
                    <p class="text-muted">Comience agregando su primer gasto</p>
                    <a href="{{ route('gastos-varios.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Registrar Primer Gasto
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

@endsection

@push('js')
<script>
    // Mostrar SweetAlert para confirmaciones adicionales si es necesario
    document.addEventListener('DOMContentLoaded', function() {
        // Aquí puedes agregar JavaScript adicional si lo necesitas
    });
</script>
@endpush