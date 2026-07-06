@props(['nombre', 'monto', 'negativo' => false])

<div class="flex justify-between">
    <span class="text-ink-600">{{ $nombre }}</span>
    <span class="tabular-nums {{ $negativo ? 'text-red-600' : 'text-ink-800' }}">{{ $negativo ? '−' : '' }}₲ {{ number_format($monto, 0, ',', '.') }}</span>
</div>
