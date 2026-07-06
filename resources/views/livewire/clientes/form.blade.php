<div>
    <div class="mb-6">
        <a href="{{ route('clientes.index') }}" wire:navigate class="text-sm text-ink-500 hover:text-ink-800">
            <i class="fa-solid fa-arrow-left"></i> Clientes
        </a>
        <h1 class="mt-1 text-2xl">{{ $cliente ? 'Editar cliente' : 'Nuevo cliente' }}</h1>
    </div>

    <form wire:submit="save" class="space-y-6">
        {{-- Datos generales --}}
        <div class="rounded-xl border border-ink-200 bg-white p-6 shadow-sm">
            <h2 class="mb-4 text-base font-semibold">Datos generales</h2>
            <div class="grid gap-4 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label class="mb-1 block text-sm font-medium text-ink-700">Nombre <span class="text-red-500">*</span></label>
                    <input type="text" wire:model="nombre" @class(['w-full rounded-lg border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-1', 'border-red-400 focus:ring-red-400' => $errors->has('nombre'), 'border-ink-200 focus:border-ink-400 focus:ring-ink-400' => !$errors->has('nombre')])>
                    @error('nombre') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-ink-700">Documento</label>
                    <input type="text" wire:model="documento" class="w-full rounded-lg border border-ink-200 px-3 py-2 text-sm focus:border-ink-400 focus:outline-none focus:ring-1 focus:ring-ink-400">
                    @error('documento') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-ink-700">Teléfono</label>
                    <input type="text" wire:model="telefono" class="w-full rounded-lg border border-ink-200 px-3 py-2 text-sm focus:border-ink-400 focus:outline-none focus:ring-1 focus:ring-ink-400">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-ink-700">Correo</label>
                    <input type="email" wire:model="correo" class="w-full rounded-lg border border-ink-200 px-3 py-2 text-sm focus:border-ink-400 focus:outline-none focus:ring-1 focus:ring-ink-400">
                    @error('correo') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-ink-700">Dirección</label>
                    <input type="text" wire:model="direccion" class="w-full rounded-lg border border-ink-200 px-3 py-2 text-sm focus:border-ink-400 focus:outline-none focus:ring-1 focus:ring-ink-400">
                </div>
            </div>
        </div>

        {{-- Medidas (desplegable, opcional) --}}
        @php
            $tieneMedidas = collect($medidas)->flatten()->contains(fn ($v) => $v !== null && $v !== '');
        @endphp
        <div x-data="{ open: @js($tieneMedidas) }" class="rounded-xl border border-ink-200 bg-white shadow-sm">
            <button type="button" @click="open = !open"
                class="flex w-full items-center justify-between gap-4 p-6 text-left hover:bg-ink-50/50 rounded-xl">
                <div>
                    <h2 class="text-base font-semibold">Medidas</h2>
                    <span class="text-xs text-ink-400">Opcional · se guarda el historial de cambios</span>
                </div>
                <i class="fa-solid fa-chevron-down text-ink-400 transition-transform duration-200" :class="open && 'rotate-180'"></i>
            </button>

            <div x-show="open" x-collapse x-cloak class="space-y-6 px-6 pb-6">
                @foreach ($tipos as $tipo)
                    <div>
                        <h3 class="mb-2 text-sm font-serif font-semibold text-accent-700 capitalize">{{ $tipo->value }}</h3>
                        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
                            @foreach ($tipo->campos() as $campo)
                                <div>
                                    <label class="mb-1 block text-xs text-ink-500">{{ ucfirst(str_replace('_', ' ', $campo)) }}</label>
                                    <input type="number" step="0.01" min="0" wire:model="medidas.{{ $tipo->value }}.{{ $campo }}"
                                        class="w-full rounded-lg border border-ink-200 px-2.5 py-1.5 text-sm focus:border-ink-400 focus:outline-none focus:ring-1 focus:ring-ink-400">
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach

                <div>
                    <label class="mb-1 block text-sm font-medium text-ink-700">Observaciones de medidas</label>
                    <textarea wire:model="observaciones_medidas" rows="2" class="w-full rounded-lg border border-ink-200 px-3 py-2 text-sm focus:border-ink-400 focus:outline-none focus:ring-1 focus:ring-ink-400"></textarea>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('clientes.index') }}" wire:navigate class="rounded-lg px-4 py-2 text-sm font-medium text-ink-600 hover:bg-ink-100">Cancelar</a>
            <button type="submit" wire:loading.attr="disabled"
                class="inline-flex items-center gap-2 rounded-lg bg-ink-900 px-5 py-2 text-sm font-medium text-white hover:bg-ink-800 disabled:opacity-60">
                <span wire:loading.remove wire:target="save"><i class="fa-solid fa-check"></i></span>
                <span wire:loading wire:target="save"><i class="fa-solid fa-spinner fa-spin"></i></span>
                Guardar
            </button>
        </div>
    </form>
</div>
