@props(['activo'])

@php
    $base = 'border-b-2 px-4 py-2';
    $on = 'border-ink-900 font-medium text-ink-900';
    $off = 'border-transparent text-ink-500 hover:text-ink-800';
@endphp

<div class="mb-6 flex gap-1 border-b border-ink-200 text-sm">
    <a href="{{ route('cierre-caja.index') }}" wire:navigate class="{{ $base }} {{ $activo === 'diario' ? $on : $off }}">Diario</a>
    @can('ver-cierre-caja-semanal')
        <a href="{{ route('cierre-caja.semanal') }}" wire:navigate class="{{ $base }} {{ $activo === 'semanal' ? $on : $off }}">Semanal</a>
    @endcan
    @can('ver-cierre-caja-mensual')
        <a href="{{ route('cierre-caja.mensual') }}" wire:navigate class="{{ $base }} {{ $activo === 'mensual' ? $on : $off }}">Mensual</a>
    @endcan
</div>
