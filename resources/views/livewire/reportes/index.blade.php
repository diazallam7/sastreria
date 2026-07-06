<div>
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl">Reportes</h1>
            <p class="text-sm text-ink-500">Resumen de {{ $tipo === 'alquileres' ? 'alquileres' : 'ventas' }} por período.</p>
        </div>
        <div class="flex items-center gap-2">
            <select wire:model.live="tipo" class="rounded-lg border border-ink-200 bg-white px-3 py-2 text-sm focus:border-ink-400 focus:outline-none focus:ring-1 focus:ring-ink-400">
                <option value="ventas">Ventas</option>
                <option value="alquileres">Alquileres</option>
            </select>
            <select wire:model.live="intervalo" class="rounded-lg border border-ink-200 bg-white px-3 py-2 text-sm focus:border-ink-400 focus:outline-none focus:ring-1 focus:ring-ink-400">
                <option value="mensual">Mensual</option>
                <option value="anual">Anual</option>
            </select>
        </div>
    </div>

    <div class="rounded-xl border border-ink-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="text-left text-xs uppercase tracking-wider text-ink-500">
                    <tr class="border-b border-ink-100">
                        <th class="px-4 py-3 font-medium">Período</th>
                        <th class="px-4 py-3 text-right font-medium">Cantidad</th>
                        <th class="px-4 py-3 text-right font-medium">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-ink-50">
                    @forelse ($filas as $f)
                        <tr class="hover:bg-ink-50/50">
                            <td class="px-4 py-3 font-medium text-ink-900">{{ $f->periodo }}</td>
                            <td class="px-4 py-3 text-right tabular-nums">{{ $f->cantidad }}</td>
                            <td class="px-4 py-3 text-right tabular-nums text-green-700">₲ {{ number_format($f->total, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="px-4 py-10 text-center text-ink-400">Sin datos en el período.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
