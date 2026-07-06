<!DOCTYPE html>
<html lang="es" class="h-full">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>{{ $title ?? 'Sastrería Medina' }}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="h-full">
    <div class="min-h-full"
        x-data="{ collapsed: (localStorage.getItem('sb_collapsed') === 'true'), mobile: false }"
        x-init="$watch('collapsed', v => localStorage.setItem('sb_collapsed', v))">
        {{-- Sidebar --}}
        <aside
            class="fixed inset-y-0 left-0 z-40 flex flex-col bg-ink-950 text-ink-200 transition-all duration-200 w-64 lg:translate-x-0"
            :class="{ 'lg:w-16': collapsed, 'lg:w-64': !collapsed, 'translate-x-0': mobile, '-translate-x-full': !mobile, 'is-collapsed': collapsed }">
            <div class="flex h-16 items-center gap-3 border-b border-white/5 px-5">
                <span class="text-xl text-accent-400"><i class="fa-solid fa-scissors"></i></span>
                <span class="brand-text font-serif text-lg font-bold tracking-wide text-white">Medina</span>
            </div>

            <nav class="sidebar-scroll flex-1 space-y-0.5 overflow-y-auto px-3 py-4 text-sm">
                @php
                    $link = fn ($pattern) => request()->routeIs($pattern)
                        ? 'nav-link flex items-center gap-3 rounded-lg bg-accent-500/15 px-3 py-2 font-medium text-accent-300'
                        : 'nav-link flex items-center gap-3 rounded-lg px-3 py-2 text-ink-300 transition hover:bg-white/5 hover:text-white';
                @endphp

                <a href="{{ route('panel') }}" class="{{ $link('panel') }}">
                    <i class="fa-solid fa-gauge-high w-5 text-center"></i><span class="nav-label">Panel</span>
                </a>

                <p class="nav-section px-3 pb-1 pt-4 text-[11px] uppercase tracking-widest text-ink-500">Operación</p>
                @can('ver-venta')
                    <a href="{{ route('ventas.index') }}" class="{{ $link('ventas.*') }}"><i class="fa-solid fa-cart-shopping w-5 text-center"></i><span class="nav-label">Ventas</span></a>
                @endcan
                @can('ver-alquiler')
                    <a href="{{ route('alquileres.index') }}" class="{{ $link('alquileres.*') }}"><i class="fa-solid fa-shirt w-5 text-center"></i><span class="nav-label">Alquileres</span></a>
                @endcan
                @can('ver-reserva')
                    <a href="{{ route('reservas.index') }}" class="{{ $link('reservas.*') }}"><i class="fa-solid fa-calendar-check w-5 text-center"></i><span class="nav-label">Reservas</span></a>
                @endcan
                @can('ver-devolucion')
                    <a href="{{ route('devoluciones.index') }}" class="{{ $link('devoluciones.*') }}"><i class="fa-solid fa-rotate-left w-5 text-center"></i><span class="nav-label">Devoluciones</span></a>
                @endcan

                <p class="nav-section px-3 pb-1 pt-4 text-[11px] uppercase tracking-widest text-ink-500">Catálogo</p>
                @can('ver-cliente')
                    <a href="{{ route('clientes.index') }}" class="{{ $link('clientes.*') }}"><i class="fa-solid fa-users w-5 text-center"></i><span class="nav-label">Clientes</span></a>
                @endcan
                @can('ver-producto')
                    <a href="{{ route('productos.index') }}" class="{{ $link('productos.*') }}"><i class="fa-solid fa-box w-5 text-center"></i><span class="nav-label">Productos</span></a>
                @endcan
                @can('ver-stock-alquiler')
                    <a href="{{ route('stock.alquiler.index') }}" class="{{ $link('stock.*') }}"><i class="fa-solid fa-warehouse w-5 text-center"></i><span class="nav-label">Stock alquiler</span></a>
                @endcan
                <a href="{{ route('gastos-varios.index') }}" class="{{ $link('gastos-varios.*') }}"><i class="fa-solid fa-receipt w-5 text-center"></i><span class="nav-label">Gastos</span></a>

                <p class="nav-section px-3 pb-1 pt-4 text-[11px] uppercase tracking-widest text-ink-500">Reportes</p>
                @can('ver-cierre-caja')
                    <a href="{{ route('cierre-caja.index') }}" class="{{ $link('cierre-caja.*') }}"><i class="fa-solid fa-cash-register w-5 text-center"></i><span class="nav-label">Cierre de caja</span></a>
                @endcan
                <a href="{{ route('reportes.index') }}" class="{{ $link('reportes.*') }}"><i class="fa-solid fa-chart-line w-5 text-center"></i><span class="nav-label">Reportes</span></a>

                @canany(['ver-user', 'ver-role'])
                    <p class="nav-section px-3 pb-1 pt-4 text-[11px] uppercase tracking-widest text-ink-500">Administración</p>
                    @can('ver-user')
                        <a href="{{ route('users.index') }}" class="{{ $link('users.*') }}"><i class="fa-solid fa-user-gear w-5 text-center"></i><span class="nav-label">Usuarios</span></a>
                    @endcan
                    @can('ver-role')
                        <a href="{{ route('roles.index') }}" class="{{ $link('roles.*') }}"><i class="fa-solid fa-shield-halved w-5 text-center"></i><span class="nav-label">Roles</span></a>
                    @endcan
                    <a href="{{ route('configuraciones.index') }}" class="{{ $link('configuraciones.*') }}"><i class="fa-solid fa-gear w-5 text-center"></i><span class="nav-label">Configuración</span></a>
                @endcanany
            </nav>
        </aside>

        {{-- Overlay móvil --}}
        <div x-show="mobile" x-cloak @click="mobile = false" class="fixed inset-0 z-30 bg-black/40 lg:hidden"></div>

        {{-- Contenido --}}
        <div class="transition-all duration-200" :class="collapsed ? 'lg:pl-16' : 'lg:pl-64'">
            <header class="sticky top-0 z-20 flex h-16 items-center gap-3 border-b border-ink-200 bg-white/80 px-4 backdrop-blur sm:px-6">
                {{-- Toggle único: colapsa en desktop, abre overlay en móvil --}}
                <button type="button" data-tip="Menú"
                    @click="window.innerWidth < 1024 ? (mobile = !mobile) : (collapsed = !collapsed)"
                    class="text-xl text-ink-600 hover:text-ink-900">
                    <i class="fa-solid fa-bars"></i>
                </button>

                <div class="flex-1"></div>

                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" class="flex items-center gap-2 text-sm text-ink-700 hover:text-ink-900">
                        <span class="grid h-8 w-8 place-items-center rounded-full bg-ink-900 text-xs font-semibold text-white">
                            {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                        </span>
                        <span class="hidden sm:block">{{ auth()->user()->name ?? '' }}</span>
                        <i class="fa-solid fa-chevron-down text-xs"></i>
                    </button>
                    <div x-show="open" x-cloak @click.outside="open = false"
                        class="absolute right-0 mt-2 w-48 rounded-lg border border-ink-200 bg-white py-1 text-sm shadow-lg">
                        <a href="{{ route('profiles.index') }}" class="block px-4 py-2 text-ink-700 hover:bg-ink-50">Mi perfil</a>
                        <hr class="my-1 border-ink-100">
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="w-full px-4 py-2 text-left text-red-600 hover:bg-red-50">Cerrar sesión</button>
                        </form>
                    </div>
                </div>
            </header>

            <main class="p-4 sm:p-6 lg:p-8">
                @if (session('success'))
                    <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                        <i class="fa-solid fa-circle-check mr-1"></i> {{ session('success') }}
                    </div>
                @endif
                @if (session('error'))
                    <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                        <i class="fa-solid fa-circle-exclamation mr-1"></i> {{ session('error') }}
                    </div>
                @endif

                {{ $slot }}
            </main>
        </div>

        {{-- Modal de confirmación global (reemplaza confirm() nativo) --}}
        <div x-data x-show="$store.confirm.show" x-cloak
            class="fixed inset-0 z-[100] flex items-center justify-center p-4"
            x-transition.opacity>
            <div class="fixed inset-0 bg-black/50" @click="$store.confirm.close()"></div>
            <div class="relative w-full max-w-sm rounded-xl bg-white p-6 shadow-xl"
                @keydown.escape.window="$store.confirm.close()">
                <div class="mb-5 flex items-start gap-3">
                    <span class="grid h-10 w-10 shrink-0 place-items-center rounded-full bg-red-100 text-red-600">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                    </span>
                    <div>
                        <h3 class="text-base font-semibold text-ink-900">Confirmar acción</h3>
                        <p class="mt-1 text-sm text-ink-600" x-text="$store.confirm.message"></p>
                    </div>
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" @click="$store.confirm.close()"
                        class="rounded-lg border border-ink-200 px-4 py-2 text-sm font-medium text-ink-700 hover:bg-ink-50">Cancelar</button>
                    <button type="button" @click="$store.confirm.accept()"
                        class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700">Confirmar</button>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
