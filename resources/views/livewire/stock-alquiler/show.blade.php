<div>
    <div class="mb-6 flex items-start justify-between gap-4">
        <div>
            <a href="{{ route('stock.alquiler.index') }}" wire:navigate class="text-sm text-ink-500 hover:text-ink-800">
                <i class="fa-solid fa-arrow-left"></i> Stock de alquiler
            </a>
            <h1 class="mt-1 text-2xl">{{ $item->nombre }}</h1>
            <p class="text-sm text-ink-500">
                <span class="font-mono">{{ $item->codigo }}</span> · ₲ {{ number_format($item->precio_alquiler, 0, ',', '.') }} por alquiler
            </p>
        </div>
        @can('editar-stock-alquiler')
            <a href="{{ route('stock.alquiler.edit', $item) }}" wire:navigate
                class="inline-flex items-center gap-2 rounded-lg border border-ink-200 bg-white px-4 py-2 text-sm font-medium text-ink-700 hover:bg-ink-50">
                <i class="fa-solid fa-pen"></i> Editar
            </a>
        @endcan
    </div>

    @if ($item->descripcion)
        <div class="mb-6 rounded-xl border border-ink-200 bg-white p-6 shadow-sm">
            <h2 class="mb-1 text-sm font-semibold text-ink-500">Descripción</h2>
            <p class="text-sm text-ink-700">{{ $item->descripcion }}</p>
        </div>
    @endif

    <div class="rounded-xl border border-ink-200 bg-white p-6 shadow-sm">
        <h2 class="mb-4 text-base font-semibold">Talles</h2>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="text-left text-xs uppercase tracking-wider text-ink-500">
                    <tr class="border-b border-ink-100">
                        <th class="py-2 pr-4 font-medium">Talle</th>
                        <th class="py-2 pr-4 text-right font-medium">Total</th>
                        <th class="py-2 pr-4 text-right font-medium">Disponible</th>
                        <th class="py-2 pr-4 text-right font-medium">Alquilado</th>
                        <th class="py-2 text-right font-medium">Reservado</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-ink-50">
                    @forelse ($item->talles as $t)
                        <tr>
                            <td class="py-2 pr-4 font-medium text-ink-800">{{ $t->talle }}</td>
                            <td class="py-2 pr-4 text-right tabular-nums">{{ $t->cantidad_total }}</td>
                            <td class="py-2 pr-4 text-right tabular-nums {{ $t->cantidad_disponible > 0 ? 'text-green-600' : 'text-red-500' }}">{{ $t->cantidad_disponible }}</td>
                            <td class="py-2 pr-4 text-right tabular-nums text-ink-500">{{ $t->cantidad_alquilada }}</td>
                            <td class="py-2 text-right tabular-nums text-ink-500">{{ $t->cantidad_reservada }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-6 text-center text-ink-400">Sin talles.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
