<div>
    <div class="mb-6 flex items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl">Devoluciones</h1>
            <p class="text-sm text-ink-500">Alquileres activos pendientes de devolución.</p>
        </div>
        @can('ver-devolucion')
            <a href="{{ route('devoluciones.historial') }}" wire:navigate
                class="inline-flex items-center gap-2 rounded-lg border border-ink-200 bg-white px-4 py-2 text-sm font-medium text-ink-700 hover:bg-ink-50">
                <i class="fa-solid fa-clock-rotate-left"></i> Historial
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
                        <th class="px-4 py-3 font-medium">Vence</th>
                        <th class="px-4 py-3 text-right font-medium">Atraso</th>
                        <th class="px-4 py-3 text-right font-medium">Multa</th>
                        <th class="px-4 py-3 text-right font-medium">Garantía</th>
                        <th class="px-4 py-3 text-right font-medium">Acción</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-ink-50">
                    @forelse ($alquileres as $a)
                        @php
                            $venc = $a->fecha_fin->copy()->startOfDay();
                            $dias = $venc->lt(today()) ? $venc->diffInDays(today()) : 0;
                            $multa = $dias * $multaDiaria;
                        @endphp
                        <tr class="hover:bg-ink-50/50" wire:key="dev-{{ $a->id }}">
                            <td class="px-4 py-3 font-medium text-ink-900">{{ $a->cliente?->nombre ?? '—' }}</td>
                            <td class="px-4 py-3 text-xs text-ink-500">
                                @foreach ($a->stockItems as $s)
                                    <div>{{ $s->nombre }} <span class="text-ink-400">({{ $tallesNombres[$s->pivot->talle_id] ?? '—' }})</span></div>
                                @endforeach
                            </td>
                            <td class="px-4 py-3 text-ink-600">{{ $a->fecha_fin->format('d/m/Y') }}</td>
                            <td class="px-4 py-3 text-right tabular-nums {{ $dias > 0 ? 'font-medium text-red-600' : 'text-ink-400' }}">
                                {{ $dias > 0 ? $dias . ' día' . ($dias > 1 ? 's' : '') : 'A tiempo' }}
                            </td>
                            <td class="px-4 py-3 text-right tabular-nums {{ $multa > 0 ? 'text-red-600' : 'text-ink-400' }}">₲ {{ number_format($multa, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right tabular-nums text-ink-500">₲ {{ number_format($a->garantia, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right">
                                @can('crear-devolucion')
                                    <button wire:click="abrirProcesar({{ $a->id }})"
                                        class="inline-flex items-center gap-1.5 rounded-lg bg-ink-900 px-3 py-1.5 text-xs font-medium text-white hover:bg-ink-800">
                                        <i class="fa-solid fa-rotate-left"></i> Procesar
                                    </button>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-10 text-center text-ink-400">
                                <i class="fa-solid fa-circle-check mb-2 block text-2xl"></i>
                                No hay alquileres pendientes de devolución.
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

    {{-- Modal: procesar devolución --}}
    @if ($modalProcesar)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4"
            x-data="{
                aplicada: @entangle('multaAplicada'),
                calculada: {{ $multaCalculada }},
                garantia: {{ $garantiaOriginal }},
                get val() { return parseInt(this.aplicada || 0) || 0; },
                get ajustada() { return this.val !== this.calculada; },
                get monto() { return Math.max(0, this.garantia - this.val); },
                get faltante() { return Math.max(0, this.val - this.garantia); },
                fmt(n) { return new Intl.NumberFormat('es-PY').format(n); },
            }">
            <div class="fixed inset-0 bg-black/40" wire:click="$set('modalProcesar', false)"></div>
            <div class="relative w-full max-w-lg rounded-xl bg-white p-6 shadow-xl">
                <h2 class="text-base font-semibold">Procesar devolución</h2>
                <p class="mb-4 text-sm text-ink-500">{{ $clienteNombre }}</p>

                <div class="mb-4 grid grid-cols-3 gap-3 text-sm">
                    <div class="rounded-lg bg-ink-50 p-3">
                        <div class="text-xs text-ink-400">Atraso</div>
                        <div class="font-medium {{ $diasRetraso > 0 ? 'text-red-600' : 'text-ink-800' }}">{{ $diasRetraso }} día{{ $diasRetraso == 1 ? '' : 's' }}</div>
                    </div>
                    <div class="rounded-lg bg-ink-50 p-3">
                        <div class="text-xs text-ink-400">Multa calculada</div>
                        <div class="font-medium text-ink-800">₲ {{ number_format($multaCalculada, 0, ',', '.') }}</div>
                    </div>
                    <div class="rounded-lg bg-ink-50 p-3">
                        <div class="text-xs text-ink-400">Garantía</div>
                        <div class="font-medium text-ink-800">₲ {{ number_format($garantiaOriginal, 0, ',', '.') }}</div>
                    </div>
                </div>

                <div class="space-y-3">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-ink-700">Multa a aplicar</label>
                        <x-input.money model="multaAplicada" />
                        @error('multaAplicada') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    {{-- Motivo: obligatorio si ajusta la multa --}}
                    <div x-show="ajustada" x-cloak>
                        <label class="mb-1 block text-sm font-medium text-ink-700">Motivo del ajuste <span class="text-red-500">*</span></label>
                        <input type="text" wire:model="motivo" class="w-full rounded-lg border border-ink-200 px-3 py-2 text-sm focus:border-ink-400 focus:outline-none focus:ring-1 focus:ring-ink-400">
                        @error('motivo') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-ink-700">Observaciones</label>
                        <textarea wire:model="observaciones" rows="2" class="w-full rounded-lg border border-ink-200 px-3 py-2 text-sm focus:border-ink-400 focus:outline-none focus:ring-1 focus:ring-ink-400"></textarea>
                    </div>

                    <div class="rounded-lg border border-ink-200 p-3 text-sm">
                        <div class="flex justify-between">
                            <span class="text-ink-500">Monto a devolver al cliente</span>
                            <span class="font-semibold text-ink-900">₲ <span x-text="fmt(monto)"></span></span>
                        </div>
                        <template x-if="faltante > 0">
                            <div class="mt-2 flex justify-between rounded-lg bg-amber-50 px-2 py-1.5 text-amber-800">
                                <span><i class="fa-solid fa-triangle-exclamation mr-1"></i> Faltante a cobrar (mora supera garantía)</span>
                                <span class="font-semibold">₲ <span x-text="fmt(faltante)"></span></span>
                            </div>
                        </template>
                    </div>
                </div>

                <div class="mt-5 flex items-center justify-end gap-3">
                    <button type="button" wire:click="$set('modalProcesar', false)" class="rounded-lg px-4 py-2 text-sm font-medium text-ink-600 hover:bg-ink-100">Cancelar</button>
                    <button type="button" wire:click="procesar" wire:loading.attr="disabled"
                        class="inline-flex items-center gap-2 rounded-lg bg-ink-900 px-4 py-2 text-sm font-medium text-white hover:bg-ink-800 disabled:opacity-60">
                        <i class="fa-solid fa-check"></i> Confirmar devolución
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
