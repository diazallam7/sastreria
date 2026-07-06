<div>
    <div class="mb-6">
        <h1 class="text-2xl">Configuración</h1>
        <p class="text-sm text-ink-500">Parámetros del negocio.</p>
    </div>

    <form wire:submit="save" class="max-w-2xl space-y-4">
        <div class="rounded-xl border border-ink-200 bg-white p-6 shadow-sm">
            @forelse ($configuraciones as $config)
                <div class="mb-4 last:mb-0">
                    <label class="mb-1 block text-sm font-medium text-ink-700">{{ ucfirst($config->nombre) }}</label>
                    @if ($config->descripcion)
                        <p class="mb-1 text-xs text-ink-400">{{ $config->descripcion }}</p>
                    @endif
                    <x-input.money model="valores.{{ $config->id }}" />
                    @error('valores.' . $config->id) <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
            @empty
                <p class="text-sm text-ink-400">No hay parámetros configurables.</p>
            @endforelse
        </div>

        @if ($configuraciones->isNotEmpty())
            <div class="flex justify-end">
                <button type="submit" wire:loading.attr="disabled"
                    class="inline-flex items-center gap-2 rounded-lg bg-ink-900 px-5 py-2 text-sm font-medium text-white hover:bg-ink-800 disabled:opacity-60">
                    <i class="fa-solid fa-check"></i> Guardar
                </button>
            </div>
        @endif
    </form>
</div>
