<div>
    <div class="mb-6 flex items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl">Productos</h1>
            <p class="text-sm text-ink-500">Comprados para reventa y fabricados por el taller.</p>
        </div>
        @can('crear-producto')
            <a href="{{ route('productos.create') }}" wire:navigate
                class="inline-flex items-center gap-2 rounded-lg bg-ink-900 px-4 py-2 text-sm font-medium text-white hover:bg-ink-800">
                <i class="fa-solid fa-plus"></i> Nuevo producto
            </a>
        @endcan
    </div>

    <div class="rounded-xl border border-ink-200 bg-white shadow-sm">
        <div class="flex flex-wrap items-center gap-3 border-b border-ink-100 p-4">
            <div class="relative min-w-64 flex-1">
                <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-sm text-ink-400"></i>
                <input type="search" wire:model.live.debounce.300ms="buscar" placeholder="Buscar por nombre…"
                    class="w-full rounded-lg border border-ink-200 bg-ink-50 py-2 pl-9 pr-3 text-sm focus:border-ink-400 focus:outline-none focus:ring-1 focus:ring-ink-400">
            </div>
            <select wire:model.live="tipo"
                class="rounded-lg border border-ink-200 bg-white py-2 px-3 text-sm focus:border-ink-400 focus:outline-none focus:ring-1 focus:ring-ink-400">
                <option value="">Todos los tipos</option>
                <option value="comprado">Comprados</option>
                <option value="fabricado">Fabricados</option>
            </select>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="text-left text-xs uppercase tracking-wider text-ink-500">
                    <tr class="border-b border-ink-100">
                        <th class="px-4 py-3 font-medium">Nombre</th>
                        <th class="px-4 py-3 font-medium">Tipo</th>
                        <th class="px-4 py-3 font-medium text-right">Precio venta</th>
                        <th class="px-4 py-3 font-medium text-right">Stock</th>
                        <th class="px-4 py-3 font-medium">Venta</th>
                        <th class="px-4 py-3 text-right font-medium">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-ink-50">
                    @forelse ($productos as $p)
                        <tr class="hover:bg-ink-50/50" wire:key="prod-{{ $p->id }}">
                            <td class="px-4 py-3 font-medium text-ink-900">{{ $p->nombre }}</td>
                            <td class="px-4 py-3">
                                <span class="rounded-full px-2.5 py-0.5 text-xs font-medium {{ $p->tipo->esComprado() ? 'bg-blue-100 text-blue-700' : 'bg-accent-100 text-accent-800' }}">
                                    {{ $p->tipo->label() }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right tabular-nums">₲ {{ number_format($p->precio_venta, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right tabular-nums {{ $p->stock > 0 ? 'text-ink-700' : 'text-red-500' }}">{{ (int) $p->stock }}</td>
                            <td class="px-4 py-3">
                                @can('editar-producto')
                                    <button wire:click="toggleActivo({{ $p->id }})"
                                        class="rounded-full px-2.5 py-0.5 text-xs font-medium {{ $p->activo_para_venta ? 'bg-green-100 text-green-700' : 'bg-ink-100 text-ink-500' }}">
                                        {{ $p->activo_para_venta ? 'Activo' : 'Inactivo' }}
                                    </button>
                                @else
                                    <span class="rounded-full px-2.5 py-0.5 text-xs font-medium {{ $p->activo_para_venta ? 'bg-green-100 text-green-700' : 'bg-ink-100 text-ink-500' }}">
                                        {{ $p->activo_para_venta ? 'Activo' : 'Inactivo' }}
                                    </span>
                                @endcan
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-1 text-ink-500">
                                    <a href="{{ route('productos.show', $p) }}" wire:navigate data-tip="Ver"
                                        class="rounded p-2 hover:bg-ink-100 hover:text-ink-900"><i class="fa-solid fa-eye"></i></a>
                                    @can('editar-producto')
                                        <a href="{{ route('productos.edit', $p) }}" wire:navigate data-tip="Editar"
                                            class="rounded p-2 hover:bg-ink-100 hover:text-ink-900"><i class="fa-solid fa-pen"></i></a>
                                    @endcan
                                    @can('eliminar-producto')
                                        <button x-on:click="$store.confirm.open('¿Eliminar este producto?', () => $wire.eliminar({{ $p->id }}))" data-tip="Eliminar"
                                            class="rounded p-2 hover:bg-red-50 hover:text-red-600"><i class="fa-solid fa-trash"></i></button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-10 text-center text-ink-400">
                                <i class="fa-solid fa-box mb-2 block text-2xl"></i>
                                No se encontraron productos.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-ink-100 p-4">
            {{ $productos->links() }}
        </div>
    </div>
</div>
