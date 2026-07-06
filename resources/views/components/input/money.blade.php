@props(['model'])

{{--
    Input de dinero con separador de miles (100.000) en pantalla, pero guarda
    el entero crudo (100000) en la propiedad Livewire.
    Uso: <x-input.money model="costo_total" />
--}}
<div
    x-data="{
        raw: @entangle($model),
        get display() {
            return (this.raw === '' || this.raw === null || this.raw === undefined)
                ? ''
                : new Intl.NumberFormat('es-PY').format(this.raw);
        },
    }"
    class="relative"
>
    <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-sm text-ink-400">₲</span>
    <input
        type="text"
        inputmode="numeric"
        :value="display"
        @input="raw = $event.target.value.replace(/\D/g, '')"
        {{ $attributes->merge(['class' => 'w-full rounded-lg border border-ink-200 py-2 pl-7 pr-3 text-sm focus:border-ink-400 focus:outline-none focus:ring-1 focus:ring-ink-400']) }}
    />
</div>
