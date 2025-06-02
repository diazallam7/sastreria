@extends('template')

@section('title', 'Panel')

@push('css')
    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endpush

@section('content')

    @if (session('success'))
        <script>
            // script para que slaga la alerta
            let message = "{{ session('success') }}";
            Swal.fire({
                title: message,
                showClass: {
                    popup: `
      animate__animated
      animate__fadeInUp
      animate__faster
    `
                },
                hideClass: {
                    popup: `
      animate__animated
      animate__fadeOutDown
      animate__faster
    `
                }
            });
        </script>
    @endif

    <div class="container-fluid px-4">
        <h1 class="mt-4 text-center">Panel</h1>
        <ol class="breadcrumb mb-4">
            <li class="breadcrumb-item active"></li>
        </ol>
        <div class="row mt-5">
            <div class="col-xl-3 col-md-6">
                <div class="card bg-warning text-white mb-4">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-8">
                                <i class="fa-solid fa-retweet"></i><span class="m-1">Alquileres</span>
                            </div>
                            <div class="col-4">
                                <?php
                                use App\Models\Alquiler;
                                $ventas = count(Alquiler::all());
                                ?>
                                <p class="text-center fw-bold fs-4">{{ $ventas }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer d-flex align-items-center justify-content-between">
                        <a class="small text-white stretched-link" href="{{ route('alquileres.index') }}">Ver Mas</a>
                        <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                    </div>
                </div>
            </div>
            @can('ver-cierre-caja')
            <div class="col-xl-3 col-md-6">
                <div class="card bg-success text-white mb-4">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-8">
                                <i class="fa-solid fa-money-bill-trend-up"></i><span class="m-1">Ingresos y Egresos</span>
                            </div>
                            <div class="col-4">
                                <h6 class="text-success">.</h6>
                                <h6 class="text-success">.</h6>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer d-flex align-items-center justify-content-between">
                        <a class="small text-white stretched-link" href="{{ route('cierre-caja.index') }}">Ver Mas</a>
                        <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                    </div>
                </div>
            </div>
            @endcan
            <div class="col-xl-3 col-md-6">
                <div class="card bg-primary text-white mb-4">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-8">
                                <i class="fa-solid fa-user-group"></i><span class="m-1">Clientes</span>
                            </div>
                            <div class="col-4">
                                <?php
                                use App\Models\Cliente;
                                $ventas = count(Cliente::all());
                                ?>
                                <p class="text-center fw-bold fs-4">{{ $ventas }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer d-flex align-items-center justify-content-between">
                        <a class="small text-white stretched-link" href="{{ route('clientes.index') }}">Ver Mas</a>
                        <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card bg-danger text-white mb-4">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-8">
                                <i class="fa-solid fa-arrows-turn-to-dots"></i><span class="m-1">Devoluciones</span>
                            </div>
                            <div class="col-4">

                                <h6 class="text-danger">.</h6>
                                <h6 class="text-danger">.</h6>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer d-flex align-items-center justify-content-between">
                        <a class="small text-white stretched-link" href="{{ route('devoluciones.index') }}">Ver Mas</a>
                        <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                    </div>
                </div>
            </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card bg-danger text-white mb-4">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-8">
                                    <i class="fas fa-receipt me-2"></i><span class="m-1">Gastos Varios</span>
                                </div>
                                <div class="col-4">

                                    <h6 class="text-danger">.</h6>
                                    <h6 class="text-danger">.</h6>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer d-flex align-items-center justify-content-between">
                            <a class="small text-white stretched-link" href="{{ route('gastos-varios.index') }}">Ver Mas</a>
                            <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                        </div>
                    </div>
                </div>

            <div class="col-xl-3 col-md-6">
                <div class="card bg-primary text-white mb-4">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-8">
                                <i class="fa-solid fa-dollar-sign"></i><span class="m-1">Ventas</span>
                            </div>
                            <div class="col-4">
                                <?php
                                use App\Models\Venta;
                                $compras = count(Venta::all());
                                ?>
                                <p class="text-center fw-bold fs-4">{{ $compras }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer d-flex align-items-center justify-content-between">
                        <a class="small text-white stretched-link" href="{{ route('ventas.index') }}">Ver Mas</a>
                        <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                    </div>
                </div>
            </div>
            @can('ver-compra')
                <div class="col-xl-3 col-md-6">
                    <div class="card bg-success text-white mb-4">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-8">
                                    <i class="fa-solid fa-cart-shopping"></i><span class="m-1">Compras</span>
                                </div>
                                <div class="col-4">
                                    <p class="text-center fw-bold fs-4">{{ $compras }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer d-flex align-items-center justify-content-between">
                            <a class="small text-white stretched-link" href="{{ route('compras.index') }}">Ver Mas</a>
                            <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                        </div>
                    </div>
                </div>
            @endcan

            @can('ver-user')
                <div class="col-xl-3 col-md-6">
                    <div class="card bg-warning text-white mb-4">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-8">
                                    <i class="fa-solid fa-user-plus"></i><span class="m-1">Usuarios</span>
                                </div>
                                <div class="col-4">
                                    <p class="text-center fw-bold fs-4">{{ $users }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer d-flex align-items-center justify-content-between">
                            <a class="small text-white stretched-link" href="{{ route('users.index') }}">Ver Mas</a>
                            <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                        </div>
                    </div>
                </div>
            @endcan


        </div>
    </div>

@endsection

@push('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js" crossorigin="anonymous"></script>
    <script src="{{ asset('assets/demo/chart-area-demo.js') }}"></script>
    <script src="{{ asset('assets/demo/chart-bar-demo.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/simple-datatables@latest" crossorigin="anonymous"></script>
    <script src="{{ asset('js/datatables-simple-demo.js') }}"></script>
@endpush
