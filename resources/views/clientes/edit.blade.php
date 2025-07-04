@extends('template')

@section('title', 'Editar Cliente')

@push('css')
    <style>
        .medidas-section {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .medidas-toggle {
            cursor: pointer;
            user-select: none;
        }
        .medidas-content {
            display: none;
        }
        .medidas-content.show {
            display: block;
        }
        .form-control:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
        }
        .badge-medidas {
            font-size: 0.75rem;
        }
        .info-card {
            background-color: #e3f2fd;
            border-left: 4px solid #2196f3;
            padding: 15px;
            margin-bottom: 20px;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid px-4">
        <h1 class="mt-4 text-center">Editar Cliente</h1>
        <ol class="breadcrumb mb-4">
            <li class="breadcrumb-item active"><a href="{{ route('panel') }}">Inicio</a></li>
            <li class="breadcrumb-item"><a href="{{ route('clientes.index') }}">Clientes</a></li>
            <li class="breadcrumb-item active">Editar Cliente</li>
        </ol>
        
        <div class="container w-100 border border-3 border-primary rounded p-4 mt-3">
            <!-- Información del cliente -->
            <div class="info-card">
                <h5 class="mb-2"><i class="fas fa-info-circle me-2"></i>Editando Cliente</h5>
                <p class="mb-1"><strong>Nombre:</strong> {{ $cliente->nombre }}</p>
                <p class="mb-1"><strong>Teléfono:</strong> {{ $cliente->telefono ?? 'No registrado' }}</p>
                <p class="mb-0"><strong>Estado:</strong> 
                    <span class="badge {{ $cliente->estado ? 'bg-success' : 'bg-secondary' }}">
                        {{ $cliente->estado ? 'Activo' : 'Inactivo' }}
                    </span>
                </p>
            </div>

            <form action="{{ route('clientes.update', $cliente) }}" method="post">
                @csrf
                @method('PUT')
                
                <!-- Información Básica -->
                <div class="row g-3 mb-4">
                    <div class="col-12">
                        <h4 class="text-primary"><i class="fas fa-user me-2"></i>Información Personal</h4>
                        <hr>
                    </div>

                    <div class="col-md-6 mb-2">
                        <label for="nombre" class="form-label">Nombre *:</label>
                        <input type="text" name="nombre" id="nombre" class="form-control" 
                               value="{{ old('nombre', $cliente->nombre) }}" required>
                        @error('nombre')
                            <small class="text-danger">{{ '*' . $message }}</small>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-2">
                        <label for="telefono" class="form-label">Teléfono:</label>
                        <input type="text" name="telefono" id="telefono" class="form-control" 
                               value="{{ old('telefono', $cliente->telefono) }}">
                        @error('telefono')
                            <small class="text-danger">{{ '*' . $message }}</small>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-2">
                        <label for="correo" class="form-label">Número de Cédula:</label>
                        <input type="text" name="correo" id="correo" class="form-control" 
                               value="{{ old('correo', $cliente->correo) }}">
                        @error('correo')
                            <small class="text-danger">{{ '*' . $message }}</small>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-2">
                        <label for="direccion" class="form-label">Dirección:</label>
                        <input type="text" name="direccion" id="direccion" class="form-control" 
                               value="{{ old('direccion', $cliente->direccion) }}">
                        @error('direccion')
                            <small class="text-danger">{{ '*' . $message }}</small>
                        @enderror
                    </div>
                </div>

                <!-- Medidas Básicas para Alquiler/Reserva -->
                <div class="medidas-section">
                    <div class="medidas-toggle" onclick="toggleMedidas('basicas')">
                        <h4 class="text-success mb-3">
                            <i class="fas fa-ruler me-2"></i>Medidas Básicas (Alquiler/Reserva)
                            @if($cliente->tieneMedidasBasicas())
                                <span class="badge bg-success badge-medidas ms-2">Registradas</span>
                            @else
                                <span class="badge bg-info badge-medidas ms-2">Opcional</span>
                            @endif
                            <i class="fas fa-chevron-down float-end" id="icon-basicas"></i>
                        </h4>
                    </div>
                    <div class="medidas-content {{ $cliente->tieneMedidasBasicas() ? 'show' : '' }}" id="medidas-basicas">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="medida_saco_basica" class="form-label">Medida Saco:</label>
                                <input type="text" name="medida_saco_basica" id="medida_saco_basica" 
                                       class="form-control" value="{{ old('medida_saco_basica', $cliente->medida_saco_basica) }}" 
                                       placeholder="Ej: 50-80">
                                <small class="text-muted">Formato: Talle-Largo</small>
                                @error('medida_saco_basica')
                                    <small class="text-danger d-block">{{ '*' . $message }}</small>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="medida_pantalon_basica" class="form-label">Medida Pantalón:</label>
                                <input type="text" name="medida_pantalon_basica" id="medida_pantalon_basica" 
                                       class="form-control" value="{{ old('medida_pantalon_basica', $cliente->medida_pantalon_basica) }}" 
                                       placeholder="Ej: 42-90">
                                <small class="text-muted">Formato: Cintura-Largo</small>
                                @error('medida_pantalon_basica')
                                    <small class="text-danger d-block">{{ '*' . $message }}</small>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Medidas Completas para Confección -->
                <div class="medidas-section">
                    <div class="medidas-toggle" onclick="toggleMedidas('completas')">
                        <h4 class="text-warning mb-3">
                            <i class="fas fa-cut me-2"></i>Medidas Completas (Confección)
                            @if($cliente->tieneMedidasCompletas())
                                <span class="badge bg-success badge-medidas ms-2">Registradas</span>
                            @else
                                <span class="badge bg-info badge-medidas ms-2">Opcional</span>
                            @endif
                            <i class="fas fa-chevron-down float-end" id="icon-completas"></i>
                        </h4>
                    </div>
                    <div class="medidas-content {{ $cliente->tieneMedidasCompletas() ? 'show' : '' }}" id="medidas-completas">
                        
                        <!-- Medidas de Saco -->
                        <div class="row g-3 mb-4">
                            <div class="col-12">
                                <h5 class="text-secondary"><i class="fas fa-vest me-2"></i>SACO</h5>
                            </div>
                            <div class="col-md-3">
                                <label for="saco_talle" class="form-label">Talle:</label>
                                <input type="number" step="0.01" name="saco_talle" id="saco_talle" 
                                       class="form-control" value="{{ old('saco_talle', $cliente->saco_talle) }}" placeholder="cm">
                            </div>
                            <div class="col-md-3">
                                <label for="saco_largo" class="form-label">Largo:</label>
                                <input type="number" step="0.01" name="saco_largo" id="saco_largo" 
                                       class="form-control" value="{{ old('saco_largo', $cliente->saco_largo) }}" placeholder="cm">
                            </div>
                            <div class="col-md-3">
                                <label for="saco_espalda" class="form-label">Espalda:</label>
                                <input type="number" step="0.01" name="saco_espalda" id="saco_espalda" 
                                       class="form-control" value="{{ old('saco_espalda', $cliente->saco_espalda) }}" placeholder="cm">
                            </div>
                            <div class="col-md-3">
                                <label for="saco_manga" class="form-label">Manga:</label>
                                <input type="number" step="0.01" name="saco_manga" id="saco_manga" 
                                       class="form-control" value="{{ old('saco_manga', $cliente->saco_manga) }}" placeholder="cm">
                            </div>
                            <div class="col-md-3">
                                <label for="saco_pecho" class="form-label">Pecho:</label>
                                <input type="number" step="0.01" name="saco_pecho" id="saco_pecho" 
                                       class="form-control" value="{{ old('saco_pecho', $cliente->saco_pecho) }}" placeholder="cm">
                            </div>
                            <div class="col-md-3">
                                <label for="saco_cintura" class="form-label">Cintura:</label>
                                <input type="number" step="0.01" name="saco_cintura" id="saco_cintura" 
                                       class="form-control" value="{{ old('saco_cintura', $cliente->saco_cintura) }}" placeholder="cm">
                            </div>
                            <div class="col-md-3">
                                <label for="saco_cadera" class="form-label">Cadera:</label>
                                <input type="number" step="0.01" name="saco_cadera" id="saco_cadera" 
                                       class="form-control" value="{{ old('saco_cadera', $cliente->saco_cadera) }}" placeholder="cm">
                            </div>
                            <div class="col-md-3">
                                <label for="saco_alto_hombro" class="form-label">Alto de Hombro:</label>
                                <input type="number" step="0.01" name="saco_alto_hombro" id="saco_alto_hombro" 
                                       class="form-control" value="{{ old('saco_alto_hombro', $cliente->saco_alto_hombro) }}" placeholder="cm">
                            </div>
                            <div class="col-md-3">
                                <label for="saco_plomo_trasero" class="form-label">A Plomo Trasero:</label>
                                <input type="number" step="0.01" name="saco_plomo_trasero" id="saco_plomo_trasero" 
                                       class="form-control" value="{{ old('saco_plomo_trasero', $cliente->saco_plomo_trasero) }}" placeholder="cm">
                            </div>
                            <div class="col-md-3">
                                <label for="saco_plomo_delantero" class="form-label">A Plomo Delantero:</label>
                                <input type="number" step="0.01" name="saco_plomo_delantero" id="saco_plomo_delantero" 
                                       class="form-control" value="{{ old('saco_plomo_delantero', $cliente->saco_plomo_delantero) }}" placeholder="cm">
                            </div>
                            <div class="col-md-3">
                                <label for="saco_sisa" class="form-label">Sisa:</label>
                                <input type="number" step="0.01" name="saco_sisa" id="saco_sisa" 
                                       class="form-control" value="{{ old('saco_sisa', $cliente->saco_sisa) }}" placeholder="cm">
                            </div>
                            <div class="col-md-3">
                                <label for="saco_puno" class="form-label">Puño:</label>
                                <input type="number" step="0.01" name="saco_puno" id="saco_puno" 
                                       class="form-control" value="{{ old('saco_puno', $cliente->saco_puno) }}" placeholder="cm">
                            </div>
                        </div>

                        <!-- Medidas de Pantalón -->
                        <div class="row g-3 mb-4">
                            <div class="col-12">
                                <h5 class="text-secondary"><i class="fas fa-user-tie me-2"></i>PANTALÓN</h5>
                            </div>
                            <div class="col-md-3">
                                <label for="pantalon_largo" class="form-label">Largo:</label>
                                <input type="number" step="0.01" name="pantalon_largo" id="pantalon_largo" 
                                       class="form-control" value="{{ old('pantalon_largo', $cliente->pantalon_largo) }}" placeholder="cm">
                            </div>
                            <div class="col-md-3">
                                <label for="pantalon_cintura" class="form-label">Cintura:</label>
                                <input type="number" step="0.01" name="pantalon_cintura" id="pantalon_cintura" 
                                       class="form-control" value="{{ old('pantalon_cintura', $cliente->pantalon_cintura) }}" placeholder="cm">
                            </div>
                            <div class="col-md-3">
                                <label for="pantalon_cadera" class="form-label">Cadera:</label>
                                <input type="number" step="0.01" name="pantalon_cadera" id="pantalon_cadera" 
                                       class="form-control" value="{{ old('pantalon_cadera', $cliente->pantalon_cadera) }}" placeholder="cm">
                            </div>
                            <div class="col-md-3">
                                <label for="pantalon_entre_pierna" class="form-label">Entre-pierna:</label>
                                <input type="number" step="0.01" name="pantalon_entre_pierna" id="pantalon_entre_pierna" 
                                       class="form-control" value="{{ old('pantalon_entre_pierna', $cliente->pantalon_entre_pierna) }}" placeholder="cm">
                            </div>
                            <div class="col-md-3">
                                <label for="pantalon_muslo" class="form-label">Muslo:</label>
                                <input type="number" step="0.01" name="pantalon_muslo" id="pantalon_muslo" 
                                       class="form-control" value="{{ old('pantalon_muslo', $cliente->pantalon_muslo) }}" placeholder="cm">
                            </div>
                            <div class="col-md-3">
                                <label for="pantalon_rodilla" class="form-label">Rodilla:</label>
                                <input type="number" step="0.01" name="pantalon_rodilla" id="pantalon_rodilla" 
                                       class="form-control" value="{{ old('pantalon_rodilla', $cliente->pantalon_rodilla) }}" placeholder="cm">
                            </div>
                            <div class="col-md-3">
                                <label for="pantalon_bajo" class="form-label">Bajo:</label>
                                <input type="number" step="0.01" name="pantalon_bajo" id="pantalon_bajo" 
                                       class="form-control" value="{{ old('pantalon_bajo', $cliente->pantalon_bajo) }}" placeholder="cm">
                            </div>
                        </div>

                        <!-- Medidas de Chaleco -->
                        <div class="row g-3 mb-4">
                            <div class="col-12">
                                <h5 class="text-secondary"><i class="fas fa-tshirt me-2"></i>CHALECO</h5>
                            </div>
                            <div class="col-md-3">
                                <label for="chaleco_talle" class="form-label">Talle:</label>
                                <input type="number" step="0.01" name="chaleco_talle" id="chaleco_talle" 
                                       class="form-control" value="{{ old('chaleco_talle', $cliente->chaleco_talle) }}" placeholder="cm">
                            </div>
                            <div class="col-md-3">
                                <label for="chaleco_pecho" class="form-label">Pecho:</label>
                                <input type="number" step="0.01" name="chaleco_pecho" id="chaleco_pecho" 
                                       class="form-control" value="{{ old('chaleco_pecho', $cliente->chaleco_pecho) }}" placeholder="cm">
                            </div>
                            <div class="col-md-3">
                                <label for="chaleco_cintura" class="form-label">Cintura:</label>
                                <input type="number" step="0.01" name="chaleco_cintura" id="chaleco_cintura" 
                                       class="form-control" value="{{ old('chaleco_cintura', $cliente->chaleco_cintura) }}" placeholder="cm">
                            </div>
                            <div class="col-md-3">
                                <label for="chaleco_escote" class="form-label">Escote:</label>
                                <input type="number" step="0.01" name="chaleco_escote" id="chaleco_escote" 
                                       class="form-control" value="{{ old('chaleco_escote', $cliente->chaleco_escote) }}" placeholder="cm">
                            </div>
                            <div class="col-md-3">
                                <label for="chaleco_largo" class="form-label">Largo:</label>
                                <input type="number" step="0.01" name="chaleco_largo" id="chaleco_largo" 
                                       class="form-control" value="{{ old('chaleco_largo', $cliente->chaleco_largo) }}" placeholder="cm">
                            </div>
                            <div class="col-md-3">
                                <label for="chaleco_largo_trasero" class="form-label">Largo Trasero:</label>
                                <input type="number" step="0.01" name="chaleco_largo_trasero" id="chaleco_largo_trasero" 
                                       class="form-control" value="{{ old('chaleco_largo_trasero', $cliente->chaleco_largo_trasero) }}" placeholder="cm">
                            </div>
                            <div class="col-md-3">
                                <label for="chaleco_cuello" class="form-label">Cuello:</label>
                                <input type="number" step="0.01" name="chaleco_cuello" id="chaleco_cuello" 
                                       class="form-control" value="{{ old('chaleco_cuello', $cliente->chaleco_cuello) }}" placeholder="cm">
                            </div>
                        </div>

                        <!-- Observaciones -->
                        <div class="row g-3">
                            <div class="col-12">
                                <label for="observaciones_medidas" class="form-label">Observaciones de Medidas:</label>
                                <textarea name="observaciones_medidas" id="observaciones_medidas" 
                                          class="form-control" rows="3" 
                                          placeholder="Observaciones adicionales sobre las medidas...">{{ old('observaciones_medidas', $cliente->observaciones_medidas) }}</textarea>
                                @error('observaciones_medidas')
                                    <small class="text-danger">{{ '*' . $message }}</small>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-12 text-center">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-save me-2"></i>Actualizar Cliente
                    </button>
                    <a href="{{ route('clientes.index') }}" class="btn btn-secondary btn-lg ms-2">
                        <i class="fas fa-times me-2"></i>Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('js')
    <script>
        function toggleMedidas(tipo) {
            const content = document.getElementById('medidas-' + tipo);
            const icon = document.getElementById('icon-' + tipo);
            
            if (content.classList.contains('show')) {
                content.classList.remove('show');
                icon.classList.remove('fa-chevron-up');
                icon.classList.add('fa-chevron-down');
            } else {
                content.classList.add('show');
                icon.classList.remove('fa-chevron-down');
                icon.classList.add('fa-chevron-up');
            }
        }

        // Inicializar iconos según el estado inicial
        document.addEventListener('DOMContentLoaded', function() {
            // Verificar si las secciones están abiertas inicialmente
            if (document.getElementById('medidas-basicas').classList.contains('show')) {
                document.getElementById('icon-basicas').classList.remove('fa-chevron-down');
                document.getElementById('icon-basicas').classList.add('fa-chevron-up');
            }
            
            if (document.getElementById('medidas-completas').classList.contains('show')) {
                document.getElementById('icon-completas').classList.remove('fa-chevron-down');
                document.getElementById('icon-completas').classList.add('fa-chevron-up');
            }
        });

        // Validación de números
        document.querySelectorAll('input[type="number"]').forEach(input => {
            input.addEventListener('input', function() {
                if (this.value < 0) {
                    this.value = 0;
                }
                if (this.value > 999.99) {
                    this.value = 999.99;
                }
            });
        });
    </script>
@endpush
