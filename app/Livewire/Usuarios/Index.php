<?php

namespace App\Livewire\Usuarios;

use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
#[Title('Usuarios')]
class Index extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $buscar = '';

    public function updatingBuscar(): void
    {
        $this->resetPage();
    }

    public function eliminar(User $user): void
    {
        abort_unless(auth()->user()->can('eliminar-user'), 403);

        // Usuarios ocultos no son gestionables desde la UI.
        abort_if($user->oculto, 404);

        if ($user->id === auth()->id()) {
            session()->flash('error', 'No podés eliminar tu propio usuario.');
            return;
        }

        $user->delete();
        session()->flash('success', 'Usuario eliminado correctamente.');
    }

    public function render()
    {
        $usuarios = User::query()
            ->where('oculto', false)
            ->with('roles')
            ->when($this->buscar, fn ($q) => $q->where('name', 'like', "%{$this->buscar}%")->orWhere('email', 'like', "%{$this->buscar}%"))
            ->orderBy('name')
            ->paginate(15);

        return view('livewire.usuarios.index', compact('usuarios'));
    }
}
