<?php

namespace App\Livewire\Ventas;

use App\Models\Venta;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Show extends Component
{
    public Venta $venta;

    public function mount(Venta $venta): void
    {
        $this->venta = $venta->load(['cliente', 'user', 'detalles']);
    }

    public function render()
    {
        return view('livewire.ventas.show');
    }
}
