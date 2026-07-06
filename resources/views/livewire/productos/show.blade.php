<div>
    <div class="mb-6 flex items-start justify-between gap-4">
        <div>
            <a href="{{ route('productos.index') }}" wire:navigate class="text-sm text-ink-500 hover:text-ink-800">
                <i class="fa-solid fa-arrow-left"></i> Productos
            </a>
            <h1 class="mt-1 text-2xl">{{ $producto->nombre }}</h1>
            <div class="mt-1 flex items-center gap-2">
                <span class="rounded-full px-2.5 py-0.5 text-xs font-medium {{ $producto->tipo->esComprado() ? 'bg-blue-100 text-blue-700' : 'bg-accent-100 text-accent-800' }}">
                    {{ $producto->tipo->label() }}
                </span>
                <span class="rounded-full px-2.5 py-0.5 text-xs font-medium {{ $producto->activo_para_venta ? 'bg-green-100 text-green-700' : 'bg-ink-100 text-ink-500' }}">
                    {{ $producto->activo_para_venta ? 'Activo' : 'Inactivo' }}
                </span>
            </div>
        </div>
        @can('editar-producto')
            <a href="{{ route('productos.edit', $producto) }}" wire:navigate
                class="inline-flex items-center gap-2 rounded-lg border border-ink-200 bg-white px-4 py-2 text-sm font-medium text-ink-700 hover:bg-ink-50">
                <i class="fa-solid fa-pen"></i> Editar
            </a>
        @endcan
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="rounded-xl border border-ink-200 bg-white p-6 shadow-sm">
            <h2 class="mb-4 text-base font-semibold">Detalle</h2>
            <dl class="space-y-3 text-sm">
                <div class="flex justify-between"><dt class="text-ink-400">Precio de venta</dt><dd class="font-medium">₲ {{ number_format($producto->precio_venta, 0, ',', '.') }}</dd></div>
                @if ($producto->tipo->esComprado())
                    <div class="flex justify-between"><dt class="text-ink-400">Precio de compra</dt><dd>₲ {{ number_format($producto->precio_compra, 0, ',', '.') }}</dd></div>
                    <div class="flex justify-between"><dt class="text-ink-400">Fecha de compra</dt><dd>{{ $producto->fecha_compra?->format('d/m/Y') ?? '—' }}</dd></div>
                @endif
                <div class="flex justify-between"><dt class="text-ink-400">Stock total</dt><dd>{{ $producto->cantidad_disponible }} disp.</dd></div>
                @if ($producto->observacion)
                    <div><dt class="text-ink-400">Observación</dt><dd class="text-ink-700">{{ $producto->observacion }}</dd></div>
                @endif
            </dl>
        </div>

        <div class="rounded-xl border border-ink-200 bg-white p-6 shadow-sm lg:col-span-2">
            <h2 class="mb-4 text-base font-semibold">Talles</h2>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="text-left text-xs uppercase tracking-wider text-ink-500">
                        <tr class="border-b border-ink-100">
                            <th class="py-2 pr-4 font-medium">Talle</th>
                            <th class="py-2 pr-4 text-right font-medium">Total</th>
                            <th class="py-2 pr-4 text-right font-medium">Disponible</th>
                            <th class="py-2 text-right font-medium">Vendido</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-ink-50">
                        @forelse ($producto->talles as $t)
                            <tr>
                                <td class="py-2 pr-4 font-medium text-ink-800">{{ $t->talle }}</td>
                                <td class="py-2 pr-4 text-right tabular-nums">{{ $t->cantidad_total }}</td>
                                <td class="py-2 pr-4 text-right tabular-nums {{ $t->cantidad_disponible > 0 ? 'text-green-600' : 'text-red-500' }}">{{ $t->cantidad_disponible }}</td>
                                <td class="py-2 text-right tabular-nums text-ink-500">{{ $t->cantidad_vendida }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="py-6 text-center text-ink-400">Sin talles.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
