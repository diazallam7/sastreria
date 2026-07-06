<div>
    <div class="mb-6">
        <a href="{{ route('reservas.index') }}" wire:navigate class="text-sm text-ink-500 hover:text-ink-800">
            <i class="fa-solid fa-arrow-left"></i> Reservas
        </a>
        <h1 class="mt-1 text-2xl">{{ $reserva ? 'Editar reserva #' . $reserva->id : 'Nueva reserva' }}</h1>
    </div>

    <form wire:submit="save" class="space-y-6">
        {{-- Datos --}}
        <div class="rounded-xl border border-ink-200 bg-white p-6 shadow-sm">
            <h2 class="mb-4 text-base font-semibold">Datos de la reserva</h2>
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="sm:col-span-2">
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
                    <label class="mb-1 block text-sm font-medium text-ink-700">Fecha reserva <span class="text-red-500">*</span></label>
                    <input type="date" wire:model="fecha_reserva" class="w-full rounded-lg border border-ink-200 px-3 py-2 text-sm focus:border-ink-400 focus:outline-none focus:ring-1 focus:ring-ink-400">
                    @error('fecha_reserva') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-ink-700">Entrega programada <span class="text-red-500">*</span></label>
                    <input type="date" wire:model="fecha_entrega_programada" class="w-full rounded-lg border border-ink-200 px-3 py-2 text-sm focus:border-ink-400 focus:outline-none focus:ring-1 focus:ring-ink-400">
                    @error('fecha_entrega_programada') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-ink-700">Devolución programada <span class="text-red-500">*</span></label>
                    <input type="date" wire:model="fecha_devolucion_programada" class="w-full rounded-lg border border-ink-200 px-3 py-2 text-sm focus:border-ink-400 focus:outline-none focus:ring-1 focus:ring-ink-400">
                    @error('fecha_devolucion_programada') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        {{-- Prendas --}}
        <div class="rounded-xl border border-ink-200 bg-white shadow-sm">
            <div class="border-b border-ink-100 p-6">
                <h2 class="mb-4 text-base font-semibold">Prendas</h2>
                <div class="grid gap-3 sm:grid-cols-[1fr_1fr_auto_auto] sm:items-end">
                    <div>
                        <label class="mb-1 block text-xs text-ink-500">Prenda</label>
                        <select wire:model.live="stockSel" class="w-full rounded-lg border border-ink-200 bg-white px-3 py-2 text-sm focus:border-ink-400 focus:outline-none focus:ring-1 focus:ring-ink-400">
                            <option value="">Seleccionar…</option>
                            @foreach ($stockItems as $s)
                                <option value="{{ $s->id }}">{{ $s->codigo }} — {{ $s->nombre }}</option>
                            @endforeach
                        </select>
                        @error('stockSel') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-xs text-ink-500">Talle</label>
                        <select wire:model="talleSel" @disabled($tallesDisponibles->isEmpty()) class="w-full rounded-lg border border-ink-200 bg-white px-3 py-2 text-sm focus:border-ink-400 focus:outline-none focus:ring-1 focus:ring-ink-400 disabled:bg-ink-50">
                            <option value="">{{ $stockSel ? 'Seleccionar…' : 'Elegí una prenda' }}</option>
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
                    <button type="button" wire:click="agregarPrenda"
                        class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-ink-900 px-4 py-2 text-sm font-medium text-white hover:bg-ink-800">
                        <i class="fa-solid fa-plus text-xs"></i> Agregar
                    </button>
                </div>
                @error('prendas') <p class="mt-2 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="text-left text-xs uppercase tracking-wider text-ink-500">
                        <tr class="border-b border-ink-100">
                            <th class="px-6 py-3 font-medium">Prenda</th>
                            <th class="px-4 py-3 font-medium">Talle</th>
                            <th class="px-4 py-3 text-right font-medium">Precio</th>
                            <th class="px-4 py-3 text-right font-medium">Cant.</th>
                            <th class="px-4 py-3 text-right font-medium">Subtotal</th>
                            <th class="px-6 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-ink-50">
                        @forelse ($prendas as $i => $p)
                            <tr wire:key="prenda-{{ $p['talle_id'] }}">
                                <td class="px-6 py-3 font-medium text-ink-900">{{ $p['nombre'] }}</td>
                                <td class="px-4 py-3 text-ink-600">{{ $p['talle'] }}</td>
                                <td class="px-4 py-3 text-right tabular-nums">₲ {{ number_format($p['precio'], 0, ',', '.') }}</td>
                                <td class="px-4 py-3 text-right tabular-nums">{{ $p['cantidad'] }}</td>
                                <td class="px-4 py-3 text-right font-medium tabular-nums">₲ {{ number_format($p['precio'] * $p['cantidad'], 0, ',', '.') }}</td>
                                <td class="px-6 py-3 text-right">
                                    <button type="button" wire:click="removePrenda({{ $i }})" class="rounded p-1.5 text-ink-400 hover:bg-red-50 hover:text-red-600"><i class="fa-solid fa-xmark"></i></button>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-6 py-8 text-center text-ink-400">Todavía no agregaste prendas.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Montos y señas --}}
            <div class="grid gap-4 border-t border-ink-100 p-6 sm:grid-cols-2 lg:grid-cols-4">
                <div>
                    <label class="mb-1 flex items-center justify-between text-sm font-medium text-ink-700">
                        Monto total <span class="text-xs font-normal text-ink-400">auto</span>
                    </label>
                    <x-input.money model="monto_total" />
                    @error('monto_total') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-ink-700">Garantía total <span class="text-red-500">*</span></label>
                    <x-input.money model="garantia_total" />
                    @error('garantia_total') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-ink-700">Seña garantía <span class="text-red-500">*</span></label>
                    <x-input.money model="senia_garantia" />
                    @error('senia_garantia') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-ink-700">Seña alquiler</label>
                    <x-input.money model="senia_alquiler" />
                    @error('senia_alquiler') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div class="sm:col-span-2 lg:col-span-4">
                    <label class="mb-1 block text-sm font-medium text-ink-700">Observaciones</label>
                    <textarea wire:model="observaciones" rows="2" class="w-full rounded-lg border border-ink-200 px-3 py-2 text-sm focus:border-ink-400 focus:outline-none focus:ring-1 focus:ring-ink-400"></textarea>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('reservas.index') }}" wire:navigate class="rounded-lg px-4 py-2 text-sm font-medium text-ink-600 hover:bg-ink-100">Cancelar</a>
            <button type="submit" wire:loading.attr="disabled"
                class="inline-flex items-center gap-2 rounded-lg bg-ink-900 px-5 py-2 text-sm font-medium text-white hover:bg-ink-800 disabled:opacity-60">
                <span wire:loading.remove wire:target="save"><i class="fa-solid fa-check"></i></span>
                <span wire:loading wire:target="save"><i class="fa-solid fa-spinner fa-spin"></i></span>
                Guardar
            </button>
        </div>
    </form>

    <livewire:clientes.crear-rapido />
</div>
