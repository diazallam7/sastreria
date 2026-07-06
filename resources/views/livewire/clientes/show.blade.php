<div>
    <div class="mb-6 flex items-start justify-between gap-4">
        <div>
            <a href="{{ route('clientes.index') }}" wire:navigate class="text-sm text-ink-500 hover:text-ink-800">
                <i class="fa-solid fa-arrow-left"></i> Clientes
            </a>
            <h1 class="mt-1 text-2xl">{{ $cliente->nombre }}</h1>
            <span class="rounded-full px-2.5 py-0.5 text-xs font-medium {{ $cliente->estado ? 'bg-green-100 text-green-700' : 'bg-ink-100 text-ink-500' }}">
                {{ $cliente->estado ? 'Activo' : 'Inactivo' }}
            </span>
        </div>
        @can('editar-cliente')
            <a href="{{ route('clientes.edit', $cliente) }}" wire:navigate
                class="inline-flex items-center gap-2 rounded-lg border border-ink-200 bg-white px-4 py-2 text-sm font-medium text-ink-700 hover:bg-ink-50">
                <i class="fa-solid fa-pen"></i> Editar
            </a>
        @endcan
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        {{-- Datos --}}
        <div class="rounded-xl border border-ink-200 bg-white p-6 shadow-sm">
            <h2 class="mb-4 text-base font-semibold">Contacto</h2>
            <dl class="space-y-3 text-sm">
                <div><dt class="text-ink-400">Documento</dt><dd class="text-ink-800">{{ $cliente->documento ?: '—' }}</dd></div>
                <div><dt class="text-ink-400">Teléfono</dt><dd class="text-ink-800">{{ $cliente->telefono ?: '—' }}</dd></div>
                <div><dt class="text-ink-400">Correo</dt><dd class="text-ink-800">{{ $cliente->correo ?: '—' }}</dd></div>
                <div><dt class="text-ink-400">Dirección</dt><dd class="text-ink-800">{{ $cliente->direccion ?: '—' }}</dd></div>
            </dl>
        </div>

        {{-- Medidas --}}
        <div class="rounded-xl border border-ink-200 bg-white p-6 shadow-sm lg:col-span-2">
            <h2 class="mb-4 text-base font-semibold">Medidas vigentes</h2>
            @forelse ($cliente->medidasVigentes as $medida)
                <div class="mb-4 last:mb-0">
                    <h3 class="mb-2 font-serif text-sm font-semibold capitalize text-accent-700">{{ $medida->tipo->value }}</h3>
                    <div class="grid grid-cols-3 gap-2 text-sm sm:grid-cols-4">
                        @foreach ($medida->medidas as $campo => $valor)
                            <div class="rounded-lg bg-ink-50 px-2.5 py-1.5">
                                <div class="text-[11px] text-ink-400">{{ ucfirst(str_replace('_', ' ', $campo)) }}</div>
                                <div class="font-medium text-ink-800">{{ $valor }}</div>
                            </div>
                        @endforeach
                    </div>
                    @if ($medida->observaciones)
                        <p class="mt-2 text-xs text-ink-500">{{ $medida->observaciones }}</p>
                    @endif
                </div>
            @empty
                <p class="text-sm text-ink-400">Sin medidas registradas.</p>
            @endforelse
        </div>
    </div>

    {{-- Actividad --}}
    <div class="mt-6 grid gap-6 md:grid-cols-3">
        <div class="rounded-xl border border-ink-200 bg-white p-5 shadow-sm">
            <h3 class="mb-3 text-sm font-semibold">Últimas ventas</h3>
            <ul class="space-y-2 text-sm">
                @forelse ($cliente->ventas as $v)
                    <li class="flex justify-between"><span class="text-ink-500">{{ $v->fecha_venta->format('d/m/Y') }}</span>
                        <span class="font-medium">₲ {{ number_format($v->precio_total, 0, ',', '.') }}</span></li>
                @empty
                    <li class="text-ink-400">Sin ventas.</li>
                @endforelse
            </ul>
        </div>
        <div class="rounded-xl border border-ink-200 bg-white p-5 shadow-sm">
            <h3 class="mb-3 text-sm font-semibold">Últimos alquileres</h3>
            <ul class="space-y-2 text-sm">
                @forelse ($cliente->alquileres as $a)
                    <li class="flex items-center justify-between gap-2">
                        <span class="text-ink-500">{{ $a->fecha_inicio->format('d/m/Y') }}</span>
                        <span class="flex items-center gap-1.5">
                            @if ($a->devolucion && $a->devolucion->multa_aplicada > 0)
                                <span class="rounded-full bg-red-100 px-1.5 py-0.5 text-[10px] font-medium text-red-700">Mora ₲{{ number_format($a->devolucion->multa_aplicada, 0, ',', '.') }}</span>
                            @endif
                            <span class="text-xs text-ink-600">{{ $a->estado->label() }}</span>
                        </span>
                    </li>
                @empty
                    <li class="text-ink-400">Sin alquileres.</li>
                @endforelse
            </ul>
        </div>
        <div class="rounded-xl border border-ink-200 bg-white p-5 shadow-sm">
            <h3 class="mb-3 text-sm font-semibold">Últimas reservas</h3>
            <ul class="space-y-2 text-sm">
                @forelse ($cliente->reservas as $r)
                    <li class="flex justify-between"><span class="text-ink-500">{{ $r->fecha_reserva->format('d/m/Y') }}</span>
                        <span class="text-xs text-ink-600 capitalize">{{ $r->estado->value }}</span></li>
                @empty
                    <li class="text-ink-400">Sin reservas.</li>
                @endforelse
            </ul>
        </div>
    </div>
</div>
