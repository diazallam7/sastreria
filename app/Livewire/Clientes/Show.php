<?php

namespace App\Livewire\Clientes;

use App\Models\Cliente;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Show extends Component
{
    public Cliente $cliente;

    public function mount(Cliente $cliente): void
    {
        $this->cliente = $cliente->load([
            'medidasVigentes',
            'ventas' => fn ($q) => $q->latest('fecha_venta')->limit(10),
            'alquileres' => fn ($q) => $q->latest()->limit(10)->with('devolucion'),
            'reservas' => fn ($q) => $q->latest('id')->limit(10),
        ]);
    }

    public function render()
    {
        return view('livewire.clientes.show');
    }
}
