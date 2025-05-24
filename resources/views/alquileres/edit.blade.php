@extends('template')

@section('title', 'Ver Compra')

@push('css')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
@endpush

@section('content')
    <div class="container-fluid px-4">
        <h1 class="mt-4 text-center">Ver Ventas</h1>
        <ol class="breadcrumb mb-4">
            <li class="breadcrumb-item active"><a href="{{ route('panel') }}">Inicio</a></li>
            <li class="breadcrumb-item"><a href="{{ route('compras.index') }}">Ventas</a></li>
            <li class="breadcrumb-item active">Ver Ventas</li>
        </ol>
    </div>

    <div class="container w-100 border border-3 border-secondary rounded p-4 mt-3">

        <div class="card p-2 mb-4">

            <!--Tipo de Comprobante-->
            <div class="row mb-2">
                <div class="col-sm-4">
                    <div class="input-group mb-3">
                        <span class="input-group-text"><i class="fa-solid fa-file"></i></span>
                        <input disabled type="text" class="form-control" value="Tipo de Comprobante: ">
                    </div>
                </div>
                <div class="col-sm-8">
                    <input disabled type="text" class="form-control"
                        value="{{ $compra->comprobante->tipo_comprobante }}">
                </div>
            </div>

            <!--Numero de Comprobante-->
            <div class="row mb-2">
                <div class="col-sm-4">
                    <div class="input-group mb-3">
                        <span class="input-group-text"><i class="fa-solid fa-hashtag"></i></span>
                        <input disabled type="text" class="form-control" value="Numero de Comprobante: ">
                    </div>
                </div>
                <div class="col-sm-8">
                    <input disabled type="text" class="form-control" value="{{ $compra->numero_comprobante }}">
                </div>
            </div>

            <!--Fecha-->
            <div class="row mb-2">
                <div class="col-sm-4">
                    <div class="input-group mb-3">
                        <span class="input-group-text"><i class="fa-solid fa-calendar-days"></i></span>
                        <input disabled type="text" class="form-control" value="Fecha: ">
                    </div>
                </div>
                <div class="col-sm-8">
                    <input disabled type="text" class="form-control"
                        value="{{ \Carbon\Carbon::parse($compra->fecha_hora)->format('d-m-Y') }}">
                </div>
            </div>

            <!--Hora-->
            <div class="row mb-2">
                <div class="col-sm-4">
                    <div class="input-group mb-3">
                        <span class="input-group-text"><i class="fa-solid fa-clock"></i></span>
                        <input disabled type="text" class="form-control" value="Hora: ">
                    </div>
                </div>
                <div class="col-sm-8">
                    <input disabled type="text" class="form-control"
                        value="{{ \Carbon\Carbon::parse($compra->fecha_hora)->format('H:i') }}">
                </div>
            </div>


        <!--Tabla producto-->
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-table me-1"></i>
                Tabla de detalles de la venta
            </div>
            <div class="card-body table-responsive">
                <table class="table table-striped">
                    <thead class="bg-primary text-white">
                        <tr>
                            <th>Producto</th>
                            <th>Cantidad</th>
                            <th>Precio de Compra</th>
                            <th>Precio de Venta</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($items as $item)
                            <tr>
                                <td>
                                    @foreach ($items as $item)
                                        @if(isset($item->nombre_del_producto))
                                            <!-- Mostrar nombre de productos -->
                                            <p class="fw-semibold mb-1">{{ $item->nombre_del_producto }}</p>
                                        @elseif(isset($item->nombre_producto))
                                            <!-- Mostrar nombre de productos desde ventas -->
                                            <p class="fw-semibold mb-1">{{ $item->nombre_producto }}</p>
                                        @endif
                                    @endforeach
                                </td>
                                
                                <td>{{ $item->pivot->cantidad }}</td>
                                    <td>{{ $item->pivot->precio_venta }}</td>
                                    <td>{{ $item->pivot->precio_compra }}</td>
                                <td class="sub-total">{{ $item->pivot->cantidad * $item->pivot->precio_venta }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="4">Sumas:</th>
                            <th id="th-sumas"></th>
                        </tr>
                        <tr>
                            <th colspan="4">Total:</th>
                            <th id="th-total"></th>
                        </tr>
                    </tfoot>
                </table>
                
            </div>
        </div>

    </div>

@endsection

@push('js')
    <script>
        //variables
        let filasSubtotal = document.getElementsByClassName('sub-total');
        let cont = 0;

        $(document).ready(function() {
            calcularValores();
        });

        function calcularValores() {
            for (let i = 0; i < filasSubtotal.length; i++) {
                cont += parseFloat(filasSubtotal[i].innerHTML);
            }
            $('#th-sumas').html(cont);
        }
    </script>
@endpush
