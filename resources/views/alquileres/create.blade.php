{{-- Modificar: resources/views/alquileres/create.blade.php --}}
@extends('template')

@section('title', 'Nuevo Alquiler')

@push('css')
    <link href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" rel="stylesheet">
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
                        <div class="col-md-6">
                            <label for="cliente_id" class="form-label">Cliente</label>
                            <select class="form-select @error('cliente_id') is-invalid @enderror" name="cliente_id"
                                id="cliente_id" required>
                                <option value="" selected disabled>Seleccione un cliente</option>
                                @foreach ($clientes as $cliente)
                                    <option value="{{ $cliente->id }}"
                                        {{ old('cliente_id') == $cliente->id ? 'selected' : '' }}>
                                        {{ $cliente->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            @error('cliente_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
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

                    <div class="mb-3">
                        <label for="costo_total" class="form-label">Costo Total (₲)</label>
                        <input type="number" step="0.01" class="form-control @error('costo_total') is-invalid @enderror"
                            name="costo_total" id="costo_total" value="{{ old('costo_total') }}"
                            placeholder="Ingrese el costo total" required>
                        @error('costo_total')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="garantia" class="form-label">Garantia (₲)</label>
                        <input type="number" step="0.01" class="form-control @error('garantia') is-invalid @enderror"
                            name="garantia" id="garantia" value="{{ old('garantia') }}"
                            placeholder="Ingrese la garantia" required>
                        @error('garantia')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
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
                        <input type="text" class="form-control" id="searchPrendas" placeholder="Buscar prendas...">
                    </div>
                    
                    <div class="prenda-list">
                        @foreach ($prendas as $prenda)
                            <div class="prenda-card" data-id="{{ $prenda->id }}" data-nombre="{{ $prenda->nombre }}" 
                                 data-precio="{{ $prenda->precio_alquiler }}">
                                <div class="form-check">
                                    <input class="form-check-input prenda-checkbox" type="checkbox" value="{{ $prenda->id }}" 
                                           id="prenda{{ $prenda->id }}" data-precio="{{ $prenda->precio_alquiler }}">
                                    <label class="form-check-label w-100" for="prenda{{ $prenda->id }}">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong>{{ $prenda->nombre }}</strong> <span class="text-muted">({{ $prenda->codigo }})</span>
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
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="confirmPrendas" data-bs-dismiss="modal">Confirmar Selección</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
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
        
        document.addEventListener('DOMContentLoaded', function() {
            const selectedPrendas = new Map();
            const prendasPreview = document.getElementById('prendasPreview');
            const selectedPrendasContainer = document.getElementById('selectedPrendasContainer');
            const noPrendasSelected = document.getElementById('noPrendasSelected');
            const prendasIdsContainer = document.getElementById('prendasIdsContainer');
            const costoTotalInput = document.getElementById('costo_total');
            const searchInput = document.getElementById('searchPrendas');
            const prendaCards = document.querySelectorAll('.prenda-card');
            
            // Función para actualizar la vista previa de prendas seleccionadas
            function updatePrendasPreview() {
                selectedPrendasContainer.innerHTML = '';
                prendasIdsContainer.innerHTML = '';
                
                if (selectedPrendas.size === 0) {
                    noPrendasSelected.style.display = 'block';
                    costoTotalInput.value = '0';
                    return;
                }
                
                noPrendasSelected.style.display = 'none';
                
                let costoTotal = 0;
                let index = 0;
                
                selectedPrendas.forEach((prenda, id) => {
                    // Crear elemento visual para la prenda
                    const prendaElement = document.createElement('div');
                    prendaElement.className = 'prenda-preview-item';
                    prendaElement.innerHTML = `
                        ${prenda.nombre} (${prenda.codigo}) - Talle: ${prenda.talleName} - Cant: ${prenda.cantidad}
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
                    
                    // Sumar al costo total
                    costoTotal += parseFloat(prenda.precio) * parseInt(prenda.cantidad);
                    index++;
                });
                
                // Actualizar el costo total
                costoTotalInput.value = costoTotal.toFixed(2);
                
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
                        nombre: card.getAttribute('data-nombre'),
                        codigo: card.getAttribute('data-codigo'),
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
            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                
                prendaCards.forEach(card => {
                    const nombre = card.getAttribute('data-nombre').toLowerCase();
                    const codigo = card.getAttribute('data-codigo').toLowerCase();
                    
                    if (nombre.includes(searchTerm) || codigo.includes(searchTerm) ) {
                        card.style.display = 'block';
                    } else {
                        card.style.display = 'none';
                    }
                });
            });
            
            // Event listener para el formulario
            document.getElementById('alquilerForm').addEventListener('submit', function(e) {
                if (selectedPrendas.size === 0) {
                    e.preventDefault();
                    alert('Debe seleccionar al menos una prenda');
                }
            });
            
            // Inicializar la vista previa
            updatePrendasPreview();
        });
    </script>
@endpush