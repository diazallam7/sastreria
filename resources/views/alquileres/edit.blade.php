@extends('template')

@section('title', 'Editar Alquiler')

@push('css')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endpush

@section('content')
    <div class="container-fluid px-4">
        <h1 class="mt-4 text-center">Editar Alquiler</h1>
        <ol class="breadcrumb mb-4">
            <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
            <li class="breadcrumb-item"><a href="{{ route('alquileres.index') }}">Alquileres</a></li>
            <li class="breadcrumb-item active">Editar Alquiler</li>
        </ol>
    </div>

    <div class="container w-100 border border-3 border-secondary rounded p-4 mt-3">
        <form action="{{ route('alquileres.update', $alquiler) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="row">
                <!-- Cliente -->
                <div class="col-md-6 mb-3">
                    <label for="cliente_id" class="form-label">Cliente:</label>
                    <select name="cliente_id" id="cliente_id" class="form-control" required>
                        <option value="">Seleccione un cliente</option>
                        @foreach($clientes as $cliente)
                            <option value="{{ $cliente->id }}" {{ $alquiler->cliente_id == $cliente->id ? 'selected' : '' }}>
                                {{ $cliente->nombre }}
                            </option>
                        @endforeach
                    </select>
                    @error('cliente_id')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                <!-- Fecha de Inicio -->
                <div class="col-md-3 mb-3">
                    <label for="fecha_inicio" class="form-label">Fecha de Inicio:</label>
                    <input type="date" name="fecha_inicio" id="fecha_inicio" class="form-control" 
                           value="{{ $alquiler->fecha_inicio }}" required>
                    @error('fecha_inicio')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                <!-- Fecha de Fin -->
                <div class="col-md-3 mb-3">
                    <label for="fecha_fin" class="form-label">Fecha de Fin:</label>
                    <input type="date" name="fecha_fin" id="fecha_fin" class="form-control" 
                           value="{{ $alquiler->fecha_fin }}" required>
                    @error('fecha_fin')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>
            </div>

            <!-- Prendas -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5>Prendas del Alquiler</h5>
                    <button type="button" class="btn btn-primary btn-sm" onclick="agregarPrenda()">
                        <i class="fas fa-plus"></i> Agregar Prenda
                    </button>
                </div>
                <div class="card-body">
                    <div id="prendas-container">
                        @foreach($alquiler->stockItems as $index => $item)
                            <div class="prenda-item border p-3 mb-3" data-index="{{ $index }}">
                                <div class="row">
                                    <div class="col-md-4">
                                        <label class="form-label">Prenda:</label>
                                        <select name="prendas[{{ $index }}][stock_id]" class="form-control prenda-select" required>
                                            <option value="">Seleccione una prenda</option>
                                            @foreach($prendas as $prenda)
                                                <option value="{{ $prenda->id }}" {{ $item->id == $prenda->id ? 'selected' : '' }}>
                                                    {{ $prenda->nombre_del_producto }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Talle:</label>
                                        <select name="prendas[{{ $index }}][talle_id]" class="form-control talle-select" required>
                                            @foreach($item->talles as $talle)
                                                <option value="{{ $talle->id }}" {{ $item->pivot->talle_id == $talle->id ? 'selected' : '' }}>
                                                    {{ $talle->talle }} (Disponible: {{ $talle->cantidad_disponible + $item->pivot->cantidad }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Cantidad:</label>
                                        <input type="number" name="prendas[{{ $index }}][cantidad]" class="form-control cantidad-input" 
                                               value="{{ $item->pivot->cantidad }}" min="1" required>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Precio:</label>
                                        <input type="number" class="form-control precio-input" 
                                               value="{{ $item->precio_alquiler }}" readonly>
                                    </div>
                                    <div class="col-md-1 d-flex align-items-end">
                                        <button type="button" class="btn btn-danger btn-sm" onclick="eliminarPrenda(this)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Costos -->
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="costo_total" class="form-label">Costo Total:</label>
                    <input type="number" name="costo_total" id="costo_total" class="form-control" 
                           value="{{ $alquiler->costo_total }}" step="0.01" min="0" required>
                    @error('costo_total')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label for="garantia" class="form-label">Garantía:</label>
                    <input type="number" name="garantia" id="garantia" class="form-control" 
                           value="{{ $alquiler->garantia }}" step="0.01" min="0" required>
                    @error('garantia')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>
            </div>

            <!-- Botones -->
            <div class="row">
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Actualizar Alquiler
                    </button>
                    <a href="{{ route('alquileres.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Cancelar
                    </a>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    let prendaIndex = {{ count($alquiler->stockItems) }};
    
    $(document).ready(function() {
        $('.prenda-select, #cliente_id').select2();
        
        // Manejar cambio de prenda para cargar talles
        $(document).on('change', '.prenda-select', function() {
            const prendaId = $(this).val();
            const talleSelect = $(this).closest('.prenda-item').find('.talle-select');
            const precioInput = $(this).closest('.prenda-item').find('.precio-input');
            
            if (prendaId) {
                // Cargar talles disponibles
                $.get(`/api/prendas/${prendaId}/talles`, function(data) {
                    talleSelect.empty();
                    talleSelect.append('<option value="">Seleccione un talle</option>');
                    
                    data.talles.forEach(function(talle) {
                        if (talle.cantidad_disponible > 0) {
                            talleSelect.append(`<option value="${talle.id}">${talle.talle} (Disponible: ${talle.cantidad_disponible})</option>`);
                        }
                    });
                    
                    precioInput.val(data.precio_alquiler);
                });
            } else {
                talleSelect.empty();
                precioInput.val('');
            }
        });
        
        // Calcular total automáticamente
        $(document).on('input', '.cantidad-input, .precio-input', calcularTotal);
        $(document).on('change', '#fecha_inicio, #fecha_fin', calcularTotal);
    });
    
    function agregarPrenda() {
        const container = document.getElementById('prendas-container');
        const newPrenda = `
            <div class="prenda-item border p-3 mb-3" data-index="${prendaIndex}">
                <div class="row">
                    <div class="col-md-4">
                        <label class="form-label">Prenda:</label>
                        <select name="prendas[${prendaIndex}][stock_id]" class="form-control prenda-select" required>
                            <option value="">Seleccione una prenda</option>
                            @foreach($prendas as $prenda)
                                <option value="{{ $prenda->id }}">{{ $prenda->nombre_del_producto }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Talle:</label>
                        <select name="prendas[${prendaIndex}][talle_id]" class="form-control talle-select" required>
                            <option value="">Seleccione un talle</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Cantidad:</label>
                        <input type="number" name="prendas[${prendaIndex}][cantidad]" class="form-control cantidad-input" min="1" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Precio:</label>
                        <input type="number" class="form-control precio-input" readonly>
                    </div>
                    <div class="col-md-1 d-flex align-items-end">
                        <button type="button" class="btn btn-danger btn-sm" onclick="eliminarPrenda(this)">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;
        
        container.insertAdjacentHTML('beforeend', newPrenda);
        prendaIndex++;
        
        // Inicializar select2 para el nuevo elemento
        $('.prenda-select').last().select2();
    }
    
    function eliminarPrenda(button) {
        $(button).closest('.prenda-item').remove();
        calcularTotal();
    }
    
    function calcularTotal() {
        let total = 0;
        const fechaInicio = new Date($('#fecha_inicio').val());
        const fechaFin = new Date($('#fecha_fin').val());
        
        if (fechaInicio && fechaFin && fechaFin > fechaInicio) {
            const dias = Math.ceil((fechaFin - fechaInicio) / (1000 * 60 * 60 * 24)) + 1;
            
            $('.prenda-item').each(function() {
                const cantidad = parseInt($(this).find('.cantidad-input').val()) || 0;
                const precio = parseFloat($(this).find('.precio-input').val()) || 0;
                total += cantidad * precio * dias;
            });
        }
        
        $('#costo_total').val(total.toFixed(2));
    }
</script>
@endpush