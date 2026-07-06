<div>
    @php
        $estadoColor = ['activo' => 'bg-blue-100 text-blue-700', 'completado' => 'bg-green-100 text-green-700', 'cancelado' => 'bg-ink-100 text-ink-500'];
    @endphp

    <div class="mb-6 flex items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl">Alquileres</h1>
            <p class="text-sm text-ink-500">Prendas entregadas en alquiler.</p>
        </div>
        @can('crear-alquiler')
            <a href="{{ route('alquileres.create') }}" wire:navigate
                class="inline-flex items-center gap-2 rounded-lg bg-ink-900 px-4 py-2 text-sm font-medium text-white hover:bg-ink-800">
                <i class="fa-solid fa-plus"></i> Nuevo alquiler
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
                        <th class="px-4 py-3 font-medium">Período</th>
                        <th class="px-4 py-3 text-right font-medium">Costo</th>
                        <th class="px-4 py-3 text-right font-medium">Garantía</th>
                        <th class="px-4 py-3 font-medium">Estado</th>
                        <th class="px-4 py-3 text-right font-medium">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-ink-50">
                    @forelse ($alquileres as $a)
                        <tr class="hover:bg-ink-50/50" wire:key="alq-{{ $a->id }}">
                            <td class="px-4 py-3 font-medium text-ink-900">{{ $a->cliente?->nombre ?? '—' }}</td>
                            <td class="px-4 py-3 text-xs text-ink-500">
                                @foreach ($a->stockItems as $s)
                                    <div>{{ $s->nombre }} <span class="text-ink-400">({{ $tallesNombres[$s->pivot->talle_id] ?? '—' }})@if ($s->pivot->cantidad > 1) ×{{ $s->pivot->cantidad }}@endif</span></div>
                                @endforeach
                            </td>
                            <td class="px-4 py-3 text-ink-600">
                                {{ $a->fecha_inicio->format('d/m/Y') }} → {{ $a->fecha_fin->format('d/m/Y') }}
                            </td>
                            <td class="px-4 py-3 text-right tabular-nums">₲ {{ number_format($a->costo_total, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right tabular-nums text-ink-500">₲ {{ number_format($a->garantia, 0, ',', '.') }}</td>
                            <td class="px-4 py-3">
                                <span class="rounded-full px-2.5 py-0.5 text-xs font-medium {{ $estadoColor[$a->estado->value] }}">{{ $a->estado->label() }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-1 text-ink-500">
                                    <a href="{{ route('alquileres.show', $a) }}" wire:navigate data-tip="Ver"
                                        class="rounded p-2 hover:bg-ink-100 hover:text-ink-900"><i class="fa-solid fa-eye"></i></a>
                                    @if ($a->estaActivo())
                                        @can('editar-alquiler')
                                            <a href="{{ route('alquileres.edit', $a) }}" wire:navigate data-tip="Editar"
                                                class="rounded p-2 hover:bg-ink-100 hover:text-ink-900"><i class="fa-solid fa-pen"></i></a>
                                            <button x-on:click="$store.confirm.open('¿Registrar la devolución de este alquiler?', () => $wire.devolver({{ $a->id }}))" data-tip="Devolver"
                                                class="rounded p-2 hover:bg-green-50 hover:text-green-600"><i class="fa-solid fa-rotate-left"></i></button>
                                        @endcan
                                    @endif
                                    @can('eliminar-alquiler')
                                        <button x-on:click="$store.confirm.open('¿Eliminar este alquiler?', () => $wire.anular({{ $a->id }}))" data-tip="Eliminar"
                                            class="rounded p-2 hover:bg-red-50 hover:text-red-600"><i class="fa-solid fa-trash"></i></button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-10 text-center text-ink-400">
                                <i class="fa-solid fa-shirt mb-2 block text-2xl"></i>
                                No hay alquileres registrados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-ink-100 p-4">
            {{ $alquileres->links() }}
        </div>
    </div>
</div>
