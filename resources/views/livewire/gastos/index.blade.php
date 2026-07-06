<div>
    <div class="mb-6 flex items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl">Gastos varios</h1>
            <p class="text-sm text-ink-500">Egresos del negocio. Total del mes: <span class="font-medium text-ink-700">₲ {{ number_format($totalMes, 0, ',', '.') }}</span></p>
        </div>
        <a href="{{ route('gastos-varios.create') }}" wire:navigate
            class="inline-flex items-center gap-2 rounded-lg bg-ink-900 px-4 py-2 text-sm font-medium text-white hover:bg-ink-800">
            <i class="fa-solid fa-plus"></i> Nuevo gasto
        </a>
    </div>

    <div class="rounded-xl border border-ink-200 bg-white shadow-sm">
        <div class="border-b border-ink-100 p-4">
            <div class="relative max-w-sm">
                <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-sm text-ink-400"></i>
                <input type="search" wire:model.live.debounce.300ms="buscar" placeholder="Buscar por concepto…"
                    class="w-full rounded-lg border border-ink-200 bg-ink-50 py-2 pl-9 pr-3 text-sm focus:border-ink-400 focus:outline-none focus:ring-1 focus:ring-ink-400">
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="text-left text-xs uppercase tracking-wider text-ink-500">
                    <tr class="border-b border-ink-100">
                        <th class="px-4 py-3 font-medium">Fecha</th>
                        <th class="px-4 py-3 font-medium">Concepto</th>
                        <th class="px-4 py-3 text-right font-medium">Monto</th>
                        <th class="px-4 py-3 text-right font-medium">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-ink-50">
                    @forelse ($gastos as $g)
                        <tr class="hover:bg-ink-50/50" wire:key="gasto-{{ $g->id }}">
                            <td class="px-4 py-3 text-ink-600">{{ $g->fecha->format('d/m/Y') }}</td>
                            <td class="px-4 py-3 font-medium text-ink-900">{{ $g->nombre_gasto }}</td>
                            <td class="px-4 py-3 text-right tabular-nums text-red-600">₲ {{ number_format($g->monto, 0, ',', '.') }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-1 text-ink-500">
                                    <a href="{{ route('gastos-varios.edit', $g) }}" wire:navigate data-tip="Editar"
                                        class="rounded p-2 hover:bg-ink-100 hover:text-ink-900"><i class="fa-solid fa-pen"></i></a>
                                    <button x-on:click="$store.confirm.open('¿Eliminar este gasto?', () => $wire.eliminar({{ $g->id }}))" data-tip="Eliminar"
                                        class="rounded p-2 hover:bg-red-50 hover:text-red-600"><i class="fa-solid fa-trash"></i></button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-10 text-center text-ink-400">
                                <i class="fa-solid fa-receipt mb-2 block text-2xl"></i>
                                No hay gastos registrados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-ink-100 p-4">
            {{ $gastos->links() }}
        </div>
    </div>
</div>
