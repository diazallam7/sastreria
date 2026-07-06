<div>
    <div class="mb-6">
        <h1 class="text-2xl">Panel</h1>
        <p class="text-sm text-ink-500">Resumen del negocio — {{ now()->translatedFormat('d \d\e F, Y') }}</p>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @php
            $tarjetas = [
                ['Ventas hoy', '₲ ' . number_format($ventasHoy, 0, ',', '.'), 'fa-cart-shopping', 'text-accent-600'],
                ['Ventas del mes', '₲ ' . number_format($ventasMes, 0, ',', '.'), 'fa-chart-line', 'text-green-600'],
                ['Clientes', number_format($clientes, 0, ',', '.'), 'fa-users', 'text-ink-700'],
                ['Productos', number_format($productos, 0, ',', '.'), 'fa-box', 'text-ink-700'],
            ];
        @endphp

        @foreach ($tarjetas as [$titulo, $valor, $icono, $color])
            <div class="rounded-xl border border-ink-200 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <span class="text-xs uppercase tracking-wider text-ink-500">{{ $titulo }}</span>
                    <i class="fa-solid {{ $icono }} {{ $color }}"></i>
                </div>
                <p class="mt-3 text-2xl font-serif font-semibold text-ink-900">{{ $valor }}</p>
            </div>
        @endforeach
    </div>
</div>
