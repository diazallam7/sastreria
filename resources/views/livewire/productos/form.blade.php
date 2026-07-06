<div>
    <div class="mb-6">
        <a href="{{ route('productos.index') }}" wire:navigate class="text-sm text-ink-500 hover:text-ink-800">
            <i class="fa-solid fa-arrow-left"></i> Productos
        </a>
        <h1 class="mt-1 text-2xl">{{ $producto ? 'Editar producto' : 'Nuevo producto' }}</h1>
    </div>

    <form wire:submit="save" class="space-y-6">
        {{-- Datos --}}
        <div class="rounded-xl border border-ink-200 bg-white p-6 shadow-sm" x-data="{ tipo: @entangle('tipo') }">
            <h2 class="mb-4 text-base font-semibold">Datos del producto</h2>

            <div class="grid gap-4 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label class="mb-1 block text-sm font-medium text-ink-700">Nombre <span class="text-red-500">*</span></label>
                    <input type="text" wire:model="nombre" class="w-full rounded-lg border border-ink-200 px-3 py-2 text-sm focus:border-ink-400 focus:outline-none focus:ring-1 focus:ring-ink-400">
                    @error('nombre') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-ink-700">Tipo <span class="text-red-500">*</span></label>
                    <select wire:model="tipo" x-model="tipo" class="w-full rounded-lg border border-ink-200 bg-white px-3 py-2 text-sm focus:border-ink-400 focus:outline-none focus:ring-1 focus:ring-ink-400">
                        <option value="comprado">Comprado (reventa)</option>
                        <option value="fabricado">Fabricado por el taller</option>
                    </select>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-ink-700">Precio de venta <span class="text-red-500">*</span></label>
                    <x-input.money model="precio_venta" />
                    @error('precio_venta') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                {{-- Solo comprado: costo + fecha --}}
                <div x-show="tipo === 'comprado'" x-cloak>
                    <label class="mb-1 block text-sm font-medium text-ink-700">Precio de compra <span class="text-red-500">*</span></label>
                    <x-input.money model="precio_compra" />
                    @error('precio_compra') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div x-show="tipo === 'comprado'" x-cloak>
                    <label class="mb-1 block text-sm font-medium text-ink-700">Fecha de compra <span class="text-red-500">*</span></label>
                    <input type="date" wire:model="fecha_compra" class="w-full rounded-lg border border-ink-200 px-3 py-2 text-sm focus:border-ink-400 focus:outline-none focus:ring-1 focus:ring-ink-400">
                    @error('fecha_compra') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="sm:col-span-2">
                    <label class="mb-1 block text-sm font-medium text-ink-700">Observación</label>
                    <textarea wire:model="observacion" rows="2" class="w-full rounded-lg border border-ink-200 px-3 py-2 text-sm focus:border-ink-400 focus:outline-none focus:ring-1 focus:ring-ink-400"></textarea>
                </div>

                <label class="flex items-center gap-2 text-sm text-ink-700">
                    <input type="checkbox" wire:model="activo_para_venta" class="rounded border-ink-300 text-ink-900 focus:ring-ink-400">
                    Disponible para venta
                </label>
            </div>
        </div>

        {{-- Talles --}}
        <div class="rounded-xl border border-ink-200 bg-white p-6 shadow-sm">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-base font-semibold">Talles y stock</h2>
                <button type="button" wire:click="addTalle" class="inline-flex items-center gap-1.5 rounded-lg border border-ink-200 px-3 py-1.5 text-sm font-medium text-ink-700 hover:bg-ink-50">
                    <i class="fa-solid fa-plus text-xs"></i> Agregar talle
                </button>
            </div>

            @error('talles') <p class="mb-2 text-xs text-red-600">{{ $message }}</p> @enderror

            <div class="space-y-2">
                @foreach ($talles as $i => $talle)
                    <div class="flex items-start gap-3" wire:key="talle-{{ $i }}">
                        <div class="flex-1">
                            <input type="text" wire:model="talles.{{ $i }}.talle" placeholder="Talle (ej. M, 42, Único)"
                                class="w-full rounded-lg border border-ink-200 px-3 py-2 text-sm focus:border-ink-400 focus:outline-none focus:ring-1 focus:ring-ink-400">
                            @error('talles.'.$i.'.talle') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div class="w-32">
                            <input type="number" min="0" wire:model="talles.{{ $i }}.cantidad" placeholder="Cantidad"
                                class="w-full rounded-lg border border-ink-200 px-3 py-2 text-sm focus:border-ink-400 focus:outline-none focus:ring-1 focus:ring-ink-400">
                            @error('talles.'.$i.'.cantidad') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <button type="button" wire:click="removeTalle({{ $i }})" data-tip="Quitar"
                            class="rounded-lg p-2.5 text-ink-400 hover:bg-red-50 hover:text-red-600">
                            <i class="fa-solid fa-trash text-sm"></i>
                        </button>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('productos.index') }}" wire:navigate class="rounded-lg px-4 py-2 text-sm font-medium text-ink-600 hover:bg-ink-100">Cancelar</a>
            <button type="submit" wire:loading.attr="disabled"
                class="inline-flex items-center gap-2 rounded-lg bg-ink-900 px-5 py-2 text-sm font-medium text-white hover:bg-ink-800 disabled:opacity-60">
                <span wire:loading.remove wire:target="save"><i class="fa-solid fa-check"></i></span>
                <span wire:loading wire:target="save"><i class="fa-solid fa-spinner fa-spin"></i></span>
                Guardar
            </button>
        </div>
    </form>
</div>
