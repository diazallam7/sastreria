<div>
    <div class="mb-6 flex items-start justify-between gap-4">
        <div>
            <a href="{{ route('ventas.index') }}" wire:navigate class="text-sm text-ink-500 hover:text-ink-800">
                <i class="fa-solid fa-arrow-left"></i> Ventas
            </a>
            <h1 class="mt-1 text-2xl">Venta #{{ $venta->id }}</h1>
            <p class="text-sm text-ink-500">
                {{ $venta->fecha_venta->format('d/m/Y H:i') }} ·
                {{ $venta->cliente?->nombre ?? 'Consumidor final' }} ·
                Cajero: {{ $venta->user?->name ?? '—' }}
            </p>
        </div>
        <a href="{{ route('factura.venta', $venta) }}" target="_blank"
            class="inline-flex items-center gap-2 rounded-lg border border-ink-200 bg-white px-4 py-2 text-sm font-medium text-ink-700 hover:bg-ink-50">
            <i class="fa-solid fa-file-pdf"></i> Factura
        </a>
    </div>

    <div class="rounded-xl border border-ink-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="text-left text-xs uppercase tracking-wider text-ink-500">
                    <tr class="border-b border-ink-100">
                        <th class="px-6 py-3 font-medium">Producto</th>
                        <th class="px-4 py-3 font-medium">Talle</th>
                        <th class="px-4 py-3 text-right font-medium">Precio</th>
                        <th class="px-4 py-3 text-right font-medium">Cant.</th>
                        <th class="px-6 py-3 text-right font-medium">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-ink-50">
                    @foreach ($venta->detalles as $d)
                        <tr>
                            <td class="px-6 py-3 font-medium text-ink-900">{{ $d->nombre_producto }}</td>
                            <td class="px-4 py-3 text-ink-600">{{ $d->talle ?? '—' }}</td>
                            <td class="px-4 py-3 text-right tabular-nums">₲ {{ number_format($d->precio_unitario, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right tabular-nums">{{ $d->cantidad }}</td>
                            <td class="px-6 py-3 text-right font-medium tabular-nums">₲ {{ number_format($d->subtotal, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="border-t border-ink-200">
                        <td colspan="4" class="px-6 py-3 text-right text-sm font-medium text-ink-500">Total</td>
                        <td class="px-6 py-3 text-right font-serif text-lg font-semibold text-ink-900">₲ {{ number_format($venta->precio_total, 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
