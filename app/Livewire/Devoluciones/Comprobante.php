<?php

namespace App\Livewire\Devoluciones;

use App\Models\Devolucion;
use App\Models\TalleStock;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Comprobante extends Component
{
    public Devolucion $devolucion;

    public function mount(Devolucion $devolucion): void
    {
        $this->devolucion = $devolucion->load('alquiler.cliente', 'alquiler.stockItems', 'user');
    }

    public function render()
    {
        $tallesNombres = TalleStock::whereIn('id', $this->devolucion->alquiler->stockItems->pluck('pivot.talle_id'))
            ->pluck('talle', 'id');

        return view('livewire.devoluciones.comprobante', compact('tallesNombres'));
    }
}
