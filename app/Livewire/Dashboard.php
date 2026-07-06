<?php

namespace App\Livewire;

use App\Models\Cliente;
use App\Models\Producto;
use App\Models\Venta;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Panel')]
class Dashboard extends Component
{
    public function render()
    {
        return view('livewire.dashboard', [
            'clientes'  => Cliente::count(),
            'productos' => Producto::count(),
            'ventasHoy' => (int) Venta::whereDate('fecha_venta', today())->sum('precio_total'),
            'ventasMes' => (int) Venta::whereBetween('fecha_venta', [now()->startOfMonth(), now()->endOfMonth()])->sum('precio_total'),
        ]);
    }
}
