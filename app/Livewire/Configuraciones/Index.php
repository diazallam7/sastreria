<?php

namespace App\Livewire\Configuraciones;

use App\Models\Configuracion;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Configuración')]
class Index extends Component
{
    /** @var array<int, string> id => valor */
    public array $valores = [];

    public function mount(): void
    {
        foreach (Configuracion::all() as $config) {
            $this->valores[$config->id] = (string) (int) $config->valor;
        }
    }

    public function save(): void
    {
        $this->validate([
            'valores.*' => ['required', 'integer', 'min:0'],
        ]);

        foreach ($this->valores as $id => $valor) {
            Configuracion::whereKey($id)->update(['valor' => (int) $valor]);
        }

        session()->flash('success', 'Configuración actualizada correctamente.');
    }

    public function render()
    {
        return view('livewire.configuraciones.index', [
            'configuraciones' => Configuracion::orderBy('nombre')->get(),
        ]);
    }
}
