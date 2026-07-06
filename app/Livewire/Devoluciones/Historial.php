<?php

namespace App\Livewire\Devoluciones;

use App\Models\Devolucion;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
#[Title('Historial de devoluciones')]
class Historial extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $buscar = '';

    public function updatingBuscar(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $devoluciones = Devolucion::query()
            ->with('alquiler.cliente')
            ->when($this->buscar, fn ($q) => $q->whereHas('alquiler.cliente', fn ($c) => $c->where('nombre', 'like', "%{$this->buscar}%")))
            ->latest('fecha_devolucion')
            ->paginate(15);

        return view('livewire.devoluciones.historial', compact('devoluciones'));
    }
}
