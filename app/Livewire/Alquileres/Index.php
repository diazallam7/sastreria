<?php

namespace App\Livewire\Alquileres;

use App\Models\Alquiler;
use App\Services\AlquilerService;
use App\Services\DevolucionService;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
#[Title('Alquileres')]
class Index extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $buscar = '';

    public function updatingBuscar(): void
    {
        $this->resetPage();
    }

    public function devolver(Alquiler $alquiler, DevolucionService $service): void
    {
        abort_unless(auth()->user()->can('editar-alquiler'), 403);

        try {
            $service->procesar($alquiler);
        } catch (ValidationException $e) {
            session()->flash('error', $e->validator->errors()->first());

            return;
        }

        session()->flash('success', 'Prendas devueltas correctamente.');
    }

    public function anular(Alquiler $alquiler, AlquilerService $service): void
    {
        abort_unless(auth()->user()->can('eliminar-alquiler'), 403);

        $service->anular($alquiler);
        session()->flash('success', 'Alquiler eliminado correctamente.');
    }

    public function render()
    {
        $alquileres = Alquiler::query()
            ->with(['cliente', 'unidades.talleStock.stock'])
            ->when($this->buscar, fn ($q) => $q->whereHas('cliente', fn ($c) => $c->where('nombre', 'like', "%{$this->buscar}%")))
            ->latest()
            ->paginate(15);

        return view('livewire.alquileres.index', compact('alquileres'));
    }
}
