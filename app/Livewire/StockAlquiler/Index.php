<?php

namespace App\Livewire\StockAlquiler;

use App\Enums\EstadoUnidad;
use App\Models\StockAlquiler;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
#[Title('Stock de alquiler')]
class Index extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $buscar = '';

    public function updatingBuscar(): void
    {
        $this->resetPage();
    }

    public function eliminar(StockAlquiler $item): void
    {
        abort_unless(auth()->user()->can('eliminar-stock-alquiler'), 403);

        if ($item->talles()->whereHas('unidades', fn ($q) => $q->where('estado', EstadoUnidad::Alquilada->value))->exists()) {
            session()->flash('error', 'No se puede eliminar: la prenda está en un alquiler activo.');

            return;
        }

        $item->delete();
        session()->flash('success', 'Prenda eliminada correctamente.');
    }

    public function render()
    {
        $items = StockAlquiler::query()
            ->withSum('talles as disponible', 'cantidad_disponible')
            ->withSum('talles as alquilado', 'cantidad_alquilada')
            ->when($this->buscar, fn ($q) => $q->where(fn ($s) => $s->where('nombre', 'like', "%{$this->buscar}%")->orWhere('codigo', 'like', "%{$this->buscar}%")))
            ->orderBy('nombre')
            ->paginate(15);

        return view('livewire.stock-alquiler.index', compact('items'));
    }
}
