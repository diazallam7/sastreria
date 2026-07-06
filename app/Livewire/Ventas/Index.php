<?php

namespace App\Livewire\Ventas;

use App\Models\Venta;
use App\Services\VentaService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
#[Title('Ventas')]
class Index extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $buscar = '';

    public function updatingBuscar(): void
    {
        $this->resetPage();
    }

    public function anular(Venta $venta, VentaService $service): void
    {
        abort_unless(auth()->user()->can('eliminar-venta'), 403);

        $service->anular($venta);
        session()->flash('success', 'Venta anulada correctamente.');
    }

    public function render()
    {
        $ventas = Venta::query()
            ->with(['cliente', 'user'])
            ->withCount('detalles')
            ->when($this->buscar, fn ($q) => $q->whereHas('cliente', fn ($c) => $c->where('nombre', 'like', "%{$this->buscar}%")))
            ->latest('fecha_venta')
            ->paginate(15);

        return view('livewire.ventas.index', compact('ventas'));
    }
}
