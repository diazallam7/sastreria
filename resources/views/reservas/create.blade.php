{{-- Archivo: resources/views/reservas/create.blade.php --}}
@extends('template')

@section('title', 'Crear Reserva')

@push('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" rel="stylesheet">
<link href="https://npmcdn.com/flatpickr/dist/themes/material_blue.css" rel="stylesheet">
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

@if (session('error'))
    <script>
        Swal.fire({
            icon: 'error',
            title: '{{ session('error') }}',
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000
        });
    </script>
@endif

<div class="container-fluid px-4">
    <h1 class="mt-4 text-center">Crear Nueva Reserva</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item"><a href="{{ route('reservas.index') }}">Reservas</a></li>
        <li class="breadcrumb-item active">Crear Reserva</li>
    </ol>

    <div class="card">
        <div class="card-header">
            <i class="fas fa-plus me-2"></i>Formulario de Nueva Reserva
        </div>
        <div class="card-body">
            <form action="{{ route('reservas.store') }}" method="POST" id="reservaForm">
                @csrf

                <div class="row mb-4">
                    <div class="col-md-5">
                        <label for="cliente_id" class="form-label">Cliente</label>
                        <select class="form-select @error('cliente_id') is-invalid @enderror" id="cliente_id" name="cliente_id" required>
                            <option value="" selected disabled>Seleccione un cliente</option>
                            @foreach ($clientes as $cliente)
                                <option value="{{ $cliente->id }}" {{ old('cliente_id') == $cliente->id ? 'selected' : '' }}>
                                    {{ $cliente->nombre }} - {{ $cliente->telefono ?? 'Sin teléfono' }}
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
                        
                        <div class="prenda-preview mt-3" id="prendasPreview">
                            <div class="text-muted text-center" id="noPrendasSelected">No hay prendas seleccionadas</div>
                            <div id="selectedPrendasContainer"></div>
                        </div>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-4">
                        <label for="fecha_reserva" class="form-label">Fecha de reserva</label>
                        <input type="text" class="form-control @error('fecha_reserva') is-invalid @enderror" 
                               id="fecha_reserva" name="fecha_reserva" value="{{ old('fecha_reserva') }}" 
                               placeholder="Seleccione la fecha de reserva" required readonly>
                        @error('fecha_reserva')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label for="fecha_entrega_programada" class="form-label">Fecha de Entrega</label>
                        <input type="text" class="form-control @error('fecha_entrega_programada') is-invalid @enderror" 
                               id="fecha_entrega_programada" name="fecha_entrega_programada" value="{{ old('fecha_entrega_programada') }}" 
                               placeholder="Seleccione la fecha de entrega" required readonly>
                        @error('fecha_entrega_programada')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label for="fecha_devolucion_programada" class="form-label">Fecha de Devolución</label>
                        <input type="text" class="form-control @error('fecha_devolucion_programada') is-invalid @enderror" 
                               id="fecha_devolucion_programada" name="fecha_devolucion_programada" value="{{ old('fecha_devolucion_programada') }}" 
                               placeholder="Seleccione la fecha de devolución" required readonly>
                        @error('fecha_devolucion_programada')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-3">
                        <label for="monto_total_display" class="form-label">Monto Total del Alquiler (₲)</label>
                        <input type="text" class="form-control @error('monto_total') is-invalid @enderror" 
                               id="monto_total_display" placeholder="0" autocomplete="off">
                        <input type="hidden" id="monto_total" name="monto_total" value="{{ old('monto_total', 0) }}">
                        <small class="text-muted">Se calcula automáticamente según las prendas seleccionadas</small>
                        @error('monto_total')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-3">
                        <label for="garantia_total_display" class="form-label">Garantía Total (₲)</label>
                        <input type="text" class="form-control @error('garantia_total') is-invalid @enderror" 
                               id="garantia_total_display" placeholder="0" autocomplete="off" required>
                        <input type="hidden" id="garantia_total" name="garantia_total" value="{{ old('garantia_total', 0) }}">
                        @error('garantia_total')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-3">
                        <label for="seña_garantia_display" class="form-label">Seña de Garantía (₲) *</label>
                        <input type="text" class="form-control @error('seña_garantia') is-invalid @enderror" 
                               id="seña_garantia_display" placeholder="0" autocomplete="off" required>
                        <input type="hidden" id="seña_garantia" name="seña_garantia" value="{{ old('seña_garantia', 0) }}">
                        @error('seña_garantia')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-3">
                        <label for="seña_alquiler_display" class="form-label">Seña de Alquiler (₲)</label>
                        <input type="text" class="form-control @error('seña_alquiler') is-invalid @enderror" 
                               id="seña_alquiler_display" placeholder="0" autocomplete="off">
                        <input type="hidden" id="seña_alquiler" name="seña_alquiler" value="{{ old('seña_alquiler', 0) }}">
                        @error('seña_alquiler')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="alert alert-info" id="resumenFinanciero" style="display: none;">
                    <h6>Resumen Financiero</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Saldo Alquiler a Cobrar:</strong> <span id="saldoAlquiler">₲ 0</span></p>
                            <p><strong>Saldo Garantía a Cobrar:</strong> <span id="saldoGarantia">₲ 0</span></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>TOTAL A COBRAR EN ENTREGA:</strong> <span id="totalACobrar" class="text-success fw-bold">₲ 0</span></p>
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <label for="observaciones" class="form-label">Observaciones</label>
                    <textarea class="form-control @error('observaciones') is-invalid @enderror" 
                              id="observaciones" name="observaciones" rows="3" 
                              placeholder="Observaciones adicionales sobre la reserva">{{ old('observaciones') }}</textarea>
                    @error('observaciones')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div id="prendasIdsContainer"></div>

                <div class="d-flex justify-content-end">
                    <a href="{{ route('reservas.index') }}" class="btn btn-secondary me-2">Cancelar</a>
                    <button type="submit" class="btn btn-primary">Crear Reserva</button>
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
                    @if(isset($prendas) && $prendas->count() > 0)
                        @foreach ($prendas as $prenda)
                            <div class="prenda-card" data-id="{{ $prenda->id }}" data-nombre="{{ $prenda->nombre }}" 
                                 data-codigo="{{ $prenda->codigo }}" data-precio="{{ $prenda->precio_alquiler ?? 0 }}">
                                <div class="form-check">
                                    <input class="form-check-input prenda-checkbox" type="checkbox" value="{{ $prenda->id }}" 
                                           id="prenda{{ $prenda->id }}" data-precio="{{ $prenda->precio_alquiler ?? 0 }}">
                                    <label class="form-check-label w-100" for="prenda{{ $prenda->id }}">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong>{{ $prenda->nombre }}</strong> <span class="text-muted">({{ $prenda->codigo }})</span>
                                                <div class="text-muted">
                                                    Talles disponibles: 
                                                    @if($prenda->talles && $prenda->talles->count() > 0)
                                                        @foreach($prenda->talles as $talle)
                                                            @if($talle->cantidad_disponible > 0)
                                                                <span class="badge bg-secondary">{{ $talle->talle }} ({{ $talle->cantidad_disponible }})</span>
                                                            @endif
                                                        @endforeach
                                                    @else
                                                        <span class="text-warning">Sin talles disponibles</span>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="text-primary">
                                                ₲ {{ number_format($prenda->precio_alquiler ?? 0, 0, ',', '.') }}
                                            </div>
                                        </div>
                                    </label>
                                </div>
                                
                                <div class="talle-selector" id="talleSelector{{ $prenda->id }}">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label class="form-label">Talle</label>
                                            <select class="form-select talle-select" data-prenda-id="{{ $prenda->id }}">
                                                <option value="" selected disabled>Seleccione un talle</option>
                                                @if($prenda->talles && $prenda->talles->count() > 0)
                                                    @foreach($prenda->talles as $talle)
                                                        @if($talle->cantidad_disponible > 0)
                                                            <option value="{{ $talle->id }}" data-disponible="{{ $talle->cantidad_disponible }}">
                                                                {{ $talle->talle }} ({{ $talle->cantidad_disponible }} disponibles)
                                                            </option>
                                                        @endif
                                                    @endforeach
                                                @endif
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
                    @else
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            No hay prendas disponibles para reservar en este momento.
                        </div>
                    @endif
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
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://npmcdn.com/flatpickr/dist/l10n/es.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Funciones para formatear números
    function formatearNumero(numero) {
        if (!numero || numero === 0) return '0';
        return numero.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

    function limpiarNumero(numeroFormateado) {
        if (!numeroFormateado) return 0;
        return parseInt(numeroFormateado.toString().replace(/\./g, '')) || 0;
    }

    // Configurar campos de precio con formato
    function configurarCampoNumerico(displayId, hiddenId) {
        const displayField = document.getElementById(displayId);
        const hiddenField = document.getElementById(hiddenId);
        
        if (!displayField || !hiddenField) return;

        // Inicializar con valor existente
        const valorInicial = hiddenField.value || 0;
        if (valorInicial > 0) {
            displayField.value = formatearNumero(valorInicial);
        }

        // Event listener para formatear mientras se escribe
        displayField.addEventListener('input', function() {
            let valor = this.value.replace(/[^\d.]/g, '');
            let numeroLimpio = valor.replace(/\./g, '');
            let numeroFormateado = formatearNumero(numeroLimpio);
            
            this.value = numeroFormateado;
            hiddenField.value = numeroLimpio;
            
            // Recalcular resumen si es necesario
            calcularResumen();
        });

        // Event listener para formatear al perder el foco
        displayField.addEventListener('blur', function() {
            if (!this.value || this.value === '0') {
                this.value = '0';
                hiddenField.value = 0;
                calcularResumen();
            }
        });
    }

    // Configurar todos los campos numéricos
    configurarCampoNumerico('monto_total_display', 'monto_total');
    configurarCampoNumerico('garantia_total_display', 'garantia_total');
    configurarCampoNumerico('seña_garantia_display', 'seña_garantia');
    configurarCampoNumerico('seña_alquiler_display', 'seña_alquiler');

    // Inicializar jQuery y Select2
    if (typeof $ !== 'undefined') {
        console.log('jQuery cargado correctamente');
        
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
        
        // Configuración de flatpickr para las fechas
        const today = new Date();
        const tomorrow = new Date(today);
        tomorrow.setDate(tomorrow.getDate() + 1);

        // Fecha de reserva (puede ser hoy o en el futuro)
        const fechaReservaFp = flatpickr("#fecha_reserva", {
            enableTime: false,
            dateFormat: "Y-m-d",
            locale: "es",
            minDate: "today",
            defaultDate: "today",
            allowInput: false,
            clickOpens: true,
            onChange: function(selectedDates, dateStr, instance) {
                // Actualizar fecha mínima de entrega cuando cambie la fecha de reserva
                if (selectedDates.length > 0) {
                    const selectedDate = new Date(selectedDates[0]);
                    fechaEntregaFp.set('minDate', selectedDate);
                    
                    // Si la fecha de entrega es anterior a la nueva fecha de reserva, limpiarla
                    const currentEntrega = fechaEntregaFp.selectedDates[0];
                    if (currentEntrega && currentEntrega < selectedDate) {
                        fechaEntregaFp.clear();
                        fechaDevolucionFp.clear();
                    }
                }
            }
        });

        // Fecha de entrega (debe ser igual o posterior a la fecha de reserva)
        const fechaEntregaFp = flatpickr("#fecha_entrega_programada", {
            enableTime: false,
            dateFormat: "Y-m-d",
            locale: "es",
            minDate: "today",
            allowInput: false,
            clickOpens: true,
            onChange: function(selectedDates, dateStr, instance) {
                // Actualizar fecha mínima de devolución cuando cambie la fecha de entrega
                if (selectedDates.length > 0) {
                    const selectedDate = new Date(selectedDates[0]);
                    fechaDevolucionFp.set('minDate', selectedDate);
                    
                    // Si la fecha de devolución es anterior a la nueva fecha de entrega, limpiarla
                    const currentDevolucion = fechaDevolucionFp.selectedDates[0];
                    if (currentDevolucion && currentDevolucion < selectedDate) {
                        fechaDevolucionFp.clear();
                    }
                }
            }
        });

        // Fecha de devolución (debe ser igual o posterior a la fecha de entrega)
        const fechaDevolucionFp = flatpickr("#fecha_devolucion_programada", {
            enableTime: false,
            dateFormat: "Y-m-d",
            locale: "es",
            minDate: "today",
            allowInput: false,
            clickOpens: true
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
    } else {
        console.error('jQuery no está cargado. Select2 no funcionará correctamente.');
    }
    
    // Código existente para prendas
    const selectedPrendas = new Map();
    const prendasPreview = document.getElementById('prendasPreview');
    const selectedPrendasContainer = document.getElementById('selectedPrendasContainer');
    const noPrendasSelected = document.getElementById('noPrendasSelected');
    const prendasIdsContainer = document.getElementById('prendasIdsContainer');
    const searchInput = document.getElementById('searchPrendas');
    const prendaCards = document.querySelectorAll('.prenda-card');
    
    function updatePrendasPreview() {
        selectedPrendasContainer.innerHTML = '';
        prendasIdsContainer.innerHTML = '';
        
        if (selectedPrendas.size === 0) {
            noPrendasSelected.style.display = 'block';
            document.getElementById('monto_total').value = '0';
            document.getElementById('monto_total_display').value = '0';
            calcularResumen();
            return;
        }
        
        noPrendasSelected.style.display = 'none';
        
        let index = 0;
        let montoTotal = 0;
        
        selectedPrendas.forEach((prenda, id) => {
            const subtotal = parseFloat(prenda.precio) * parseInt(prenda.cantidad);
            const prendaElement = document.createElement('div');
            prendaElement.className = 'prenda-preview-item';
            prendaElement.innerHTML = `
                ${prenda.nombre} (${prenda.codigo}) - Talle: ${prenda.talleName} - Cant: ${prenda.cantidad} - ₲ ${formatearNumero(subtotal)}
                <span class="remove-prenda" data-id="${id}">
                    <i class="fas fa-times"></i>
                </span>
            `;
            selectedPrendasContainer.appendChild(prendaElement);
            
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
            
            montoTotal += subtotal;
            index++;
        });
        
        // Actualizar el monto total automáticamente
        document.getElementById('monto_total').value = montoTotal;
        document.getElementById('monto_total_display').value = formatearNumero(montoTotal);
        calcularResumen();
        
        document.querySelectorAll('.remove-prenda').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                selectedPrendas.delete(id);
                updatePrendasPreview();
                
                const checkbox = document.getElementById(`prenda${id}`);
                if (checkbox) {
                    checkbox.checked = false;
                    const talleSelector = document.getElementById(`talleSelector${id}`);
                    if (talleSelector) {
                        talleSelector.classList.remove('active');
                    }
                }
                
                const card = document.querySelector(`.prenda-card[data-id="${id}"]`);
                if (card) {
                    card.classList.remove('selected');
                }
            });
        });
    }
    
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
    
    document.querySelectorAll('.talle-select').forEach(select => {
        select.addEventListener('change', function() {
            const prendaId = this.getAttribute('data-prenda-id');
            const cantidadInput = document.querySelector(`.cantidad-input[data-prenda-id="${prendaId}"]`);
            const maxDisponible = this.options[this.selectedIndex].getAttribute('data-disponible');
            
            cantidadInput.max = maxDisponible;
            cantidadInput.value = 1;
            cantidadInput.disabled = false;
            
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
    
    document.getElementById('confirmPrendas').addEventListener('click', function() {
        updatePrendasPreview();
    });
    
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            
            prendaCards.forEach(card => {
                const nombre = card.getAttribute('data-nombre').toLowerCase();
                const codigo = card.getAttribute('data-codigo').toLowerCase();
                
                if (nombre.includes(searchTerm) || codigo.includes(searchTerm)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    }
    
    function calcularResumen() {
        const montoTotal = parseFloat(document.getElementById('monto_total').value) || 0;
        const garantiaTotal = parseFloat(document.getElementById('garantia_total').value) || 0;
        const seniaAlquiler = parseFloat(document.getElementById('seña_alquiler').value) || 0;
        const seniaGarantia = parseFloat(document.getElementById('seña_garantia').value) || 0;

        const saldoAlquiler = montoTotal - seniaAlquiler;
        const saldoGarantia = garantiaTotal - seniaGarantia;
        const totalACobrar = saldoAlquiler + saldoGarantia;

        document.getElementById('saldoAlquiler').textContent = `₲ ${formatearNumero(saldoAlquiler)}`;
        document.getElementById('saldoGarantia').textContent = `₲ ${formatearNumero(saldoGarantia)}`;
        document.getElementById('totalACobrar').textContent = `₲ ${formatearNumero(totalACobrar)}`;

        if (montoTotal > 0 || garantiaTotal > 0) {
            document.getElementById('resumenFinanciero').style.display = 'block';
        }
    }
    
    document.getElementById('reservaForm').addEventListener('submit', function(e) {
        if (selectedPrendas.size === 0) {
            e.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'Seleccione al menos una prenda',
                text: 'Debe seleccionar al menos una prenda para crear la reserva.',
                confirmButtonText: 'Entendido'
            });
        }
    });
    
    updatePrendasPreview();
});
</script>
@endpush