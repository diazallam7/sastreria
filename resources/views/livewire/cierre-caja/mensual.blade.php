<div>
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl">Cierre mensual</h1>
            <p class="text-sm text-ink-500">{{ $nombreMes }} {{ $año }}</p>
        </div>
        <div class="flex items-center gap-2">
            <input type="date" wire:model.live="fecha" class="rounded-lg border border-ink-200 px-3 py-2 text-sm focus:border-ink-400 focus:outline-none focus:ring-1 focus:ring-ink-400">
            @can('exportar-cierre-caja')
                <a href="{{ route('cierre-caja.mensual.pdf', ['fecha' => $fecha]) }}" target="_blank"
                    class="inline-flex items-center gap-2 rounded-lg border border-ink-200 bg-white px-4 py-2 text-sm font-medium text-ink-700 hover:bg-ink-50">
                    <i class="fa-solid fa-file-pdf"></i> PDF
                </a>
            @endcan
        </div>
    </div>

    <x-cierre.tabs activo="mensual" />

    <div class="grid gap-4 sm:grid-cols-3">
        <div class="rounded-xl border border-ink-200 bg-white p-5 shadow-sm">
            <div class="text-xs uppercase tracking-wider text-ink-500">Ingresos</div>
            <div class="mt-1 font-serif text-2xl font-semibold text-green-600">₲ {{ number_format($totalesMes['ingresos'], 0, ',', '.') }}</div>
        </div>
        <div class="rounded-xl border border-ink-200 bg-white p-5 shadow-sm">
            <div class="text-xs uppercase tracking-wider text-ink-500">Egresos</div>
            <div class="mt-1 font-serif text-2xl font-semibold text-red-600">₲ {{ number_format($totalesMes['egresos'], 0, ',', '.') }}</div>
        </div>
        <div class="rounded-xl border border-ink-200 p-5 shadow-sm {{ $totalesMes['saldo_neto'] >= 0 ? 'bg-ink-900 text-white' : 'bg-red-600 text-white' }}">
            <div class="text-xs uppercase tracking-wider opacity-70">Saldo neto</div>
            <div class="mt-1 font-serif text-2xl font-semibold">₲ {{ number_format($totalesMes['saldo_neto'], 0, ',', '.') }}</div>
        </div>
    </div>

    <div class="mt-6 rounded-xl border border-ink-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="text-left text-xs uppercase tracking-wider text-ink-500">
                    <tr class="border-b border-ink-100">
                        <th class="px-4 py-3 font-medium">Semana</th>
                        <th class="px-4 py-3 font-medium">Período</th>
                        <th class="px-4 py-3 text-right font-medium">Ingresos</th>
                        <th class="px-4 py-3 text-right font-medium">Egresos</th>
                        <th class="px-4 py-3 text-right font-medium">Saldo</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-ink-50">
                    @foreach ($movimientosMensuales as $s)
                        <tr class="hover:bg-ink-50/50">
                            <td class="px-4 py-3 font-medium text-ink-800">Semana {{ $s['semana'] }}</td>
                            <td class="px-4 py-3 text-ink-500">{{ $s['fecha_inicio'] }} — {{ $s['fecha_fin'] }}</td>
                            <td class="px-4 py-3 text-right tabular-nums text-green-600">₲ {{ number_format($s['ingresos'], 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right tabular-nums text-red-600">₲ {{ number_format($s['egresos'], 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right font-medium tabular-nums {{ $s['saldo_neto'] >= 0 ? 'text-ink-900' : 'text-red-600' }}">₲ {{ number_format($s['saldo_neto'], 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6 grid gap-4 sm:grid-cols-3">
        <div class="rounded-xl border border-ink-200 bg-white p-5 shadow-sm">
            <div class="text-xs uppercase tracking-wider text-ink-500">Mejor semana</div>
            <div class="mt-1 font-medium text-ink-900">{{ $mejorSemana['numero'] ? 'Semana ' . $mejorSemana['numero'] : '—' }}</div>
            <div class="text-sm text-green-600">₲ {{ number_format(max(0, $mejorSemana['saldo']), 0, ',', '.') }}</div>
        </div>
        <div class="rounded-xl border border-ink-200 bg-white p-5 shadow-sm">
            <div class="text-xs uppercase tracking-wider text-ink-500">Peor semana</div>
            <div class="mt-1 font-medium text-ink-900">{{ $peorSemana['numero'] ? 'Semana ' . $peorSemana['numero'] : '—' }}</div>
            <div class="text-sm text-ink-600">₲ {{ number_format($peorSemana['saldo'] === PHP_INT_MAX ? 0 : $peorSemana['saldo'], 0, ',', '.') }}</div>
        </div>
        <div class="rounded-xl border border-ink-200 bg-white p-5 shadow-sm">
            <div class="text-xs uppercase tracking-wider text-ink-500">Promedio semanal</div>
            <div class="mt-1 font-medium text-ink-900">₲ {{ number_format($promedios['saldo_neto'], 0, ',', '.') }}</div>
            <div class="text-xs text-ink-400">saldo neto</div>
        </div>
    </div>
</div>
