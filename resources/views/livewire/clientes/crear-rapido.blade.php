<div>
    @if ($abierto)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-black/40" wire:click="cerrar"></div>

            <div class="relative w-full max-w-md rounded-xl bg-white p-6 shadow-xl">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-base font-semibold">Nuevo cliente</h2>
                    <button type="button" wire:click="cerrar" class="rounded p-1 text-ink-400 hover:bg-ink-100 hover:text-ink-700"><i class="fa-solid fa-xmark"></i></button>
                </div>

                <form wire:submit="guardar" class="space-y-3">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-ink-700">Nombre <span class="text-red-500">*</span></label>
                        <input type="text" wire:model="nombre" autofocus class="w-full rounded-lg border border-ink-200 px-3 py-2 text-sm focus:border-ink-400 focus:outline-none focus:ring-1 focus:ring-ink-400">
                        @error('nombre') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-ink-700">Documento</label>
                            <input type="text" wire:model="documento" class="w-full rounded-lg border border-ink-200 px-3 py-2 text-sm focus:border-ink-400 focus:outline-none focus:ring-1 focus:ring-ink-400">
                            @error('documento') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-ink-700">Teléfono</label>
                            <input type="text" wire:model="telefono" class="w-full rounded-lg border border-ink-200 px-3 py-2 text-sm focus:border-ink-400 focus:outline-none focus:ring-1 focus:ring-ink-400">
                        </div>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-ink-700">Correo</label>
                        <input type="email" wire:model="correo" class="w-full rounded-lg border border-ink-200 px-3 py-2 text-sm focus:border-ink-400 focus:outline-none focus:ring-1 focus:ring-ink-400">
                        @error('correo') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-2">
                        <button type="button" wire:click="cerrar" class="rounded-lg px-4 py-2 text-sm font-medium text-ink-600 hover:bg-ink-100">Cancelar</button>
                        <button type="submit" wire:loading.attr="disabled"
                            class="inline-flex items-center gap-2 rounded-lg bg-ink-900 px-4 py-2 text-sm font-medium text-white hover:bg-ink-800 disabled:opacity-60">
                            <i class="fa-solid fa-check"></i> Crear
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
