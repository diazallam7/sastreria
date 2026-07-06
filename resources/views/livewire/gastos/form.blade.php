<div>
    <div class="mb-6">
        <a href="{{ route('gastos-varios.index') }}" wire:navigate class="text-sm text-ink-500 hover:text-ink-800">
            <i class="fa-solid fa-arrow-left"></i> Gastos varios
        </a>
        <h1 class="mt-1 text-2xl">{{ $gasto ? 'Editar gasto' : 'Nuevo gasto' }}</h1>
    </div>

    <form wire:submit="save" class="max-w-2xl space-y-6">
        <div class="rounded-xl border border-ink-200 bg-white p-6 shadow-sm">
            <div class="grid gap-4 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label class="mb-1 block text-sm font-medium text-ink-700">Concepto <span class="text-red-500">*</span></label>
                    <input type="text" wire:model="nombre_gasto" class="w-full rounded-lg border border-ink-200 px-3 py-2 text-sm focus:border-ink-400 focus:outline-none focus:ring-1 focus:ring-ink-400">
                    @error('nombre_gasto') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-ink-700">Fecha <span class="text-red-500">*</span></label>
                    <input type="date" wire:model="fecha" class="w-full rounded-lg border border-ink-200 px-3 py-2 text-sm focus:border-ink-400 focus:outline-none focus:ring-1 focus:ring-ink-400">
                    @error('fecha') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-ink-700">Monto <span class="text-red-500">*</span></label>
                    <x-input.money model="monto" />
                    @error('monto') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div class="sm:col-span-2">
                    <label class="mb-1 block text-sm font-medium text-ink-700">Observación</label>
                    <textarea wire:model="observacion" rows="2" class="w-full rounded-lg border border-ink-200 px-3 py-2 text-sm focus:border-ink-400 focus:outline-none focus:ring-1 focus:ring-ink-400"></textarea>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('gastos-varios.index') }}" wire:navigate class="rounded-lg px-4 py-2 text-sm font-medium text-ink-600 hover:bg-ink-100">Cancelar</a>
            <button type="submit" wire:loading.attr="disabled"
                class="inline-flex items-center gap-2 rounded-lg bg-ink-900 px-5 py-2 text-sm font-medium text-white hover:bg-ink-800 disabled:opacity-60">
                <i class="fa-solid fa-check"></i> Guardar
            </button>
        </div>
    </form>
</div>
