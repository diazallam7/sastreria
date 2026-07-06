<div>
    <div class="mb-6">
        <a href="{{ route('roles.index') }}" wire:navigate class="text-sm text-ink-500 hover:text-ink-800">
            <i class="fa-solid fa-arrow-left"></i> Roles
        </a>
        <h1 class="mt-1 text-2xl">{{ $rol ? 'Editar rol' : 'Nuevo rol' }}</h1>
    </div>

    <form wire:submit="save" class="space-y-6">
        <div class="rounded-xl border border-ink-200 bg-white p-6 shadow-sm">
            <label class="mb-1 block text-sm font-medium text-ink-700">Nombre del rol <span class="text-red-500">*</span></label>
            <input type="text" wire:model="name" class="w-full max-w-sm rounded-lg border border-ink-200 px-3 py-2 text-sm focus:border-ink-400 focus:outline-none focus:ring-1 focus:ring-ink-400">
            @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        <div class="rounded-xl border border-ink-200 bg-white p-6 shadow-sm">
            <h2 class="mb-1 text-base font-semibold">Permisos</h2>
            @error('permisos') <p class="mb-2 text-xs text-red-600">{{ $message }}</p> @enderror

            <div class="mt-4 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($grupos as $modulo => $permisosGrupo)
                    <div>
                        <h3 class="mb-2 text-xs font-semibold uppercase tracking-wider text-accent-700">{{ str_replace('-', ' ', $modulo) }}</h3>
                        <div class="space-y-1.5">
                            @foreach ($permisosGrupo as $permiso)
                                <label class="flex items-center gap-2 text-sm text-ink-700">
                                    <input type="checkbox" wire:model="permisos" value="{{ $permiso->name }}" class="rounded border-ink-300 text-ink-900 focus:ring-ink-400">
                                    {{ \Illuminate\Support\Str::before($permiso->name, '-') }}
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('roles.index') }}" wire:navigate class="rounded-lg px-4 py-2 text-sm font-medium text-ink-600 hover:bg-ink-100">Cancelar</a>
            <button type="submit" wire:loading.attr="disabled"
                class="inline-flex items-center gap-2 rounded-lg bg-ink-900 px-5 py-2 text-sm font-medium text-white hover:bg-ink-800 disabled:opacity-60">
                <i class="fa-solid fa-check"></i> Guardar
            </button>
        </div>
    </form>
</div>
