@extends('template')

@section('title', 'Editar Compra')

@push('css')
    <style>
        #descripcion {
            resize: none;
        }
    </style>
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/css/bootstrap-select.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
@endpush

@section('content')
    <div class="container-fluid px-4">
        <h1 class="mt-4 text-center">Editar Compra</h1>
        <ol class="breadcrumb mb-4">
            <li class="breadcrumb-item active"><a href="{{ route('panel') }}">Inicio</a></li>
            <li class="breadcrumb-item"><a href="{{ route('ventas.index') }}">Compras</a></li>
            <li class="breadcrumb-item active">Editar Compra</li>
        </ol>
        <div class="contriner w-100 border border-3 border-primary rounded p-4 mt-3">
            <form action="{{route('ventas.update',['venta'=>$venta])}}" method="post">
                @method('PATCH')
                @csrf
                <div class="row g-3">

                <div class="col-md-6 mb-2">
                        <label for="codigo" class="form-label">Nombre del Producto:</label>
                        <input type="text" name="codigo" id="codigo" class="form-control"
                            value="{{ old('codigo', $venta->codigo) }}">
                        @error('codigo')
                            <small class="text-danger">{{ '*' . $message }}</small>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-2">
                        <label for="nombre_producto" class="form-label">Nombre del Producto:</label>
                        <input type="text" name="nombre_producto" id="nombre_producto" class="form-control"
                            value="{{ old('nombre_producto', $venta->nombre_producto) }}">
                        @error('nombre_producto')
                            <small class="text-danger">{{ '*' . $message }}</small>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-2">
                        <label for="fecha_hora" class="form-label">Fecha de Compra:</label>
                        <input type="date" name="fecha_hora" id="fecha_hora" class="form-control"
                            value="{{ old('fecha_hora', $venta->fecha_hora) }}">
                        @error('fecha_hora')
                            <small class="text-danger">{{ '*' . $message }}</small>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-2">
                        <label for="precio_compra" class="form-label">Cedula:</label>
                        <input type="text" name="precio_compra" id="precio_compra" class="form-control"
                            value="{{ old('precio_compra', $venta->precio_compra) }}">
                        @error('precio_compra')
                            <small class="text-danger">{{ '*' . $message }}</small>
                        @enderror
                    </div>

                    <div class="col-md-12 text-center">
                        <button type="sumbit" class="btn btn-primary">Guardar</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

@endsection

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/js/bootstrap-select.min.js"></script>

    <script>
    document.getElementById('precio_compra').addEventListener('input', function (e) {
        // Mantiene el valor con puntos solo en la interfaz de usuario
        let value = e.target.value.replace(/\D/g, '');
        value = new Intl.NumberFormat('es-ES').format(value);
        e.target.value = value;
    });

    document.getElementById('precio_compra').form.addEventListener('submit', function () {
        // Elimina los puntos antes de enviar el formulario para que se guarde correctamente
        let input = document.getElementById('precio_compra');
        input.value = input.value.replace(/\./g, '');
    });
</script>

@endpush
