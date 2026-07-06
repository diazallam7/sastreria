<?php

namespace App\Livewire\Alquileres;

use App\Models\Alquiler;
use App\Services\DevolucionService;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Show extends Component
{
    public Alquiler $alquiler;

    public function mount(Alquiler $alquiler): void
    {
        $this->alquiler = $alquiler->load('cliente', 'unidades.talleStock.stock');
    }

    public function devolver(DevolucionService $service): void
    {
        abort_unless(auth()->user()->can('editar-alquiler'), 403);

        try {
            $service->procesar($this->alquiler);
        } catch (ValidationException $e) {
            session()->flash('error', $e->validator->errors()->first());

            return;
        }

        session()->flash('success', 'Prendas devueltas correctamente.');
        $this->redirectRoute('alquileres.index', navigate: true);
    }

    public function render()
    {
        $prendas = $this->alquiler->prendasAgrupadas();

        return view('livewire.alquileres.show', compact('prendas'));
    }
}
