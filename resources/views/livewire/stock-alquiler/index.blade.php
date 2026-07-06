<div>
    <div class="mb-6 flex items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl">Stock de alquiler</h1>
            <p class="text-sm text-ink-500">Prendas disponibles para alquilar.</p>
        </div>
        @can('crear-stock-alquiler')
            <a href="{{ route('stock.alquiler.create') }}" wire:navigate
                class="inline-flex items-center gap-2 rounded-lg bg-ink-900 px-4 py-2 text-sm font-medium text-white hover:bg-ink-800">
                <i class="fa-solid fa-plus"></i> Nueva prenda
            </a>
        @endcan
    </div>

    <div class="rounded-xl border border-ink-200 bg-white shadow-sm">
        <div class="border-b border-ink-100 p-4">
            <div class="relative max-w-sm">
                <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-sm text-ink-400"></i>
                <input type="search" wire:model.live.debounce.300ms="buscar" placeholder="Buscar por nombre o código…"
                    class="w-full rounded-lg border border-ink-200 bg-ink-50 py-2 pl-9 pr-3 text-sm focus:border-ink-400 focus:outline-none focus:ring-1 focus:ring-ink-400">
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="text-left text-xs uppercase tracking-wider text-ink-500">
                    <tr class="border-b border-ink-100">
                        <th class="px-4 py-3 font-medium">Código</th>
                        <th class="px-4 py-3 font-medium">Nombre</th>
                        <th class="px-4 py-3 text-right font-medium">Precio alquiler</th>
                        <th class="px-4 py-3 text-right font-medium">Disponible</th>
                        <th class="px-4 py-3 text-right font-medium">Alquilado</th>
                        <th class="px-4 py-3 text-right font-medium">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-ink-50">
                    @forelse ($items as $item)
                        <tr class="hover:bg-ink-50/50" wire:key="stock-{{ $item->id }}">
                            <td class="px-4 py-3 font-mono text-xs text-ink-600">{{ $item->codigo }}</td>
                            <td class="px-4 py-3 font-medium text-ink-900">{{ $item->nombre }}</td>
                            <td class="px-4 py-3 text-right tabular-nums">₲ {{ number_format($item->precio_alquiler, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right tabular-nums {{ (int) $item->disponible > 0 ? 'text-green-600' : 'text-red-500' }}">{{ (int) $item->disponible }}</td>
                            <td class="px-4 py-3 text-right tabular-nums text-ink-500">{{ (int) $item->alquilado }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-1 text-ink-500">
                                    <a href="{{ route('stock.alquiler.show', $item) }}" wire:navigate data-tip="Ver"
                                        class="rounded p-2 hover:bg-ink-100 hover:text-ink-900"><i class="fa-solid fa-eye"></i></a>
                                    @can('editar-stock-alquiler')
                                        <a href="{{ route('stock.alquiler.edit', $item) }}" wire:navigate data-tip="Editar"
                                            class="rounded p-2 hover:bg-ink-100 hover:text-ink-900"><i class="fa-solid fa-pen"></i></a>
                                    @endcan
                                    @can('eliminar-stock-alquiler')
                                        <button x-on:click="$store.confirm.open('¿Eliminar esta prenda?', () => $wire.eliminar({{ $item->id }}))" data-tip="Eliminar"
                                            class="rounded p-2 hover:bg-red-50 hover:text-red-600"><i class="fa-solid fa-trash"></i></button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-10 text-center text-ink-400">
                                <i class="fa-solid fa-warehouse mb-2 block text-2xl"></i>
                                No hay prendas en stock.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-ink-100 p-4">
            {{ $items->links() }}
        </div>
    </div>
</div>
