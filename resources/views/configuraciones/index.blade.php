@extends('template')

@section('title', 'Configuración del Sistema')

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
    <h1 class="mt-4 text-center">Configuración del Sistema</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item active">Configuración</li>
    </ol>
    <form action="{{ route('configuraciones.update') }}" method="POST">
        @csrf
        @method('PATCH')
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-cogs me-2"></i> Configuraciones
            </div>
            <div class="card-body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Parámetro</th>
                            <th>Descripción</th>
                            <th>Valor</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($configuraciones as $config)
                            <tr>
                                <td>{{ ucfirst($config->nombre) }}</td>
                                <td>{{ $config->descripcion }}</td>
                                <td>
                                    <input type="hidden" name="configuraciones[{{ $loop->index }}][id]" value="{{ $config->id }}">
                                    <input type="number" name="configuraciones[{{ $loop->index }}][valor]" class="form-control" value="{{ $config->valor }}">
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <button type="submit" class="btn btn-success">Guardar Cambios</button>
    </form>
</div>
@endsection
