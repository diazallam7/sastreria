<div>
    @php
        $estadoColor = ['pendiente' => 'bg-amber-100 text-amber-700', 'confirmada' => 'bg-blue-100 text-blue-700', 'entregada' => 'bg-green-100 text-green-700', 'cancelada' => 'bg-ink-100 text-ink-500'];
    @endphp

    <div class="mb-6 flex items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl">Reservas</h1>
            <p class="text-sm text-ink-500">Reservas de prendas con seña.</p>
        </div>
        @can('crear-reserva')
            <a href="{{ route('reservas.create') }}" wire:navigate
                class="inline-flex items-center gap-2 rounded-lg bg-ink-900 px-4 py-2 text-sm font-medium text-white hover:bg-ink-800">
                <i class="fa-solid fa-plus"></i> Nueva reserva
            </a>
        @endcan
    </div>

    <div class="rounded-xl border border-ink-200 bg-white shadow-sm">
        <div class="border-b border-ink-100 p-4">
            <div class="relative max-w-sm">
                <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-sm text-ink-400"></i>
                <input type="search" wire:model.live.debounce.300ms="buscar" placeholder="Buscar por cliente…"
                    class="w-full rounded-lg border border-ink-200 bg-ink-50 py-2 pl-9 pr-3 text-sm focus:border-ink-400 focus:outline-none focus:ring-1 focus:ring-ink-400">
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="text-left text-xs uppercase tracking-wider text-ink-500">
                    <tr class="border-b border-ink-100">
                        <th class="px-4 py-3 font-medium">Cliente</th>
                        <th class="px-4 py-3 font-medium">Prendas</th>
                        <th class="px-4 py-3 font-medium">Entrega prog.</th>
                        <th class="px-4 py-3 text-right font-medium">Monto</th>
                        <th class="px-4 py-3 text-right font-medium">Seña</th>
                        <th class="px-4 py-3 font-medium">Estado</th>
                        <th class="px-4 py-3 text-right font-medium">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-ink-50">
                    @forelse ($reservas as $r)
                        <tr class="hover:bg-ink-50/50" wire:key="res-{{ $r->id }}">
                            <td class="px-4 py-3 font-medium text-ink-900">{{ $r->cliente?->nombre ?? '—' }}</td>
                            <td class="px-4 py-3 text-xs text-ink-500">
                                @foreach ($r->stockItems as $s)
                                    <div>{{ $s->nombre }} <span class="text-ink-400">({{ $tallesNombres[$s->pivot->talle_id] ?? '—' }})@if ($s->pivot->cantidad > 1) ×{{ $s->pivot->cantidad }}@endif</span></div>
                                @endforeach
                            </td>
                            <td class="px-4 py-3 text-ink-600">{{ $r->fecha_entrega_programada->format('d/m/Y') }}</td>
                            <td class="px-4 py-3 text-right tabular-nums">₲ {{ number_format($r->monto_total, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right tabular-nums text-ink-500">₲ {{ number_format($r->total_recibido, 0, ',', '.') }}</td>
                            <td class="px-4 py-3">
                                <span class="rounded-full px-2.5 py-0.5 text-xs font-medium {{ $estadoColor[$r->estado->value] }}">{{ $r->estado->label() }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-1 text-ink-500">
                                    <a href="{{ route('reservas.show', $r) }}" wire:navigate data-tip="Ver"
                                        class="rounded p-2 hover:bg-ink-100 hover:text-ink-900"><i class="fa-solid fa-eye"></i></a>
                                    @if ($r->estado->value === 'confirmada')
                                        @can('editar-reserva')
                                            <button wire:click="abrirConvertir({{ $r->id }})" data-tip="Convertir a alquiler"
                                                class="rounded p-2 hover:bg-green-50 hover:text-green-600"><i class="fa-solid fa-right-left"></i></button>
                                        @endcan
                                    @endif
                                    @if (in_array($r->estado->value, ['pendiente', 'confirmada']))
                                        @can('editar-reserva')
                                            <a href="{{ route('reservas.edit', $r) }}" wire:navigate data-tip="Editar"
                                                class="rounded p-2 hover:bg-ink-100 hover:text-ink-900"><i class="fa-solid fa-pen"></i></a>
                                        @endcan
                                    @endif
                                    @can('eliminar-reserva')
                                        <button x-on:click="$store.confirm.open('¿Eliminar esta reserva?', () => $wire.eliminar({{ $r->id }}))" data-tip="Eliminar"
                                            class="rounded p-2 hover:bg-red-50 hover:text-red-600"><i class="fa-solid fa-trash"></i></button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-10 text-center text-ink-400">
                                <i class="fa-solid fa-calendar-check mb-2 block text-2xl"></i>
                                No hay reservas registradas.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-ink-100 p-4">
            {{ $reservas->links() }}
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
</div>
