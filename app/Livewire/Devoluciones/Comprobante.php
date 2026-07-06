<?php

namespace App\Livewire\Devoluciones;

use App\Models\Devolucion;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Comprobante extends Component
{
    public Devolucion $devolucion;

    public function mount(Devolucion $devolucion): void
    {
        $this->devolucion = $devolucion->load('alquiler.cliente', 'alquiler.unidades.talleStock.stock', 'user');
    }

    public function render()
    {
        $prendas = $this->devolucion->alquiler->prendasAgrupadas();

        return view('livewire.devoluciones.comprobante', compact('prendas'));
    }
}
