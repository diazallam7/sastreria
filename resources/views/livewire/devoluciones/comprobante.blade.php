<div>
    @php $faltante = max(0, $devolucion->multa_aplicada - $devolucion->garantia_original); @endphp

    <div class="mb-6">
        <a href="{{ route('devoluciones.historial') }}" wire:navigate class="text-sm text-ink-500 hover:text-ink-800">
            <i class="fa-solid fa-arrow-left"></i> Historial
        </a>
        <h1 class="mt-1 text-2xl">Comprobante de devolución #{{ $devolucion->id }}</h1>
    </div>

    <div class="rounded-xl border border-ink-200 bg-white p-6 shadow-sm">
        <div class="mb-4 flex items-center justify-between border-b border-ink-100 pb-4">
            <div>
                <div class="font-semibold text-ink-900">{{ $devolucion->alquiler?->cliente?->nombre ?? '—' }}</div>
                <div class="text-sm text-ink-500">Alquiler #{{ $devolucion->alquiler_id }} · {{ $devolucion->fecha_devolucion->format('d/m/Y H:i') }}</div>
            </div>
            <div class="text-right text-sm text-ink-500">Cajero<br><span class="text-ink-800">{{ $devolucion->user?->name ?? '—' }}</span></div>
        </div>

        <div class="mb-4">
            <h2 class="mb-2 text-sm font-semibold text-ink-500">Prendas devueltas</h2>
            @foreach ($devolucion->alquiler->stockItems as $s)
                <div class="text-sm text-ink-700">{{ $s->nombre }} <span class="text-ink-400">({{ $tallesNombres[$s->pivot->talle_id] ?? '—' }}) ×{{ $s->pivot->cantidad }}</span></div>
            @endforeach
        </div>

        <dl class="space-y-2 border-t border-ink-100 pt-4 text-sm">
            <div class="flex justify-between"><dt class="text-ink-500">Días de atraso</dt><dd class="{{ $devolucion->dias_retraso > 0 ? 'font-medium text-red-600' : '' }}">{{ $devolucion->dias_retraso }}</dd></div>
            <div class="flex justify-between"><dt class="text-ink-500">Multa calculada</dt><dd>₲ {{ number_format($devolucion->multa_calculada, 0, ',', '.') }}</dd></div>
            <div class="flex justify-between"><dt class="text-ink-500">Multa aplicada</dt><dd class="font-medium">₲ {{ number_format($devolucion->multa_aplicada, 0, ',', '.') }}</dd></div>
            @if ($devolucion->motivo_ajuste)
                <div class="flex justify-between"><dt class="text-ink-500">Motivo del ajuste</dt><dd class="text-right">{{ $devolucion->motivo_ajuste }}</dd></div>
            @endif
            <div class="flex justify-between"><dt class="text-ink-500">Garantía original</dt><dd>₲ {{ number_format($devolucion->garantia_original, 0, ',', '.') }}</dd></div>
            <div class="flex justify-between border-t border-ink-100 pt-2"><dt class="font-medium text-ink-700">Monto devuelto</dt><dd class="font-serif text-lg font-semibold text-ink-900">₲ {{ number_format($devolucion->monto_devuelto, 0, ',', '.') }}</dd></div>
            @if ($faltante > 0)
                <div class="flex justify-between rounded-lg bg-amber-50 px-2 py-1.5 text-amber-800">
                    <dt><i class="fa-solid fa-triangle-exclamation mr-1"></i> Faltante cobrado (mora > garantía)</dt>
                    <dd class="font-semibold">₲ {{ number_format($faltante, 0, ',', '.') }}</dd>
                </div>
            @endif
        </dl>

        @if ($devolucion->observaciones)
            <p class="mt-4 whitespace-pre-line border-t border-ink-100 pt-3 text-xs text-ink-500">{{ $devolucion->observaciones }}</p>
        @endif
    </div>
</div>
