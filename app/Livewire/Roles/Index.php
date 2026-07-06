<?php

namespace App\Livewire\Roles;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Spatie\Permission\Models\Role;

#[Layout('components.layouts.app')]
#[Title('Roles')]
class Index extends Component
{
    public function eliminar(Role $role): void
    {
        abort_unless(auth()->user()->can('eliminar-role'), 403);

        if ($role->name === 'administrador') {
            session()->flash('error', 'No se puede eliminar el rol administrador.');
            return;
        }

        $role->delete();
        session()->flash('success', 'Rol eliminado correctamente.');
    }

    public function render()
    {
        return view('livewire.roles.index', [
            'roles' => Role::withCount('permissions')->orderBy('name')->get(),
        ]);
    }
}
