<?php

namespace App\Livewire\Gastos;

use App\Models\GastoVario;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
#[Title('Gastos varios')]
class Index extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $buscar = '';

    public function updatingBuscar(): void
    {
        $this->resetPage();
    }

    public function eliminar(GastoVario $gasto): void
    {
        $gasto->delete();
        session()->flash('success', 'Gasto eliminado correctamente.');
    }

    public function render()
    {
        $gastos = GastoVario::query()
            ->when($this->buscar, fn ($q) => $q->where('nombre_gasto', 'like', "%{$this->buscar}%"))
            ->latest('fecha')
            ->paginate(15);

        $totalMes = (int) GastoVario::whereMonth('fecha', now()->month)->whereYear('fecha', now()->year)->sum('monto');

        return view('livewire.gastos.index', compact('gastos', 'totalMes'));
    }
}
