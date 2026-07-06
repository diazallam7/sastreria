<div>
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl">Cierre semanal</h1>
            <p class="text-sm text-ink-500">{{ $fechaInicio->format('d/m/Y') }} — {{ $fechaFin->format('d/m/Y') }}</p>
        </div>
        <div class="flex items-center gap-2">
            <input type="date" wire:model.live="fecha" class="rounded-lg border border-ink-200 px-3 py-2 text-sm focus:border-ink-400 focus:outline-none focus:ring-1 focus:ring-ink-400">
            @can('exportar-cierre-caja')
                <a href="{{ route('cierre-caja.semanal.pdf', ['fecha' => $fecha]) }}" target="_blank"
                    class="inline-flex items-center gap-2 rounded-lg border border-ink-200 bg-white px-4 py-2 text-sm font-medium text-ink-700 hover:bg-ink-50">
                    <i class="fa-solid fa-file-pdf"></i> PDF
                </a>
            @endcan
        </div>
    </div>

    <x-cierre.tabs activo="semanal" />

    <div class="grid gap-4 sm:grid-cols-3">
        <div class="rounded-xl border border-ink-200 bg-white p-5 shadow-sm">
            <div class="text-xs uppercase tracking-wider text-ink-500">Ingresos</div>
            <div class="mt-1 font-serif text-2xl font-semibold text-green-600">₲ {{ number_format($totalesSemana['ingresos'], 0, ',', '.') }}</div>
        </div>
        <div class="rounded-xl border border-ink-200 bg-white p-5 shadow-sm">
            <div class="text-xs uppercase tracking-wider text-ink-500">Egresos</div>
            <div class="mt-1 font-serif text-2xl font-semibold text-red-600">₲ {{ number_format($totalesSemana['egresos'], 0, ',', '.') }}</div>
        </div>
        <div class="rounded-xl border border-ink-200 p-5 shadow-sm {{ $totalesSemana['saldo_neto'] >= 0 ? 'bg-ink-900 text-white' : 'bg-red-600 text-white' }}">
            <div class="text-xs uppercase tracking-wider opacity-70">Saldo neto</div>
            <div class="mt-1 font-serif text-2xl font-semibold">₲ {{ number_format($totalesSemana['saldo_neto'], 0, ',', '.') }}</div>
        </div>
    </div>

    <div class="mt-6 rounded-xl border border-ink-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="text-left text-xs uppercase tracking-wider text-ink-500">
                    <tr class="border-b border-ink-100">
                        <th class="px-4 py-3 font-medium">Día</th>
                        <th class="px-4 py-3 text-right font-medium">Ingresos</th>
                        <th class="px-4 py-3 text-right font-medium">Egresos</th>
                        <th class="px-4 py-3 text-right font-medium">Saldo</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-ink-50">
                    @foreach ($resumenSemanal as $d)
                        <tr class="hover:bg-ink-50/50">
                            <td class="px-4 py-3"><span class="font-medium text-ink-800">{{ $d['dia'] }}</span> <span class="text-xs text-ink-400">{{ \Carbon\Carbon::parse($d['fecha'])->format('d/m') }}</span></td>
                            <td class="px-4 py-3 text-right tabular-nums text-green-600">₲ {{ number_format($d['ingresos'], 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right tabular-nums text-red-600">₲ {{ number_format($d['egresos'], 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right font-medium tabular-nums {{ $d['saldo_neto'] >= 0 ? 'text-ink-900' : 'text-red-600' }}">₲ {{ number_format($d['saldo_neto'], 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6 grid gap-4 sm:grid-cols-3">
        <div class="rounded-xl border border-ink-200 bg-white p-5 shadow-sm">
            <div class="text-xs uppercase tracking-wider text-ink-500">Mejor día</div>
            <div class="mt-1 font-medium text-ink-900">{{ $mejorDia['dia'] ?: '—' }}</div>
            <div class="text-sm text-green-600">₲ {{ number_format(max(0, $mejorDia['saldo']), 0, ',', '.') }}</div>
        </div>
        <div class="rounded-xl border border-ink-200 bg-white p-5 shadow-sm">
            <div class="text-xs uppercase tracking-wider text-ink-500">Peor día</div>
            <div class="mt-1 font-medium text-ink-900">{{ $peorDia['dia'] ?: '—' }}</div>
            <div class="text-sm text-ink-600">₲ {{ number_format($peorDia['saldo'] === PHP_INT_MAX ? 0 : $peorDia['saldo'], 0, ',', '.') }}</div>
        </div>
        <div class="rounded-xl border border-ink-200 bg-white p-5 shadow-sm">
            <div class="text-xs uppercase tracking-wider text-ink-500">Promedio diario</div>
            <div class="mt-1 font-medium text-ink-900">₲ {{ number_format($promedios['saldo_neto'], 0, ',', '.') }}</div>
            <div class="text-xs text-ink-400">saldo neto</div>
        </div>
    </div>
</div>
