<div>
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <h1 class="text-2xl">Cierre de caja</h1>
        <div class="flex items-center gap-2">
            <input type="date" wire:model.live="fecha" class="rounded-lg border border-ink-200 px-3 py-2 text-sm focus:border-ink-400 focus:outline-none focus:ring-1 focus:ring-ink-400">
            @can('exportar-cierre-caja')
                <a href="{{ route('cierre-caja.pdf', ['fecha' => $fecha]) }}" target="_blank"
                    class="inline-flex items-center gap-2 rounded-lg border border-ink-200 bg-white px-4 py-2 text-sm font-medium text-ink-700 hover:bg-ink-50">
                    <i class="fa-solid fa-file-pdf"></i> PDF
                </a>
            @endcan
        </div>
    </div>

    <x-cierre.tabs activo="diario" />

    {{-- Totales --}}
    <div class="grid gap-4 sm:grid-cols-3">
        <div class="rounded-xl border border-ink-200 bg-white p-5 shadow-sm">
            <div class="text-xs uppercase tracking-wider text-ink-500">Ingresos</div>
            <div class="mt-1 font-serif text-2xl font-semibold text-green-600">₲ {{ number_format($movimientos['ingresos']['total'], 0, ',', '.') }}</div>
        </div>
        <div class="rounded-xl border border-ink-200 bg-white p-5 shadow-sm">
            <div class="text-xs uppercase tracking-wider text-ink-500">Egresos</div>
            <div class="mt-1 font-serif text-2xl font-semibold text-red-600">₲ {{ number_format($movimientos['egresos']['total'], 0, ',', '.') }}</div>
        </div>
        <div class="rounded-xl border border-ink-200 p-5 shadow-sm {{ $movimientos['saldo_neto'] >= 0 ? 'bg-ink-900 text-white' : 'bg-red-600 text-white' }}">
            <div class="text-xs uppercase tracking-wider opacity-70">Saldo neto</div>
            <div class="mt-1 font-serif text-2xl font-semibold">₲ {{ number_format($movimientos['saldo_neto'], 0, ',', '.') }}</div>
        </div>
    </div>

    {{-- Desglose --}}
    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        <div class="rounded-xl border border-ink-200 bg-white p-6 shadow-sm">
            <h2 class="mb-3 text-base font-semibold text-green-700">Ingresos</h2>
            <dl class="space-y-2 text-sm">
                @foreach (['alquileres' => 'Alquileres', 'multas_retraso' => 'Multas por retraso', 'ventas' => 'Ventas', 'ingresos_cancelaciones' => 'Cancelaciones (neto)'] as $k => $label)
                    <div class="flex justify-between"><dt class="text-ink-500">{{ $label }}</dt><dd class="tabular-nums">₲ {{ number_format($movimientos['ingresos'][$k], 0, ',', '.') }}</dd></div>
                @endforeach
                <div class="flex justify-between border-t border-ink-100 pt-2 font-medium"><dt>Total</dt><dd class="tabular-nums text-green-700">₲ {{ number_format($movimientos['ingresos']['total'], 0, ',', '.') }}</dd></div>
            </dl>
        </div>
        <div class="rounded-xl border border-ink-200 bg-white p-6 shadow-sm">
            <h2 class="mb-3 text-base font-semibold text-red-700">Egresos</h2>
            <dl class="space-y-2 text-sm">
                @foreach (['compras' => 'Compras', 'gastos_varios' => 'Gastos varios'] as $k => $label)
                    <div class="flex justify-between"><dt class="text-ink-500">{{ $label }}</dt><dd class="tabular-nums">₲ {{ number_format($movimientos['egresos'][$k], 0, ',', '.') }}</dd></div>
                @endforeach
                <div class="flex justify-between border-t border-ink-100 pt-2 font-medium"><dt>Total</dt><dd class="tabular-nums text-red-700">₲ {{ number_format($movimientos['egresos']['total'], 0, ',', '.') }}</dd></div>
            </dl>
        </div>
    </div>

    {{-- Detalle --}}
    @php $d = $movimientos['detalles']; @endphp
    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        <x-cierre.detalle titulo="Ventas" :vacio="$d['ventas']->isEmpty()">
            @foreach ($d['ventas'] as $v)
                <x-cierre.fila :nombre="$v->cliente?->nombre ?? 'Consumidor final'" :monto="$v->precio_total" />
            @endforeach
        </x-cierre.detalle>

        <x-cierre.detalle titulo="Alquileres iniciados" :vacio="$d['alquileres_iniciados']->isEmpty()">
            @foreach ($d['alquileres_iniciados'] as $a)
                <x-cierre.fila :nombre="$a->cliente?->nombre ?? '—'" :monto="$a->costo_total" />
            @endforeach
        </x-cierre.detalle>

        <x-cierre.detalle titulo="Multas (devoluciones)" :vacio="$d['devoluciones']->where('multa_aplicada', '>', 0)->isEmpty()">
            @foreach ($d['devoluciones']->where('multa_aplicada', '>', 0) as $dev)
                <x-cierre.fila :nombre="$dev->alquiler?->cliente?->nombre ?? '—'" :monto="$dev->multa_aplicada" />
            @endforeach
        </x-cierre.detalle>

        <x-cierre.detalle titulo="Compras" :vacio="$d['compras']->isEmpty()">
            @foreach ($d['compras'] as $c)
                <x-cierre.fila :nombre="$c->nombre" :monto="$c->precio_compra * $c->talles->sum('cantidad_total')" negativo />
            @endforeach
        </x-cierre.detalle>

        <x-cierre.detalle titulo="Gastos varios" :vacio="$d['gastos']->isEmpty()">
            @foreach ($d['gastos'] as $g)
                <x-cierre.fila :nombre="$g->nombre_gasto" :monto="$g->monto" negativo />
            @endforeach
        </x-cierre.detalle>

        <x-cierre.detalle titulo="Cancelaciones" :vacio="$d['cancelaciones']->isEmpty()">
            @foreach ($d['cancelaciones'] as $r)
                <x-cierre.fila :nombre="$r->cliente?->nombre ?? '—'" :monto="$r->total_recibido - ($r->senia_devuelta ?? 0)" />
            @endforeach
        </x-cierre.detalle>
    </div>
</div>
