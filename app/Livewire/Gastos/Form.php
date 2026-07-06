<?php

namespace App\Livewire\Gastos;

use App\Models\GastoVario;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Form extends Component
{
    public ?GastoVario $gasto = null;

    public string $nombre_gasto = '';
    public string $fecha = '';
    public ?string $monto = null;
    public string $observacion = '';

    public function mount(?GastoVario $gasto = null): void
    {
        $this->fecha = now()->format('Y-m-d');

        if ($gasto?->exists) {
            $this->gasto = $gasto;
            $this->nombre_gasto = $gasto->nombre_gasto;
            $this->fecha = $gasto->fecha->format('Y-m-d');
            $this->monto = (string) (int) $gasto->monto;
            $this->observacion = (string) $gasto->observacion;
        }
    }

    protected function rules(): array
    {
        return [
            'nombre_gasto' => ['required', 'string', 'max:255'],
            'fecha'        => ['required', 'date'],
            'monto'        => ['required', 'integer', 'min:0'],
            'observacion'  => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function save()
    {
        $this->validate();

        $datos = [
            'nombre_gasto' => $this->nombre_gasto,
            'fecha'        => $this->fecha,
            'monto'        => $this->monto,
            'observacion'  => $this->observacion ?: null,
        ];

        $this->gasto ? $this->gasto->update($datos) : GastoVario::create($datos);

        session()->flash('success', $this->gasto ? 'Gasto actualizado correctamente.' : 'Gasto registrado correctamente.');

        return $this->redirectRoute('gastos-varios.index', navigate: true);
    }

    public function render()
    {
        return view('livewire.gastos.form');
    }
}
