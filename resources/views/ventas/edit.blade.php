{{-- Archivo: resources/views/ventas/edit.blade.php --}}
@extends('template')

@section('title', 'Editar Venta')

@push('css')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .producto-preview {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 0.25rem;
            padding: 15px;
            margin-bottom: 15px;
            min-height: 60px;
        }
        .producto-table {
            max-height: 400px;
            overflow-y: auto;
        }
        .producto-row {
            cursor: pointer;
            transition: background-color 0.2s;
        }
        .producto-row:hover {
            background-color: #f8f9fa;
        }
        .producto-row.selected {
            background-color: #e2f0ff;
        }
        .producto-row.disabled {
            opacity: 0.5;
            cursor: not-allowed;
            background-color: #f8f9fa;
        }
        .talle-selector {
            margin-top: 15px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 0.25rem;
            border: 1px solid #dee2e6;
            display: none;
        }
        .talle-selector.active {
            display: block;
        }
        .carrito-item {
            background-color: #fff;
            border: 1px solid #dee2e6;
            border-radius: 0.25rem;
            padding: 15px;
            margin-bottom: 10px;
        }
        .btn-remove {
            background: none;
            border: none;
            color: #dc3545;
            font-size: 1.2rem;
            cursor: pointer;
            padding: 5px;
        }
        .btn-remove:hover {
            color: #a71e2a;
        }
    </style>
@endpush

@section('content')

<div class="container-fluid px-4">
    <h1 class="mt-4 text-center">Editar Venta #{{ $venta->id }}</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item"><a href="{{ route('ventas.index') }}">Ventas</a></li>
        <li class="breadcrumb-item active">Editar Venta</li>
    </ol>

    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <i class="fas fa-edit me-2"></i>Formulario de Edición de Venta
        </div>
        <div class="card-body">
            <form action="{{ route('ventas.update', $venta->id) }}" method="POST" id="ventaForm">
                @csrf
                @method('PUT')

                <!-- Selección de cliente -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <label for="cliente_id" class="form-label">Cliente</label>
                        <select class="form-select @error('cliente_id') is-invalid @enderror" id="cliente_id" name="cliente_id" required>
                            <option value="" disabled>Seleccione un cliente</option>
                            @foreach ($clientes as $cliente)
                                <option value="{{ $cliente->id }}" {{ ($venta->cliente_id == $cliente->id || old('cliente_id') == $cliente->id) ? 'selected' : '' }}>
                                    {{ $cliente->nombre }}
                                </option>
                            @endforeach
                        </select>
                        @error('cliente_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="fecha_venta" class="form-label">Fecha de Venta</label>
                        <input type="date" class="form-control @error('fecha_venta') is-invalid @enderror" id="fecha_venta" name="fecha_venta" value="{{ old('fecha_venta', $venta->fecha_venta->format('Y-m-d')) }}" required>
                        @error('fecha_venta')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Productos actuales -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h6>Productos Actuales:</h6>
                        <div id="productosActuales">
                            @foreach($productosActuales as $index => $item)
                                <div class="carrito-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong>{{ $item['producto']->nombre_producto }}</strong><br>
                                            <small class="text-muted">
                                                Talle: {{ $item['talle']->talle }} | Cantidad: {{ $item['detalle']->cantidad }} | 
                                                Tipo: {{ $item['detalle']->tipo_producto === 'compra' ? 'De Compra' : 'Manual' }}
                                            </small>
                                        </div>
                                        <div class="text-end">
                                            <div class="text-primary fw-bold">₲ {{ number_format($item['detalle']->subtotal, 0, ',', '.') }}</div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            Para editar los productos, elimine todos los actuales y agregue los nuevos productos deseados.
                        </div>
                        
                        <button type="button" class="btn btn-warning mb-3" onclick="limpiarProductosActuales()">
                            <i class="fas fa-trash me-2"></i>Limpiar Productos Actuales
                        </button>
                    </div>
                </div>

                <!-- Selección de nuevos productos -->
                <div class="row mb-4" id="seccionNuevosProductos" style="display: none;">
                    <div class="col-12">
                        <label class="form-label">Agregar Productos</label>
                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#productosCompraModal">
                                <i class="fas fa-shopping-cart me-2"></i>Agregar Producto de Compra
                                <span class="badge bg-secondary ms-2">{{ $compras->sum(function($compra) { return $compra->talles->sum('cantidad_disponible'); }) }}</span>
                            </button>
                            <button type="button" class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#productosManualModal">
                                <i class="fas fa-plus-circle me-2"></i>Agregar Producto Manual
                                <span class="badge bg-secondary ms-2">{{ $productosVenta->sum(function($producto) { return $producto->talles->sum('cantidad_disponible'); }) }}</span>
                            </button>
                        </div>
                        
                        <!-- Carrito de nuevos productos -->
                        <div class="mt-3">
                            <h6>Nuevos Productos Seleccionados:</h6>
                            <div id="carritoProductos">
                                <div class="text-muted text-center p-3" id="carritoVacio">No hay productos seleccionados</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Precio total -->
                <div class="row mb-4">
                    <div class="col-md-6 ms-auto">
                        <label for="precio_total" class="form-label">Precio Total (₲)</label>
                        <input type="number" class="form-control @error('precio_total') is-invalid @enderror" id="precio_total" name="precio_total" value="{{ old('precio_total', $venta->precio_total) }}" step="0.01" min="0" required readonly>
                        @error('precio_total')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Campos ocultos para productos -->
                <div id="productosHidden">
                    @foreach($venta->detalles as $index => $detalle)
                        <input type="hidden" name="productos[{{ $index }}][tipo_producto]" value="{{ $detalle->tipo_producto }}">
                        <input type="hidden" name="productos[{{ $index }}][producto_id]" value="{{ $detalle->producto_id }}">
                        <input type="hidden" name="productos[{{ $index }}][talle_id]" value="{{ $detalle->talle_id }}">
                        <input type="hidden" name="productos[{{ $index }}][cantidad]" value="{{ $detalle->cantidad }}">
                        <input type="hidden" name="productos[{{ $index }}][precio_unitario]" value="{{ $detalle->precio_unitario }}">
                    @endforeach
                </div>

                <!-- Botones -->
                <div class="d-flex justify-content-end">
                    <a href="{{ route('ventas.show', $venta->id) }}" class="btn btn-secondary me-2">Cancelar</a>
                    <button type="submit" class="btn btn-primary" id="btnSubmit">Actualizar Venta</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Incluir los mismos modales que en create.blade.php -->
<!-- Modal para productos de compra -->
<div class="modal fade" id="productosCompraModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-shopping-cart me-2"></i>Seleccionar Producto de Compra
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Buscador -->
                <div class="mb-3">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" class="form-control" id="searchCompra" placeholder="Buscar productos de compra...">
                    </div>
                </div>

                <!-- Tabla de productos -->
                <div class="producto-table">
                    <table class="table table-hover">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th>Producto</th>
                                <th>Precio</th>
                                <th>Talles Disponibles</th>
                                <th>Stock Total</th>
                            </tr>
                        </thead>
                        <tbody id="tablaCompraBody">
                            @foreach ($compras as $compra)
                                @php
                                    $stockTotal = $compra->talles->sum('cantidad_disponible');
                                    $tallesDisponibles = $compra->talles->where('cantidad_disponible', '>', 0);
                                @endphp
                                <tr class="producto-row {{ $stockTotal == 0 ? 'disabled' : '' }}" 
                                    data-tipo="compra" 
                                    data-id="{{ $compra->id }}" 
                                    data-nombre="{{ $compra->nombre_producto }}" 
                                    data-precio="{{ $compra->precio_venta }}"
                                    data-stock="{{ $stockTotal }}">
                                    <td>
                                        <strong>{{ $compra->nombre_producto }}</strong>
                                        @if($stockTotal == 0)
                                            <span class="badge bg-danger ms-2">Sin Stock</span>
                                        @endif
                                    </td>
                                    <td class="text-primary fw-bold">₲ {{ number_format($compra->precio_venta, 0, ',', '.') }}</td>
                                    <td>
                                        @foreach($tallesDisponibles as $talle)
                                            <span class="badge bg-secondary me-1">{{ $talle->talle }}</span>
                                        @endforeach
                                        @if($tallesDisponibles->count() == 0)
                                            <span class="text-muted">Sin talles disponibles</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge {{ $stockTotal > 0 ? 'bg-success' : 'bg-danger' }}">
                                            {{ $stockTotal }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Selector de talle y cantidad -->
                <div class="talle-selector" id="talleCantidadCompra">
                    <h6 class="mb-3">Seleccionar Talle y Cantidad</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Talle</label>
                            <select class="form-select" id="talleCompraSelect">
                                <option value="">Seleccione un talle</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Cantidad</label>
                            <input type="number" class="form-control" id="cantidadCompraInput" min="1" value="1" disabled>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="agregarCompra" disabled>Agregar al Carrito</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para productos manuales -->
<div class="modal fade" id="productosManualModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-plus-circle me-2"></i>Seleccionar Producto Manual
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Buscador -->
                <div class="mb-3">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" class="form-control" id="searchManual" placeholder="Buscar productos manuales...">
                    </div>
                </div>

                <!-- Tabla de productos -->
                <div class="producto-table">
                    <table class="table table-hover">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th>Producto</th>
                                <th>Precio</th>
                                <th>Talles Disponibles</th>
                                <th>Stock Total</th>
                            </tr>
                        </thead>
                        <tbody id="tablaManualBody">
                            @foreach ($productosVenta as $producto)
                                @php
                                    $stockTotal = $producto->talles->sum('cantidad_disponible');
                                    $tallesDisponibles = $producto->talles->where('cantidad_disponible', '>', 0);
                                @endphp
                                <tr class="producto-row {{ $stockTotal == 0 ? 'disabled' : '' }}" 
                                    data-tipo="manual" 
                                    data-id="{{ $producto->id }}" 
                                    data-nombre="{{ $producto->nombre_producto }}" 
                                    data-precio="{{ $producto->precio_venta }}"
                                    data-stock="{{ $stockTotal }}">
                                    <td>
                                        <strong>{{ $producto->nombre_producto }}</strong>
                                        @if($stockTotal == 0)
                                            <span class="badge bg-danger ms-2">Sin Stock</span>
                                        @endif
                                    </td>
                                    <td class="text-primary fw-bold">₲ {{ number_format($producto->precio_venta, 0, ',', '.') }}</td>
                                    <td>
                                        @foreach($tallesDisponibles as $talle)
                                            <span class="badge bg-secondary me-1">{{ $talle->talle }}</span>
                                        @endforeach
                                        @if($tallesDisponibles->count() == 0)
                                            <span class="text-muted">Sin talles disponibles</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge {{ $stockTotal > 0 ? 'bg-success' : 'bg-danger' }}">
                                            {{ $stockTotal }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Selector de talle y cantidad -->
                <div class="talle-selector" id="talleCantidadManual">
                    <h6 class="mb-3">Seleccionar Talle y Cantidad</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Talle</label>
                            <select class="form-select" id="talleManualSelect">
                                <option value="">Seleccione un talle</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Cantidad</label>
                            <input type="number" class="form-control" id="cantidadManualInput" min="1" value="1" disabled>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="agregarManual" disabled>Agregar al Carrito</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let selectedProducto = null;
    let carrito = [];
    let carritoIndex = 0;
    let productosActualesEliminados = false;
    
    // Datos de productos
    const productosCompra = [
        @foreach($compras as $compra)
        {
            id: {{ $compra->id }},
            nombre: "{{ addslashes($compra->nombre_producto) }}",
            precio: {{ $compra->precio_venta }},
            talles: [
                @foreach($compra->talles as $talle)
                {
                    id: {{ $talle->id }},
                    talle: "{{ $talle->talle }}",
                    cantidad: {{ $talle->cantidad_disponible }}
                },
                @endforeach
            ]
        },
        @endforeach
    ];

    const productosManual = [
        @foreach($productosVenta as $producto)
        {
            id: {{ $producto->id }},
            nombre: "{{ addslashes($producto->nombre_producto) }}",
            precio: {{ $producto->precio_venta }},
            talles: [
                @foreach($producto->talles as $talle)
                {
                    id: {{ $talle->id }},
                    talle: "{{ $talle->talle }}",
                    cantidad: {{ $talle->cantidad_disponible }}
                },
                @endforeach
            ]
        },
        @endforeach
    ];

    // Función para limpiar productos actuales
    window.limpiarProductosActuales = function() {
        document.getElementById('productosActuales').style.display = 'none';
        document.getElementById('seccionNuevosProductos').style.display = 'block';
        document.getElementById('productosHidden').innerHTML = '';
        productosActualesEliminados = true;
        actualizarTotal();
    };

    // Resto del JavaScript similar al de create.blade.php pero adaptado para edición
    // [Incluir aquí todo el JavaScript del create.blade.php con las adaptaciones necesarias]
    
    // Función para seleccionar producto
    function seleccionarProducto(row) {
        const tipo = row.getAttribute('data-tipo');
        const id = parseInt(row.getAttribute('data-id'));
        const nombre = row.getAttribute('data-nombre');
        const precio = parseFloat(row.getAttribute('data-precio'));
        const stock = parseInt(row.getAttribute('data-stock'));

        if (stock === 0) return;

        // Limpiar selecciones anteriores
        document.querySelectorAll('.producto-row').forEach(r => r.classList.remove('selected'));
        document.querySelectorAll('.talle-selector').forEach(s => s.classList.remove('active'));

        // Marcar como seleccionado
        row.classList.add('selected');

        // Encontrar el producto en los datos
        const productos = tipo === 'compra' ? productosCompra : productosManual;
        const producto = productos.find(p => p.id === id);
        
        if (!producto) return;

        selectedProducto = {
            tipo: tipo,
            id: id,
            nombre: nombre,
            precio: precio,
            talles: producto.talles.filter(t => t.cantidad > 0),
            talleId: null,
            talle: null,
            cantidad: 1
        };

        // Mostrar selector de talle
        const tipoCapitalized = tipo.charAt(0).toUpperCase() + tipo.slice(1);
        const talleSelector = document.getElementById('talleCantidad' + tipoCapitalized);
        const talleSelect = document.getElementById('talle' + tipoCapitalized + 'Select');
        const cantidadInput = document.getElementById('cantidad' + tipoCapitalized + 'Input');
        const agregarBtn = document.getElementById('agregar' + tipoCapitalized);

        // Limpiar y llenar select de talles
        talleSelect.innerHTML = '<option value="">Seleccione un talle</option>';
        selectedProducto.talles.forEach(talle => {
            const option = document.createElement('option');
            option.value = talle.id;
            option.textContent = talle.talle + ' (' + talle.cantidad + ' disponibles)';
            option.setAttribute('data-cantidad', talle.cantidad);
            option.setAttribute('data-talle', talle.talle);
            talleSelect.appendChild(option);
        });

        cantidadInput.value = 1;
        cantidadInput.disabled = true;
        agregarBtn.disabled = true;
        talleSelector.classList.add('active');
    }

    // Event listeners para filas de productos
    document.querySelectorAll('.producto-row').forEach(row => {
        row.addEventListener('click', function() {
            if (!this.classList.contains('disabled')) {
                seleccionarProducto(this);
            }
        });
    });

    // Event listeners para selects de talle
    ['Compra', 'Manual'].forEach(tipo => {
        const talleSelect = document.getElementById('talle' + tipo + 'Select');
        const cantidadInput = document.getElementById('cantidad' + tipo + 'Input');
        const agregarBtn = document.getElementById('agregar' + tipo);

        if (talleSelect) {
            talleSelect.addEventListener('change', function() {
                if (this.value && selectedProducto) {
                    const option = this.options[this.selectedIndex];
                    const maxCantidad = parseInt(option.getAttribute('data-cantidad'));
                    const talleName = option.getAttribute('data-talle');

                    selectedProducto.talleId = parseInt(this.value);
                    selectedProducto.talle = talleName;
                    
                    cantidadInput.max = maxCantidad;
                    cantidadInput.value = 1;
                    cantidadInput.disabled = false;
                    agregarBtn.disabled = false;

                    selectedProducto.cantidad = 1;
                } else {
                    cantidadInput.disabled = true;
                    agregarBtn.disabled = true;
                }
            });
        }

        if (cantidadInput) {
            cantidadInput.addEventListener('change', function() {
                if (selectedProducto) {
                    selectedProducto.cantidad = parseInt(this.value);
                }
            });
        }

        if (agregarBtn) {
            agregarBtn.addEventListener('click', function() {
                if (selectedProducto && selectedProducto.talleId) {
                    agregarAlCarrito();
                    const modal = bootstrap.Modal.getInstance(document.getElementById('productos' + tipo + 'Modal'));
                    modal.hide();
                }
            });
        }
    });

    // Función para agregar al carrito
    function agregarAlCarrito() {
        if (!selectedProducto) return;

        const item = {
            index: carritoIndex++,
            tipo: selectedProducto.tipo,
            id: selectedProducto.id,
            nombre: selectedProducto.nombre,
            precio: selectedProducto.precio,
            talleId: selectedProducto.talleId,
            talle: selectedProducto.talle,
            cantidad: selectedProducto.cantidad,
            subtotal: selectedProducto.precio * selectedProducto.cantidad
        };

        carrito.push(item);
        actualizarCarrito();
        limpiarSeleccion();
    }

    // Función para actualizar el carrito
    function actualizarCarrito() {
        const carritoContainer = document.getElementById('carritoProductos');
        
        if (carrito.length === 0) {
            carritoContainer.innerHTML = '<div class="text-muted text-center p-3" id="carritoVacio">No hay productos seleccionados</div>';
        } else {
            let html = '';
            
            carrito.forEach(item => {
                html += `
                    <div class="carrito-item" data-index="${item.index}">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong>${item.nombre}</strong><br>
                                <small class="text-muted">
                                    Talle: ${item.talle} | Cantidad: ${item.cantidad} | 
                                    Tipo: ${item.tipo === 'compra' ? 'De Compra' : 'Manual'}
                                </small>
                            </div>
                            <div class="text-end">
                                <div class="text-primary fw-bold">₲ ${item.subtotal.toLocaleString()}</div>
                                <button type="button" class="btn-remove" onclick="eliminarDelCarrito(${item.index})">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                `;
            });
            
            carritoContainer.innerHTML = html;
        }
        
        actualizarTotal();
        actualizarCamposOcultos();
    }

    // Función para eliminar del carrito
    window.eliminarDelCarrito = function(index) {
        carrito = carrito.filter(item => item.index !== index);
        actualizarCarrito();
    };

    // Función para actualizar el total
    function actualizarTotal() {
        let total = 0;
        
        if (productosActualesEliminados) {
            total = carrito.reduce((sum, item) => sum + item.subtotal, 0);
        } else {
            total = {{ $venta->precio_total }};
        }
        
        document.getElementById('precio_total').value = total.toFixed(2);
    }

    // Función para actualizar campos ocultos
    function actualizarCamposOcultos() {
        if (productosActualesEliminados) {
            const container = document.getElementById('productosHidden');
            let html = '';
            
            carrito.forEach((item, index) => {
                html += `
                    <input type="hidden" name="productos[${index}][tipo_producto]" value="${item.tipo}">
                    <input type="hidden" name="productos[${index}][producto_id]" value="${item.id}">
                    <input type="hidden" name="productos[${index}][talle_id]" value="${item.talleId}">
                    <input type="hidden" name="productos[${index}][cantidad]" value="${item.cantidad}">
                    <input type="hidden" name="productos[${index}][precio_unitario]" value="${item.precio}">
                `;
            });
            
            container.innerHTML = html;
        }
    }

    // Función para limpiar selección
    function limpiarSeleccion() {
        selectedProducto = null;
        document.querySelectorAll('.producto-row').forEach(r => r.classList.remove('selected'));
        document.querySelectorAll('.talle-selector').forEach(s => s.classList.remove('active'));
        document.querySelectorAll('select[id*="talle"]').forEach(s => s.value = '');
        document.querySelectorAll('input[id*="cantidad"]').forEach(i => {
            i.value = 1;
            i.disabled = true;
        });
        document.querySelectorAll('button[id*="agregar"]').forEach(b => b.disabled = true);
    }

    // Funcionalidad de búsqueda
    function setupSearch(inputId, tableBodyId) {
        const searchInput = document.getElementById(inputId);
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                const rows = document.querySelectorAll('#' + tableBodyId + ' .producto-row');
                
                rows.forEach(row => {
                    const nombre = row.getAttribute('data-nombre').toLowerCase();
                    if (nombre.includes(searchTerm)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            });
        }
    }

    setupSearch('searchCompra', 'tablaCompraBody');
    setupSearch('searchManual', 'tablaManualBody');

    // Limpiar selecciones al cerrar modales
    ['productosCompraModal', 'productosManualModal'].forEach(modalId => {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.addEventListener('hidden.bs.modal', function() {
                limpiarSeleccion();
            });
        }
    });

    // Validación del formulario
    document.getElementById('ventaForm').addEventListener('submit', function(e) {
        if (productosActualesEliminados && carrito.length === 0) {
            e.preventDefault();
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Productos requeridos',
                    text: 'Debe agregar al menos un producto al carrito antes de continuar.',
                    confirmButtonText: 'Entendido'
                });
            } else {
                alert('Debe agregar al menos un producto al carrito antes de continuar.');
            }
            return false;
        }

        document.getElementById('btnSubmit').disabled = true;
        document.getElementById('btnSubmit').innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Procesando...';
    });
});
</script>
@endpush