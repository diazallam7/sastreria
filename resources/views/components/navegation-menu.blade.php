<div id="layoutSidenav_nav">
    <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
        <div class="sb-sidenav-menu">
            <div class="nav">

                <div class="sb-sidenav-menu-heading">Inicio</div>
                <a class="nav-link" href="{{ route('panel') }}">
                    <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                    Panel
                </a>
                <!---<div class="sb-sidenav-menu-heading">Interface</div>
                            <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseLayouts" aria-expanded="false" aria-controls="collapseLayouts">
                                <div class="sb-nav-link-icon"><i class="fas fa-columns"></i></div>
                                Layouts
                                <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                            </a>
                            <div class="collapse" id="collapseLayouts" aria-labelledby="headingOne" data-bs-parent="#sidenavAccordion">
                                <nav class="sb-sidenav-menu-nested nav">
                                    <a class="nav-link" href="layout-static.html">Static Navigation</a>
                                    <a class="nav-link" href="layout-sidenav-light.html">Light Sidenav</a>
                                </nav>
                            </div>
                            <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapsePages" aria-expanded="false" aria-controls="collapsePages">
                                <div class="sb-nav-link-icon"><i class="fas fa-book-open"></i></div>
                                Pages
                                <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                            </a>
                            <div class="collapse" id="collapsePages" aria-labelledby="headingTwo" data-bs-parent="#sidenavAccordion">
                                <nav class="sb-sidenav-menu-nested nav accordion" id="sidenavAccordionPages">
                                    <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#pagesCollapseAuth" aria-expanded="false" aria-controls="pagesCollapseAuth">
                                        Authentication
                                        <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                                    </a>
                                    <div class="collapse" id="pagesCollapseAuth" aria-labelledby="headingOne" data-bs-parent="#sidenavAccordionPages">
                                        <nav class="sb-sidenav-menu-nested nav">
                                            <a class="nav-link" href="login.html">Login</a>
                                            <a class="nav-link" href="register.html">Register</a>
                                            <a class="nav-link" href="password.html">Forgot Password</a>
                                        </nav>
                                    </div>
                                    <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#pagesCollapseError" aria-expanded="false" aria-controls="pagesCollapseError">
                                        Error
                                        <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                                    </a>
                                    <div class="collapse" id="pagesCollapseError" aria-labelledby="headingOne" data-bs-parent="#sidenavAccordionPages">
                                        <nav class="sb-sidenav-menu-nested nav">
                                            <a class="nav-link" href="401.html">401 Page</a>
                                            <a class="nav-link" href="404.html">404 Page</a>
                                            <a class="nav-link" href="500.html">500 Page</a>
                                        </nav>
                                    </div>
                                </nav>
                            </div>--->
                <div class="sb-sidenav-menu-heading">Modulos</div>
                @can('ver-compra')
                    <a class="nav-link" href="{{ route('compras.index') }}">
                        <div class="sb-nav-link-icon"><i class="fa-solid fa-cart-shopping"></i></div>
                        Compras
                    </a>
                @endcan

                @can('ver-venta')
                    <a class="nav-link" href="{{ route('ventas.index') }}">
                        <div class="sb-nav-link-icon"><i class="fa-solid fa-dollar-sign"></i></div>
                        Ventas
                    </a>
                @endcan

                    <a class="nav-link" href="{{ route('reservas.index') }}">
                        <div class="sb-nav-link-icon"><i class="fa-regular fa-pen-to-square"></i></div>
                        Reservas
                    </a>

                @can('ver-alquiler')
                    <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseVenta"
                        aria-expanded="false" aria-controls="collapseLayouts">
                        <div class="sb-nav-link-icon"><i class="fa-regular fa-handshake"></i></div>
                        Alquileres
                        <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                    </a>
                    <div class="collapse" id="collapseVenta" aria-labelledby="headingOne"
                        data-bs-parent="#sidenavAccordion">
                        <nav class="sb-sidenav-menu-nested nav">

                            <a class="nav-link" href="{{ route('stock.alquiler.index') }}">Stock de Alquiler</a>
                            <a class="nav-link" href="{{ route('alquileres.index') }}">Tabla de Alquiler</a>
                        </nav>
                    </div>
                @endcan


                <a class="nav-link collapsed" href="#" data-bs-toggle="collapse"
                    data-bs-target="#collapseDevoluciones" aria-expanded="false" aria-controls="collapseLayouts">
                    <div class="sb-nav-link-icon"><i class="fa-solid fa-retweet"></i></div>
                    Devoluciones
                    <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                </a>
                <div class="collapse" id="collapseDevoluciones" aria-labelledby="headingOne"
                    data-bs-parent="#sidenavAccordion">
                    <nav class="sb-sidenav-menu-nested nav">

                        <a class="nav-link" href="{{ route('devoluciones.index') }}">Pendientes</a>
                        <a class="nav-link" href="{{ route('devoluciones.historial') }}">Historial</a>
                    </nav>
                </div>

                @can('ver-cliente')
                    <a class="nav-link" href="{{ route('clientes.index') }}">
                        <div class="sb-nav-link-icon"><i class="fa-solid fa-user-group"></i></i></div>
                        Clientes
                    </a>
                @endcan


                <div class="sb-sidenav-menu-heading">OTROS</div>
                @can('ver-user')
                    <a class="nav-link" href="{{ route('users.index') }}">
                        <div class="sb-nav-link-icon"><i class="fa-solid fa-user-plus"></i></i></div>
                        Usuarios
                    </a>
                @endcan


                <!---@can('ver-role')
    <a class="nav-link" href="{{ route('roles.index') }}">
                                <div class="sb-nav-link-icon"><i class="fa-solid fa-shield-halved"></i></i></div>
                                Roles
                            </a>
@endcan -->

                <!---<a class="nav-link" href="{{ route('reportes.index') }}">
                    <div class="sb-nav-link-icon"><i class="fa-solid fa-pencil"></i></div>
                    Reportes y Estadisticas
                </a> -->
                @can('ver-compra')
                    <a class="nav-link" href="{{ route('configuraciones.index') }}">
                        <div class="sb-nav-link-icon"><i class="fa-solid fa-gear"></i></div>
                        Configuración de Multas
                    </a>
                @endcan

                @can('ver-compra')
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('gastos-varios.index') }}">
                            <div class="sb-nav-link-icon"><i class="fa-solid fa-money-bill"></i></div>
                            Gastos Varios
                        </a>
                    </li>
                @endcan

                @can('ver-compra')
                    <a class="nav-link" href="{{ route('cierre-caja.index') }}">
                        <div class="sb-nav-link-icon"><i class="fas fa-cash-register"></i></div>
                            Ingreso y Egresos
                    </a>
                @endcan


            </div>
        </div>
        <div class="sb-sidenav-footer">
            <div class="small">Bienvenido:</div>
            {{ auth()->user()->name }}
        </div>
    </nav>
</div>
