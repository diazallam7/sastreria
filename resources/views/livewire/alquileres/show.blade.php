<div>
    @php
        $estadoColor = ['activo' => 'bg-blue-100 text-blue-700', 'completado' => 'bg-green-100 text-green-700', 'cancelado' => 'bg-ink-100 text-ink-500'];
        $dias = $alquiler->fecha_inicio->diffInDays($alquiler->fecha_fin) + 1;
    @endphp

    <div class="mb-6 flex items-start justify-between gap-4">
        <div>
            <a href="{{ route('alquileres.index') }}" wire:navigate class="text-sm text-ink-500 hover:text-ink-800">
                <i class="fa-solid fa-arrow-left"></i> Alquileres
            </a>
            <h1 class="mt-1 text-2xl">Alquiler #{{ $alquiler->id }}</h1>
            <div class="mt-1 flex items-center gap-2">
                <span class="text-sm text-ink-500">{{ $alquiler->cliente?->nombre ?? '—' }}</span>
                <span class="rounded-full px-2.5 py-0.5 text-xs font-medium {{ $estadoColor[$alquiler->estado->value] }}">{{ $alquiler->estado->label() }}</span>
            </div>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('factura.alquiler', $alquiler) }}" target="_blank"
                class="inline-flex items-center gap-2 rounded-lg border border-ink-200 bg-white px-4 py-2 text-sm font-medium text-ink-700 hover:bg-ink-50">
                <i class="fa-solid fa-file-pdf"></i> Factura
            </a>
            @if ($alquiler->estaActivo())
                @can('editar-alquiler')
                    <button x-on:click="$store.confirm.open('¿Registrar la devolución de este alquiler?', () => $wire.devolver())"
                        class="inline-flex items-center gap-2 rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700">
                        <i class="fa-solid fa-rotate-left"></i> Registrar devolución
                    </button>
                @endcan
            @endif
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="rounded-xl border border-ink-200 bg-white p-6 shadow-sm">
            <h2 class="mb-4 text-base font-semibold">Detalle</h2>
            <dl class="space-y-3 text-sm">
                <div class="flex justify-between"><dt class="text-ink-400">Inicio</dt><dd>{{ $alquiler->fecha_inicio->format('d/m/Y') }}</dd></div>
                <div class="flex justify-between"><dt class="text-ink-400">Devolución</dt><dd>{{ $alquiler->fecha_fin->format('d/m/Y') }}</dd></div>
                <div class="flex justify-between"><dt class="text-ink-400">Días</dt><dd>{{ $dias }}</dd></div>
                <div class="flex justify-between"><dt class="text-ink-400">Costo total</dt><dd class="font-medium">₲ {{ number_format($alquiler->costo_total, 0, ',', '.') }}</dd></div>
                <div class="flex justify-between"><dt class="text-ink-400">Garantía</dt><dd>₲ {{ number_format($alquiler->garantia, 0, ',', '.') }}</dd></div>
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
                        @forelse ($alquiler->stockItems as $s)
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
        </div>
    </div>
</div>
