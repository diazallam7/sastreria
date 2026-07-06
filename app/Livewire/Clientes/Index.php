<?php

namespace App\Livewire\Clientes;

use App\Models\Cliente;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
#[Title('Clientes')]
class Index extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $buscar = '';

    public function updatingBuscar(): void
    {
        $this->resetPage();
    }

    public function toggleEstado(Cliente $cliente): void
    {
        abort_unless(auth()->user()->can('editar-cliente'), 403);

        $cliente->update(['estado' => ! $cliente->estado]);
    }

    public function eliminar(Cliente $cliente): void
    {
        abort_unless(auth()->user()->can('eliminar-cliente'), 403);

        $cliente->delete();
        session()->flash('success', 'Cliente eliminado correctamente.');
    }

    public function render()
    {
        return view('livewire.clientes.index', [
            'clientes' => Cliente::buscar($this->buscar)->orderBy('nombre')->paginate(15),
        ]);
    }
}
