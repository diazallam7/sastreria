<div>
    <div class="mb-6 flex items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl">Clientes</h1>
            <p class="text-sm text-ink-500">Gestión de clientes y sus medidas.</p>
        </div>
        @can('crear-cliente')
            <a href="{{ route('clientes.create') }}" wire:navigate
                class="inline-flex items-center gap-2 rounded-lg bg-ink-900 px-4 py-2 text-sm font-medium text-white hover:bg-ink-800">
                <i class="fa-solid fa-plus"></i> Nuevo cliente
            </a>
        @endcan
    </div>

    <div class="rounded-xl border border-ink-200 bg-white shadow-sm">
        <div class="border-b border-ink-100 p-4">
            <div class="relative max-w-sm">
                <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-sm text-ink-400"></i>
                <input type="search" wire:model.live.debounce.300ms="buscar" placeholder="Buscar por nombre, documento, teléfono…"
                    class="w-full rounded-lg border border-ink-200 bg-ink-50 py-2 pl-9 pr-3 text-sm focus:border-ink-400 focus:outline-none focus:ring-1 focus:ring-ink-400">
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="text-left text-xs uppercase tracking-wider text-ink-500">
                    <tr class="border-b border-ink-100">
                        <th class="px-4 py-3 font-medium">Nombre</th>
                        <th class="px-4 py-3 font-medium">Documento</th>
                        <th class="px-4 py-3 font-medium">Teléfono</th>
                        <th class="px-4 py-3 font-medium">Estado</th>
                        <th class="px-4 py-3 text-right font-medium">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-ink-50">
                    @forelse ($clientes as $c)
                        <tr class="hover:bg-ink-50/50" wire:key="cliente-{{ $c->id }}">
                            <td class="px-4 py-3 font-medium text-ink-900">{{ $c->nombre }}</td>
                            <td class="px-4 py-3 text-ink-600">{{ $c->documento ?: '—' }}</td>
                            <td class="px-4 py-3 text-ink-600">{{ $c->telefono ?: '—' }}</td>
                            <td class="px-4 py-3">
                                @can('editar-cliente')
                                    <button wire:click="toggleEstado({{ $c->id }})"
                                        class="rounded-full px-2.5 py-0.5 text-xs font-medium {{ $c->estado ? 'bg-green-100 text-green-700' : 'bg-ink-100 text-ink-500' }}">
                                        {{ $c->estado ? 'Activo' : 'Inactivo' }}
                                    </button>
                                @else
                                    <span class="rounded-full px-2.5 py-0.5 text-xs font-medium {{ $c->estado ? 'bg-green-100 text-green-700' : 'bg-ink-100 text-ink-500' }}">
                                        {{ $c->estado ? 'Activo' : 'Inactivo' }}
                                    </span>
                                @endcan
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-1 text-ink-500">
                                    <a href="{{ route('clientes.show', $c) }}" wire:navigate data-tip="Ver"
                                        class="rounded p-2 hover:bg-ink-100 hover:text-ink-900"><i class="fa-solid fa-eye"></i></a>
                                    @can('editar-cliente')
                                        <a href="{{ route('clientes.edit', $c) }}" wire:navigate data-tip="Editar"
                                            class="rounded p-2 hover:bg-ink-100 hover:text-ink-900"><i class="fa-solid fa-pen"></i></a>
                                    @endcan
                                    @can('eliminar-cliente')
                                        <button x-on:click="$store.confirm.open('¿Eliminar este cliente?', () => $wire.eliminar({{ $c->id }}))" data-tip="Eliminar"
                                            class="rounded p-2 hover:bg-red-50 hover:text-red-600"><i class="fa-solid fa-trash"></i></button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-10 text-center text-ink-400">
                                <i class="fa-solid fa-users mb-2 block text-2xl"></i>
                                No se encontraron clientes.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-ink-100 p-4">
            {{ $clientes->links() }}
        </div>
    </div>
</div>
