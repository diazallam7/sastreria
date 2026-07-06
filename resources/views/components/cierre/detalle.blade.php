@props(['titulo', 'vacio' => false])

<div class="rounded-xl border border-ink-200 bg-white p-5 shadow-sm">
    <h3 class="mb-2 text-sm font-semibold">{{ $titulo }}</h3>
    @if ($vacio)
        <p class="text-xs text-ink-400">Sin movimientos.</p>
    @else
        <div class="space-y-1 text-sm">{{ $slot }}</div>
    @endif
</div>
