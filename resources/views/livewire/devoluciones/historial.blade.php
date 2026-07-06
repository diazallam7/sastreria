<div>
    <div class="mb-6 flex items-center justify-between gap-4">
        <div>
            <a href="{{ route('devoluciones.index') }}" wire:navigate class="text-sm text-ink-500 hover:text-ink-800">
                <i class="fa-solid fa-arrow-left"></i> Devoluciones
            </a>
            <h1 class="mt-1 text-2xl">Historial de devoluciones</h1>
        </div>
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
                        <th class="px-4 py-3 font-medium">Fecha</th>
                        <th class="px-4 py-3 font-medium">Cliente</th>
                        <th class="px-4 py-3 text-right font-medium">Atraso</th>
                        <th class="px-4 py-3 text-right font-medium">Multa</th>
                        <th class="px-4 py-3 text-right font-medium">Devuelto</th>
                        <th class="px-4 py-3 text-right font-medium"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-ink-50">
                    @forelse ($devoluciones as $d)
                        <tr class="hover:bg-ink-50/50" wire:key="devh-{{ $d->id }}">
                            <td class="px-4 py-3 text-ink-600">{{ $d->fecha_devolucion->format('d/m/Y H:i') }}</td>
                            <td class="px-4 py-3 font-medium text-ink-900">{{ $d->alquiler?->cliente?->nombre ?? '—' }}</td>
                            <td class="px-4 py-3 text-right tabular-nums {{ $d->dias_retraso > 0 ? 'text-red-600' : 'text-ink-400' }}">{{ $d->dias_retraso }} día{{ $d->dias_retraso == 1 ? '' : 's' }}</td>
                            <td class="px-4 py-3 text-right tabular-nums {{ $d->multa_aplicada > 0 ? 'text-red-600' : 'text-ink-400' }}">₲ {{ number_format($d->multa_aplicada, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right tabular-nums">₲ {{ number_format($d->monto_devuelto, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('devoluciones.comprobante', $d) }}" wire:navigate data-tip="Comprobante"
                                    class="rounded p-2 text-ink-500 hover:bg-ink-100 hover:text-ink-900"><i class="fa-solid fa-receipt"></i></a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-10 text-center text-ink-400">Sin devoluciones registradas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-ink-100 p-4">
            {{ $devoluciones->links() }}
        </div>
    </div>
</div>
