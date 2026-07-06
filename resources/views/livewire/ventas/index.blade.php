<div>
    <div class="mb-6 flex items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl">Ventas</h1>
            <p class="text-sm text-ink-500">Registro de ventas.</p>
        </div>
        @can('crear-venta')
            <a href="{{ route('ventas.create') }}" wire:navigate
                class="inline-flex items-center gap-2 rounded-lg bg-ink-900 px-4 py-2 text-sm font-medium text-white hover:bg-ink-800">
                <i class="fa-solid fa-plus"></i> Nueva venta
            </a>
        @endcan
    </div>

    <div class="rounded-xl border border-ink-200 bg-white shadow-sm">
        <div class="border-b border-ink-100 p-4">
            <div class="relative max-w-sm">
                <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-sm text-ink-400"></i>
                <input type="search" wire:model.live.debounce.300ms="buscar" placeholder="Buscar por cliente…"
                    class="w-full rounded-lg border border-ink-200 bg-ink-50 py-2 pl-9 pr-3 text-sm focus:border-ink-400 focus:outline-none focus:ring-1 focus:ring-ink-400">
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="text-left text-xs uppercase tracking-wider text-ink-500">
                    <tr class="border-b border-ink-100">
                        <th class="px-4 py-3 font-medium">#</th>
                        <th class="px-4 py-3 font-medium">Fecha</th>
                        <th class="px-4 py-3 font-medium">Cliente</th>
                        <th class="px-4 py-3 font-medium">Cajero</th>
                        <th class="px-4 py-3 text-right font-medium">Ítems</th>
                        <th class="px-4 py-3 text-right font-medium">Total</th>
                        <th class="px-4 py-3 text-right font-medium">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-ink-50">
                    @forelse ($ventas as $v)
                        <tr class="hover:bg-ink-50/50" wire:key="venta-{{ $v->id }}">
                            <td class="px-4 py-3 text-ink-500">#{{ $v->id }}</td>
                            <td class="px-4 py-3 text-ink-600">{{ $v->fecha_venta->format('d/m/Y') }}</td>
                            <td class="px-4 py-3 font-medium text-ink-900">{{ $v->cliente?->nombre ?? 'Consumidor final' }}</td>
                            <td class="px-4 py-3 text-ink-600">{{ $v->user?->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-right tabular-nums">{{ $v->detalles_count }}</td>
                            <td class="px-4 py-3 text-right font-medium tabular-nums">₲ {{ number_format($v->precio_total, 0, ',', '.') }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-1 text-ink-500">
                                    <a href="{{ route('ventas.show', $v) }}" wire:navigate data-tip="Ver"
                                        class="rounded p-2 hover:bg-ink-100 hover:text-ink-900"><i class="fa-solid fa-eye"></i></a>
                                    @can('editar-venta')
                                        <a href="{{ route('ventas.edit', $v) }}" wire:navigate data-tip="Editar"
                                            class="rounded p-2 hover:bg-ink-100 hover:text-ink-900"><i class="fa-solid fa-pen"></i></a>
                                    @endcan
                                    @can('eliminar-venta')
                                        <button x-on:click="$store.confirm.open('¿Anular esta venta? Se restaurará el stock.', () => $wire.anular({{ $v->id }}))" data-tip="Anular"
                                            class="rounded p-2 hover:bg-red-50 hover:text-red-600"><i class="fa-solid fa-ban"></i></button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-10 text-center text-ink-400">
                                <i class="fa-solid fa-cart-shopping mb-2 block text-2xl"></i>
                                No hay ventas registradas.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-ink-100 p-4">
            {{ $ventas->links() }}
        </div>
    </div>
</div>
