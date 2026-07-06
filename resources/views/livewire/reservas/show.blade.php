<div>
    @php
        $estadoColor = ['pendiente' => 'bg-amber-100 text-amber-700', 'confirmada' => 'bg-blue-100 text-blue-700', 'entregada' => 'bg-green-100 text-green-700', 'cancelada' => 'bg-ink-100 text-ink-500'];
    @endphp

    <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
        <div>
            <a href="{{ route('reservas.index') }}" wire:navigate class="text-sm text-ink-500 hover:text-ink-800">
                <i class="fa-solid fa-arrow-left"></i> Reservas
            </a>
            <h1 class="mt-1 text-2xl">Reserva #{{ $reserva->id }}</h1>
            <div class="mt-1 flex items-center gap-2">
                <span class="text-sm text-ink-500">{{ $reserva->cliente?->nombre ?? '—' }}</span>
                <span class="rounded-full px-2.5 py-0.5 text-xs font-medium {{ $estadoColor[$reserva->estado->value] }}">{{ $reserva->estado->label() }}</span>
                @if ($reserva->alquiler_id)
                    <a href="{{ route('alquileres.show', $reserva->alquiler_id) }}" wire:navigate class="text-xs text-accent-700 hover:underline">→ Alquiler #{{ $reserva->alquiler_id }}</a>
                @endif
            </div>
        </div>
        @can('editar-reserva')
            <div class="flex gap-2">
                @if ($puedeConvertir)
                    <button wire:click="abrirConvertir" class="inline-flex items-center gap-2 rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700">
                        <i class="fa-solid fa-right-left"></i> Convertir a alquiler
                    </button>
                @endif
                @if ($puedeCancelar)
                    <button wire:click="abrirCancelar" class="inline-flex items-center gap-2 rounded-lg border border-red-200 bg-white px-4 py-2 text-sm font-medium text-red-600 hover:bg-red-50">
                        <i class="fa-solid fa-ban"></i> Cancelar
                    </button>
                @endif
            </div>
        @endcan
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="rounded-xl border border-ink-200 bg-white p-6 shadow-sm">
            <h2 class="mb-4 text-base font-semibold">Detalle</h2>
            <dl class="space-y-3 text-sm">
                <div class="flex justify-between"><dt class="text-ink-400">Reserva</dt><dd>{{ $reserva->fecha_reserva->format('d/m/Y') }}</dd></div>
                <div class="flex justify-between"><dt class="text-ink-400">Entrega prog.</dt><dd>{{ $reserva->fecha_entrega_programada->format('d/m/Y') }}</dd></div>
                <div class="flex justify-between"><dt class="text-ink-400">Devolución prog.</dt><dd>{{ $reserva->fecha_devolucion_programada->format('d/m/Y') }}</dd></div>
                <div class="flex justify-between border-t border-ink-100 pt-2"><dt class="text-ink-400">Monto total</dt><dd class="font-medium">₲ {{ number_format($reserva->monto_total, 0, ',', '.') }}</dd></div>
                <div class="flex justify-between"><dt class="text-ink-400">Garantía</dt><dd>₲ {{ number_format($reserva->garantia_total, 0, ',', '.') }}</dd></div>
                <div class="flex justify-between"><dt class="text-ink-400">Seña garantía</dt><dd>₲ {{ number_format($reserva->senia_garantia, 0, ',', '.') }}</dd></div>
                <div class="flex justify-between"><dt class="text-ink-400">Seña alquiler</dt><dd>₲ {{ number_format($reserva->senia_alquiler, 0, ',', '.') }}</dd></div>
                <div class="flex justify-between border-t border-ink-100 pt-2"><dt class="text-ink-500">Total recibido</dt><dd class="font-semibold">₲ {{ number_format($reserva->total_recibido, 0, ',', '.') }}</dd></div>
                @if ($reserva->estado->value === 'cancelada')
                    <div class="flex justify-between"><dt class="text-ink-400">Seña devuelta</dt><dd>₲ {{ number_format($reserva->senia_devuelta ?? 0, 0, ',', '.') }}</dd></div>
                @endif
            </dl>
        </div>

        <div class="rounded-xl border border-ink-200 bg-white p-6 shadow-sm lg:col-span-2">
            <h2 class="mb-4 text-base font-semibold">Prendas</h2>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="text-left text-xs uppercase tracking-wider text-ink-500">
                        <tr class="border-b border-ink-100">
                            <th class="py-2 pr-4 font-medium">Prenda</th>
                            <th class="py-2 pr-4 font-medium">Código</th>
                            <th class="py-2 pr-4 font-medium">Talle</th>
                            <th class="py-2 text-right font-medium">Cantidad</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-ink-50">
                        @forelse ($reserva->stockItems as $s)
                            <tr>
                                <td class="py-2 pr-4 font-medium text-ink-800">{{ $s->nombre }}</td>
                                <td class="py-2 pr-4 font-mono text-xs text-ink-500">{{ $s->codigo }}</td>
                                <td class="py-2 pr-4 text-ink-600">{{ $tallesNombres[$s->pivot->talle_id] ?? '—' }}</td>
                                <td class="py-2 text-right tabular-nums">{{ $s->pivot->cantidad }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="py-6 text-center text-ink-400">Sin prendas.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($reserva->observaciones)
                <p class="mt-4 whitespace-pre-line border-t border-ink-100 pt-3 text-xs text-ink-500">{{ $reserva->observaciones }}</p>
            @endif
        </div>
    </div>

    {{-- Modal: convertir a alquiler --}}
    @if ($modalConvertir)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-black/40" wire:click="$set('modalConvertir', false)"></div>
            <div class="relative w-full max-w-md rounded-xl bg-white p-6 shadow-xl">
                <h2 class="mb-4 text-base font-semibold">Convertir a alquiler</h2>
                <div class="space-y-3">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-ink-700">Fecha de entrega real</label>
                        <input type="date" wire:model="fechaEntrega" class="w-full rounded-lg border border-ink-200 px-3 py-2 text-sm focus:border-ink-400 focus:outline-none focus:ring-1 focus:ring-ink-400">
                        @error('fechaEntrega') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-ink-700">Fecha de devolución</label>
                        <input type="date" wire:model="fechaDevolucion" class="w-full rounded-lg border border-ink-200 px-3 py-2 text-sm focus:border-ink-400 focus:outline-none focus:ring-1 focus:ring-ink-400">
                        @error('fechaDevolucion') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-ink-700">Observaciones de entrega</label>
                        <textarea wire:model="obsEntrega" rows="2" class="w-full rounded-lg border border-ink-200 px-3 py-2 text-sm focus:border-ink-400 focus:outline-none focus:ring-1 focus:ring-ink-400"></textarea>
                    </div>
                </div>
                <div class="mt-4 flex items-center justify-end gap-3">
                    <button type="button" wire:click="$set('modalConvertir', false)" class="rounded-lg px-4 py-2 text-sm font-medium text-ink-600 hover:bg-ink-100">Cancelar</button>
                    <button type="button" wire:click="convertir" class="inline-flex items-center gap-2 rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700">
                        <i class="fa-solid fa-check"></i> Confirmar
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal: cancelar --}}
    @if ($modalCancelar)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-black/40" wire:click="$set('modalCancelar', false)"></div>
            <div class="relative w-full max-w-md rounded-xl bg-white p-6 shadow-xl">
                <h2 class="mb-1 text-base font-semibold">Cancelar reserva</h2>
                <p class="mb-4 text-xs text-ink-500">Total recibido: ₲ {{ number_format($reserva->total_recibido, 0, ',', '.') }}</p>
                <div class="space-y-3">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-ink-700">Seña devuelta</label>
                        <x-input.money model="seniaDevuelta" />
                        @error('seniaDevuelta') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-ink-700">Motivo</label>
                        <input type="text" wire:model="motivo" class="w-full rounded-lg border border-ink-200 px-3 py-2 text-sm focus:border-ink-400 focus:outline-none focus:ring-1 focus:ring-ink-400">
                        @error('motivo') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-ink-700">Observaciones</label>
                        <textarea wire:model="obsCancelacion" rows="2" class="w-full rounded-lg border border-ink-200 px-3 py-2 text-sm focus:border-ink-400 focus:outline-none focus:ring-1 focus:ring-ink-400"></textarea>
                    </div>
                </div>
                <div class="mt-4 flex items-center justify-end gap-3">
                    <button type="button" wire:click="$set('modalCancelar', false)" class="rounded-lg px-4 py-2 text-sm font-medium text-ink-600 hover:bg-ink-100">Volver</button>
                    <button type="button" wire:click="cancelar" class="inline-flex items-center gap-2 rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700">
                        <i class="fa-solid fa-ban"></i> Cancelar reserva
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
