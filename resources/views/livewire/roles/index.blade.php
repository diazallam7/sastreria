<div>
    <div class="mb-6 flex items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl">Roles</h1>
            <p class="text-sm text-ink-500">Roles y sus permisos.</p>
        </div>
        @can('crear-role')
            <a href="{{ route('roles.create') }}" wire:navigate
                class="inline-flex items-center gap-2 rounded-lg bg-ink-900 px-4 py-2 text-sm font-medium text-white hover:bg-ink-800">
                <i class="fa-solid fa-plus"></i> Nuevo rol
            </a>
        @endcan
    </div>

    <div class="rounded-xl border border-ink-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="text-left text-xs uppercase tracking-wider text-ink-500">
                    <tr class="border-b border-ink-100">
                        <th class="px-4 py-3 font-medium">Rol</th>
                        <th class="px-4 py-3 text-right font-medium">Permisos</th>
                        <th class="px-4 py-3 text-right font-medium">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-ink-50">
                    @forelse ($roles as $r)
                        <tr class="hover:bg-ink-50/50" wire:key="role-{{ $r->id }}">
                            <td class="px-4 py-3 font-medium capitalize text-ink-900">{{ $r->name }}</td>
                            <td class="px-4 py-3 text-right tabular-nums text-ink-500">{{ $r->permissions_count }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-1 text-ink-500">
                                    @can('editar-role')
                                        <a href="{{ route('roles.edit', $r) }}" wire:navigate data-tip="Editar"
                                            class="rounded p-2 hover:bg-ink-100 hover:text-ink-900"><i class="fa-solid fa-pen"></i></a>
                                    @endcan
                                    @can('eliminar-role')
                                        <button x-on:click="$store.confirm.open('¿Eliminar este rol?', () => $wire.eliminar({{ $r->id }}))" data-tip="Eliminar"
                                            class="rounded p-2 hover:bg-red-50 hover:text-red-600"><i class="fa-solid fa-trash"></i></button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="px-4 py-10 text-center text-ink-400">Sin roles.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
