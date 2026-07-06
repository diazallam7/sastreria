<div>
    <div class="mb-6">
        <a href="{{ route('ventas.index') }}" wire:navigate class="text-sm text-ink-500 hover:text-ink-800">
            <i class="fa-solid fa-arrow-left"></i> Ventas
        </a>
        <h1 class="mt-1 text-2xl">{{ $venta ? 'Editar venta #' . $venta->id : 'Nueva venta' }}</h1>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        {{-- Izquierda: selector + carrito --}}
        <div class="space-y-6 lg:col-span-2">
            <div class="rounded-xl border border-ink-200 bg-white p-6 shadow-sm">
                <h2 class="mb-4 text-base font-semibold">Agregar producto</h2>
                <div class="grid gap-3 sm:grid-cols-[1fr_1fr_auto_auto] sm:items-end">
                    <div>
                        <label class="mb-1 block text-xs text-ink-500">Producto</label>
                        <select wire:model.live="productoSel" class="w-full rounded-lg border border-ink-200 bg-white px-3 py-2 text-sm focus:border-ink-400 focus:outline-none focus:ring-1 focus:ring-ink-400">
                            <option value="">Seleccionar…</option>
                            @foreach ($productos as $p)
                                <option value="{{ $p->id }}">{{ $p->nombre }} — ₲ {{ number_format($p->precio_venta, 0, ',', '.') }}</option>
                            @endforeach
                        </select>
                        @error('productoSel') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-xs text-ink-500">Talle</label>
                        <select wire:model="talleSel" @disabled($tallesDisponibles->isEmpty()) class="w-full rounded-lg border border-ink-200 bg-white px-3 py-2 text-sm focus:border-ink-400 focus:outline-none focus:ring-1 focus:ring-ink-400 disabled:bg-ink-50">
                            <option value="">{{ $productoSel ? 'Seleccionar…' : 'Elegí un producto' }}</option>
                            @foreach ($tallesDisponibles as $t)
                                <option value="{{ $t->id }}">{{ $t->talle }} ({{ $t->cantidad_disponible }} disp.)</option>
                            @endforeach
                        </select>
                        @error('talleSel') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="w-24">
                        <label class="mb-1 block text-xs text-ink-500">Cantidad</label>
                        <input type="number" min="1" wire:model="cantidadSel" class="w-full rounded-lg border border-ink-200 px-3 py-2 text-sm focus:border-ink-400 focus:outline-none focus:ring-1 focus:ring-ink-400">
                        @error('cantidadSel') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <button type="button" wire:click="agregarItem"
                        class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-ink-900 px-4 py-2 text-sm font-medium text-white hover:bg-ink-800">
                        <i class="fa-solid fa-plus text-xs"></i> Agregar
                    </button>
                </div>
            </div>

            <div class="rounded-xl border border-ink-200 bg-white shadow-sm">
                <div class="border-b border-ink-100 px-6 py-4">
                    <h2 class="text-base font-semibold">Productos de la venta</h2>
                    @error('items') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="text-left text-xs uppercase tracking-wider text-ink-500">
                            <tr class="border-b border-ink-100">
                                <th class="px-6 py-3 font-medium">Producto</th>
                                <th class="px-4 py-3 font-medium">Talle</th>
                                <th class="px-4 py-3 text-right font-medium">Precio</th>
                                <th class="px-4 py-3 text-right font-medium">Cant.</th>
                                <th class="px-4 py-3 text-right font-medium">Subtotal</th>
                                <th class="px-6 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-ink-50">
                            @forelse ($items as $i => $item)
                                <tr wire:key="item-{{ $item['producto_talle_id'] }}">
                                    <td class="px-6 py-3 font-medium text-ink-900">{{ $item['nombre'] }}</td>
                                    <td class="px-4 py-3 text-ink-600">{{ $item['talle'] }}</td>
                                    <td class="px-4 py-3 text-right tabular-nums">₲ {{ number_format($item['precio'], 0, ',', '.') }}</td>
                                    <td class="px-4 py-3 text-right tabular-nums">{{ $item['cantidad'] }}</td>
                                    <td class="px-4 py-3 text-right font-medium tabular-nums">₲ {{ number_format($item['precio'] * $item['cantidad'], 0, ',', '.') }}</td>
                                    <td class="px-6 py-3 text-right">
                                        <button type="button" wire:click="removeItem({{ $i }})" class="rounded p-1.5 text-ink-400 hover:bg-red-50 hover:text-red-600"><i class="fa-solid fa-xmark"></i></button>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="px-6 py-8 text-center text-ink-400">Todavía no agregaste productos.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Derecha: resumen --}}
        <div class="lg:col-span-1">
            <div class="sticky top-24 space-y-4 rounded-xl border border-ink-200 bg-white p-6 shadow-sm">
                <h2 class="text-base font-semibold">Resumen</h2>

                <div>
                    <label class="mb-1 block text-sm font-medium text-ink-700">Cliente <span class="text-red-500">*</span></label>
                    <div class="flex gap-2">
                        <select wire:model="cliente_id" class="min-w-0 flex-1 rounded-lg border border-ink-200 bg-white px-3 py-2 text-sm focus:border-ink-400 focus:outline-none focus:ring-1 focus:ring-ink-400">
                            <option value="">Seleccionar…</option>
                            @foreach ($clientes as $c)
                                <option value="{{ $c->id }}">{{ $c->nombre }}</option>
                            @endforeach
                        </select>
                        @can('crear-cliente')
                            <button type="button" wire:click="$dispatch('abrir-crear-cliente')" data-tip="Nuevo cliente"
                                class="inline-flex shrink-0 items-center rounded-lg border border-ink-200 px-3 py-2 text-ink-700 hover:bg-ink-50">
                                <i class="fa-solid fa-user-plus"></i>
                            </button>
                        @endcan
                    </div>
                    @error('cliente_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-ink-700">Fecha</label>
                    <input type="date" wire:model="fecha_venta" class="w-full rounded-lg border border-ink-200 px-3 py-2 text-sm focus:border-ink-400 focus:outline-none focus:ring-1 focus:ring-ink-400">
                    @error('fecha_venta') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="flex items-center justify-between border-t border-ink-100 pt-4">
                    <span class="text-sm text-ink-500">Total</span>
                    <span class="font-serif text-2xl font-semibold text-ink-900">₲ {{ number_format($this->total, 0, ',', '.') }}</span>
                </div>

                <button type="button" wire:click="save" wire:loading.attr="disabled"
                    class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-ink-900 px-5 py-2.5 text-sm font-medium text-white hover:bg-ink-800 disabled:opacity-60">
                    <span wire:loading.remove wire:target="save"><i class="fa-solid fa-check"></i></span>
                    <span wire:loading wire:target="save"><i class="fa-solid fa-spinner fa-spin"></i></span>
                    {{ $venta ? 'Guardar cambios' : 'Registrar venta' }}
                </button>
            </div>
        </div>
    </div>

    <livewire:clientes.crear-rapido />
</div>
