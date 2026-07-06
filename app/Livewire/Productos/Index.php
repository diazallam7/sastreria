<?php

namespace App\Livewire\Productos;

use App\Models\Producto;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
#[Title('Productos')]
class Index extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $buscar = '';

    #[Url(as: 'tipo')]
    public string $tipo = '';

    public function updatingBuscar(): void
    {
        $this->resetPage();
    }

    public function updatingTipo(): void
    {
        $this->resetPage();
    }

    public function toggleActivo(Producto $producto): void
    {
        abort_unless(auth()->user()->can('editar-producto'), 403);

        $producto->update(['activo_para_venta' => ! $producto->activo_para_venta]);
    }

    public function eliminar(Producto $producto): void
    {
        abort_unless(auth()->user()->can('eliminar-producto'), 403);

        $producto->delete();
        session()->flash('success', 'Producto eliminado correctamente.');
    }

    public function render()
    {
        $productos = Producto::query()
            ->withSum('talles as stock', 'cantidad_disponible')
            ->when($this->buscar, fn ($q) => $q->where('nombre', 'like', "%{$this->buscar}%"))
            ->when($this->tipo, fn ($q) => $q->where('tipo', $this->tipo))
            ->orderBy('nombre')
            ->paginate(15);

        return view('livewire.productos.index', compact('productos'));
    }
}
