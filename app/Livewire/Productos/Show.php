<?php

namespace App\Livewire\Productos;

use App\Models\Producto;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Show extends Component
{
    public Producto $producto;

    public function mount(Producto $producto): void
    {
        $this->producto = $producto->load('talles');
    }

    public function render()
    {
        return view('livewire.productos.show');
    }
}
