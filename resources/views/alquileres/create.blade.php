{{-- Modificar: resources/views/alquileres/create.blade.php --}}
@extends('template')

@section('title', 'Nuevo Alquiler')

@push('css')
    <link href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .prenda-card {
            border: 1px solid #dee2e6;
            border-radius: 0.25rem;
            padding: 10px;
            margin-bottom: 10px;
            transition: all 0.2s ease;
        }
        .prenda-card:hover {
            background-color: #f8f9fa;
        }
        .prenda-card.selected {
            background-color: #e2f0ff;
            border-color: #0d6efd;
        }
        .prenda-list {
            max-height: 400px;
            overflow-y: auto;
        }
        .prenda-preview {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 0.25rem;
            padding: 10px;
            margin-bottom: 10px;
        }
        .prenda-preview-item {
            background-color: #e2f0ff;
            border: 1px solid #0d6efd;
            border-radius: 0.25rem;
            padding: 5px 10px;
            margin: 5px;
            display: inline-block;
        }
        .prenda-preview-item .remove-prenda {
            margin-left: 5px;
            cursor: pointer;
            color: #dc3545;
        }
        .talle-selector {
            margin-top: 10px;
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 0.25rem;
            display: none;
        }
        .talle-selector.active {
            display: block;
        }
        .no-results {
            text-align: center;
            color: #6c757d;
            font-style: italic;
            padding: 20px;
            display: none;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid px-4">
        <h1 class="mt-4 text-center">Registrar Nuevo Alquiler</h1>
        <ol class="breadcrumb mb-4">
            <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
            <li class="breadcrumb-item"><a href="{{ route('alquileres.index') }}">Alquileres</a></li>
            <li class="breadcrumb-item active">Nuevo Alquiler</li>
        </ol>

        <div class="card">
            <div class="card-header">
                <i class="fas fa-plus-circle me-2"></i>Formulario de Registro
            </div>
            <div class="card-body">
                <form action="{{ route('alquileres.store') }}" method="POST" id="alquilerForm">
                    @csrf
                    <div class="row mb-3">
                        <div class="col-md-5">
                            <label for="cliente_id" class="form-label">Cliente</label>
                            <select class="form-select @error('cliente_id') is-invalid @enderror" name="cliente_id"
                                id="cliente_id" required>
                                <option value="" selected disabled>Seleccione un cliente</option>
                                @foreach ($clientes as $cliente)
                                    <option value="{{ $cliente->id }}"
                                        {{ old('cliente_id') == $cliente->id ? 'selected' : '' }}>
                                        {{ $cliente->nombre }} - {{ $cliente->correo ?? 'Sin cédula' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('cliente_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-1">
                            <label class="form-label">&nbsp;</label>
                            <button type="button" class="btn btn-success w-100" data-bs-toggle="modal" data-bs-target="#modalNuevoCliente" title="Nuevo Cliente">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Prendas</label>
                            <div class="input-group">
                                <button type="button" class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#prendasModal">
                                    Seleccionar Prendas <i class="fas fa-tshirt ms-2"></i>
                                </button>
                            </div>
                            @error('prendas')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                            
                            <!-- Vista previa de prendas seleccionadas -->
                            <div class="prenda-preview mt-3" id="prendasPreview">
                                <div class="text-muted text-center" id="noPrendasSelected">No hay prendas seleccionadas</div>
                                <div id="selectedPrendasContainer"></div>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="fecha_inicio" class="form-label">Fecha de Inicio</label>
                            <input type="text" class="form-control @error('fecha_inicio') is-invalid @enderror"
                                name="fecha_inicio" id="fecha_inicio" value="{{ old('fecha_inicio') }}"
                                placeholder="Seleccione una fecha" required>
                            @error('fecha_inicio')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="fecha_fin" class="form-label">Fecha de Fin</label>
                            <input type="text" class="form-control @error('fecha_fin') is-invalid @enderror"
                                name="fecha_fin" id="fecha_fin" value="{{ old('fecha_fin') }}"
                                placeholder="Seleccione una fecha" required>
                            @error('fecha_fin')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="costo_total" class="form-label">Costo Total (₲)</label>
                            <input type="number" step="0.01" class="form-control @error('costo_total') is-invalid @enderror"
                                name="costo_total" id="costo_total" value="{{ old('costo_total') }}"
                                placeholder="Se calculará automáticamente" readonly>
                            @error('costo_total')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="garantia" class="form-label">Garantía (₲)</label>
                            <input type="number" step="0.01" class="form-control @error('garantia') is-invalid @enderror"
                                name="garantia" id="garantia" value="{{ old('garantia') }}"
                                placeholder="Ingrese la garantía" required>
                            @error('garantia')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <input type="hidden" name="estado" value="1">
                    
                    <!-- Campos ocultos para las prendas seleccionadas -->
                    <div id="prendasIdsContainer"></div>

                    <div class="text-center">
                        <button type="submit" class="btn btn-primary">Registrar Alquiler</button>
                        <a href="{{ route('alquileres.index') }}" class="btn btn-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Modal para seleccionar prendas -->
    <div class="modal fade" id="prendasModal" tabindex="-1" aria-labelledby="prendasModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="prendasModalLabel">Seleccionar Prendas</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <input type="text" class="form-control" id="searchPrendas" placeholder="Buscar prendas por nombre o descripción...">
                    </div>
                    
                    <div class="prenda-list" id="prendaList">
                        @foreach ($stockItems as $prenda)
                            <div class="prenda-card" data-id="{{ $prenda->id }}" 
                                 data-nombre="{{ strtolower($prenda->nombre) }}" 
                                 data-descripcion="{{ strtolower($prenda->descripcion ?? '') }}"
                                 data-precio="{{ $prenda->precio_alquiler }}">
                                <div class="form-check">
                                    <input class="form-check-input prenda-checkbox" type="checkbox" value="{{ $prenda->id }}" 
                                           id="prenda{{ $prenda->id }}" data-precio="{{ $prenda->precio_alquiler }}">
                                    <label class="form-check-label w-100" for="prenda{{ $prenda->id }}">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong>{{ $prenda->nombre }}</strong>
                                                @if($prenda->descripcion)
                                                    <div class="text-muted small">{{ $prenda->descripcion }}</div>
                                                @endif
                                                <div class="text-muted">
                                                    Talles disponibles: 
                                                    @foreach($prenda->talles as $talle)
                                                        @if($talle->cantidad_disponible > 0)
                                                            <span class="badge bg-secondary">{{ $talle->talle }} ({{ $talle->cantidad_disponible }})</span>
                                                        @endif
                                                    @endforeach
                                                </div>
                                            </div>
                                            <div class="text-primary">
                                                ₲ {{ number_format($prenda->precio_alquiler, 0, ',', '.') }}
                                            </div>
                                        </div>
                                    </label>
                                </div>
                                
                                <!-- Selector de talle y cantidad -->
                                <div class="talle-selector" id="talleSelector{{ $prenda->id }}">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label class="form-label">Talle</label>
                                            <select class="form-select talle-select" data-prenda-id="{{ $prenda->id }}">
                                                <option value="" selected disabled>Seleccione un talle</option>
                                                @foreach($prenda->talles as $talle)
                                                    @if($talle->cantidad_disponible > 0)
                                                        <option value="{{ $talle->id }}" data-disponible="{{ $talle->cantidad_disponible }}">
                                                            {{ $talle->talle }} ({{ $talle->cantidad_disponible }} disponibles)
                                                        </option>
                                                    @endif
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Cantidad</label>
                                            <input type="number" class="form-control cantidad-input" min="1" value="1" 
                                                   data-prenda-id="{{ $prenda->id }}" disabled>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    
                    <div class="no-results" id="noResults">
                        No se encontraron prendas que coincidan con la búsqueda.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="confirmPrendas" data-bs-dismiss="modal">Confirmar Selección</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Nuevo Cliente -->
    <div class="modal fade" id="modalNuevoCliente" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">Crear Nuevo Cliente</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="formNuevoCliente">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <label for="nuevo_nombre" class="form-label">Nombre *</label>
                                <input type="text" class="form-control" id="nuevo_nombre" name="nombre" required>
                            </div>
                            <div class="col-md-6">
                                <label for="nuevo_telefono" class="form-label">Teléfono</label>
                                <input type="text" class="form-control" id="nuevo_telefono" name="telefono">
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <label for="nuevo_correo" class="form-label">Cédula</label>
                                <input type="text" class="form-control" id="nuevo_correo" name="correo">
                            </div>
                            <div class="col-md-6">
                                <label for="nueva_direccion" class="form-label">Dirección</label>
                                <input type="text" class="form-control" id="nueva_direccion" name="direccion">
                            </div>
                        </div>
                        
                        <!-- Sección de Medidas Básicas -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <h6 class="text-success mb-3">
                                    <i class="fas fa-ruler me-2"></i>Medidas Básicas (Opcional)
                                </h6>
                            </div>
                            <div class="col-md-6">
                                <label for="nuevo_medida_saco_basica" class="form-label">Medida Saco</label>
                                <input type="text" class="form-control" id="nuevo_medida_saco_basica" 
                                       name="medida_saco_basica" placeholder="Ej: 50-80">
                                <small class="text-muted">Formato: Talle-Largo</small>
                            </div>
                            <div class="col-md-6">
                                <label for="nuevo_medida_pantalon_basica" class="form-label">Medida Pantalón</label>
                                <input type="text" class="form-control" id="nuevo_medida_pantalon_basica" 
                                       name="medida_pantalon_basica" placeholder="Ej: 42-90">
                                <small class="text-muted">Formato: Cintura-Largo</small>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success">Crear Cliente</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <!-- Asegurar que jQuery esté cargado ANTES que otros scripts -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    
    <script>
        $(document).ready(function() {
            console.log('jQuery cargado:', typeof $ !== 'undefined');
            
            // Variables globales
            const selectedPrendas = new Map();
            
            // Inicializar flatpickr
            flatpickr("#fecha_inicio", {
                enableTime: false,
                dateFormat: "Y-m-d",
                locale: "es"
            });
            
            flatpickr("#fecha_fin", {
                enableTime: false,
                dateFormat: "Y-m-d",
                locale: "es"
            });

            // Inicializar Select2 para clientes
            $('#cliente_id').select2({
                theme: 'bootstrap-5',
                placeholder: 'Buscar cliente...',
                allowClear: true,
                language: {
                    noResults: function() {
                        return "No se encontraron clientes";
                    },
                    searching: function() {
                        return "Buscando...";
                    }
                }
            });

            // Función para calcular costo total (solo suma de precios de prendas)
            function calcularCostoTotal() {
                let costoTotal = 0;
                
                selectedPrendas.forEach((prenda) => {
                    costoTotal += parseFloat(prenda.precio) * parseInt(prenda.cantidad);
                });
                
                document.getElementById('costo_total').value = costoTotal.toFixed(2);
            }
            
            // Función para actualizar la vista previa de prendas seleccionadas
            function updatePrendasPreview() {
                const selectedPrendasContainer = document.getElementById('selectedPrendasContainer');
                const noPrendasSelected = document.getElementById('noPrendasSelected');
                const prendasIdsContainer = document.getElementById('prendasIdsContainer');
                
                selectedPrendasContainer.innerHTML = '';
                prendasIdsContainer.innerHTML = '';
                
                if (selectedPrendas.size === 0) {
                    noPrendasSelected.style.display = 'block';
                    document.getElementById('costo_total').value = '0';
                    return;
                }
                
                noPrendasSelected.style.display = 'none';
                
                let index = 0;
                
                selectedPrendas.forEach((prenda, id) => {
                    // Crear elemento visual para la prenda
                    const prendaElement = document.createElement('div');
                    prendaElement.className = 'prenda-preview-item';
                    prendaElement.innerHTML = `
                        ${prenda.nombre} - Talle: ${prenda.talleName} - Cant: ${prenda.cantidad} - ₲${parseFloat(prenda.precio * prenda.cantidad).toLocaleString()}
                        <span class="remove-prenda" data-id="${id}">
                            <i class="fas fa-times"></i>
                        </span>
                    `;
                    selectedPrendasContainer.appendChild(prendaElement);
                    
                    // Crear campos ocultos para el formulario
                    const stockIdInput = document.createElement('input');
                    stockIdInput.type = 'hidden';
                    stockIdInput.name = `prendas[${index}][stock_id]`;
                    stockIdInput.value = id;
                    prendasIdsContainer.appendChild(stockIdInput);
                    
                    const talleIdInput = document.createElement('input');
                    talleIdInput.type = 'hidden';
                    talleIdInput.name = `prendas[${index}][talle_id]`;
                    talleIdInput.value = prenda.talleId;
                    prendasIdsContainer.appendChild(talleIdInput);
                    
                    const cantidadInput = document.createElement('input');
                    cantidadInput.type = 'hidden';
                    cantidadInput.name = `prendas[${index}][cantidad]`;
                    cantidadInput.value = prenda.cantidad;
                    prendasIdsContainer.appendChild(cantidadInput);
                    
                    index++;
                });
                
                // Calcular costo total
                calcularCostoTotal();
                
                // Agregar event listeners para eliminar prendas
                document.querySelectorAll('.remove-prenda').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const id = this.getAttribute('data-id');
                        selectedPrendas.delete(id);
                        updatePrendasPreview();
                        
                        // Desmarcar el checkbox en el modal si está abierto
                        const checkbox = document.getElementById(`prenda${id}`);
                        if (checkbox) {
                            checkbox.checked = false;
                            
                            // Ocultar el selector de talle
                            const talleSelector = document.getElementById(`talleSelector${id}`);
                            if (talleSelector) {
                                talleSelector.classList.remove('active');
                            }
                        }
                        
                        // Quitar la clase selected de la tarjeta
                        const card = document.querySelector(`.prenda-card[data-id="${id}"]`);
                        if (card) {
                            card.classList.remove('selected');
                        }
                    });
                });
            }
            
            // Event listener para los checkboxes de prendas
            document.querySelectorAll('.prenda-checkbox').forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const card = this.closest('.prenda-card');
                    const id = card.getAttribute('data-id');
                    const talleSelector = document.getElementById(`talleSelector${id}`);
                    
                    if (this.checked) {
                        card.classList.add('selected');
                        talleSelector.classList.add('active');
                    } else {
                        card.classList.remove('selected');
                        talleSelector.classList.remove('active');
                        selectedPrendas.delete(id);
                        updatePrendasPreview();
                    }
                });
            });
            
            // Event listener para los selectores de talle
            document.querySelectorAll('.talle-select').forEach(select => {
                select.addEventListener('change', function() {
                    const prendaId = this.getAttribute('data-prenda-id');
                    const cantidadInput = document.querySelector(`.cantidad-input[data-prenda-id="${prendaId}"]`);
                    const maxDisponible = this.options[this.selectedIndex].getAttribute('data-disponible');
                    
                    cantidadInput.max = maxDisponible;
                    cantidadInput.value = 1;
                    cantidadInput.disabled = false;
                    
                    // Actualizar la prenda seleccionada
                    const card = document.querySelector(`.prenda-card[data-id="${prendaId}"]`);
                    const talleName = this.options[this.selectedIndex].text.split(' (')[0];
                    
                    selectedPrendas.set(prendaId, {
                        nombre: card.querySelector('strong').textContent,
                        precio: card.getAttribute('data-precio'),
                        talleId: this.value,
                        talleName: talleName,
                        cantidad: 1
                    });
                    
                    updatePrendasPreview();
                });
            });
            
            // Event listener para los inputs de cantidad
            document.querySelectorAll('.cantidad-input').forEach(input => {
                input.addEventListener('change', function() {
                    const prendaId = this.getAttribute('data-prenda-id');
                    const prenda = selectedPrendas.get(prendaId);
                    
                    if (prenda) {
                        prenda.cantidad = this.value;
                        selectedPrendas.set(prendaId, prenda);
                        updatePrendasPreview();
                    }
                });
            });
            
            // Event listener para confirmar la selección de prendas
            document.getElementById('confirmPrendas').addEventListener('click', function() {
                updatePrendasPreview();
            });
            
            // Event listener para buscar prendas
            document.getElementById('searchPrendas').addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                const prendaCards = document.querySelectorAll('.prenda-card');
                const noResults = document.getElementById('noResults');
                let hasResults = false;
                
                prendaCards.forEach(card => {
                    const nombre = card.getAttribute('data-nombre') || '';
                    const descripcion = card.getAttribute('data-descripcion') || '';
                    
                    if (nombre.includes(searchTerm) || descripcion.includes(searchTerm) || searchTerm === '') {
                        card.style.display = 'block';
                        hasResults = true;
                    } else {
                        card.style.display = 'none';
                    }
                });
                
                // Mostrar/ocultar mensaje de "no hay resultados"
                noResults.style.display = (!hasResults && searchTerm !== '') ? 'block' : 'none';
            });
            
            // Event listener para el formulario principal
            document.getElementById('alquilerForm').addEventListener('submit', function(e) {
                if (selectedPrendas.size === 0) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'warning',
                        title: 'Atención',
                        text: 'Debe seleccionar al menos una prenda'
                    });
                }
            });

            // Crear nuevo cliente
            $('#formNuevoCliente').on('submit', function(e) {
                e.preventDefault();
                console.log('Formulario de cliente enviado');
                
                // Mostrar loading
                Swal.fire({
                    title: 'Creando cliente...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                const formData = new FormData(this);
                
                fetch('{{ route("clientes.store") }}', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => {
                    console.log('Respuesta recibida:', response);
                    return response.json();
                })
                .then(data => {
                    console.log('Datos:', data);
                    
                    if (data.success) {
                        // Agregar el nuevo cliente al select
                        const newOption = new Option(
                            data.cliente.nombre + ' - ' + (data.cliente.correo || 'Sin cédula'),
                            data.cliente.id,
                            true,
                            true
                        );
                        $('#cliente_id').append(newOption).trigger('change');

                        // Cerrar modal y limpiar formulario
                        $('#modalNuevoCliente').modal('hide');
                        document.getElementById('formNuevoCliente').reset();

                        Swal.fire({
                            icon: 'success',
                            title: 'Cliente creado exitosamente',
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 1500
                        });
                    } else {
                        throw new Error(data.message || 'Error desconocido');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Error al crear el cliente: ' + error.message
                    });
                });
            });
            
            // Inicializar la vista previa
            updatePrendasPreview();
            
            // Hacer función disponible globalmente
            window.calcularCostoTotal = calcularCostoTotal;
        });
    </script>
@endpush
