<?php

namespace App\Livewire\Roles;

use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

#[Layout('components.layouts.app')]
class Form extends Component
{
    public ?Role $rol = null;

    public string $name = '';

    /** @var array<int, string> permission names */
    public array $permisos = [];

    public function mount(?Role $role = null): void
    {
        if ($role?->exists) {
            abort_unless(auth()->user()->can('editar-role'), 403);

            $this->rol = $role;
            $this->name = $role->name;
            $this->permisos = $role->permissions->pluck('name')->all();
        } else {
            abort_unless(auth()->user()->can('crear-role'), 403);
        }
    }

    protected function rules(): array
    {
        return [
            'name'     => ['required', 'string', 'max:255', Rule::unique('roles', 'name')->ignore($this->rol?->id)],
            'permisos' => ['required', 'array', 'min:1'],
        ];
    }

    protected function messages(): array
    {
        return ['permisos.required' => 'Seleccioná al menos un permiso.', 'permisos.min' => 'Seleccioná al menos un permiso.'];
    }

    public function save()
    {
        $this->validate();

        $role = $this->rol ?? Role::create(['name' => $this->name, 'guard_name' => 'web']);
        if ($this->rol) {
            $role->update(['name' => $this->name]);
        }

        $role->syncPermissions($this->permisos);

        session()->flash('success', $this->rol ? 'Rol actualizado correctamente.' : 'Rol creado correctamente.');

        return $this->redirectRoute('roles.index', navigate: true);
    }

    public function render()
    {
        $grupos = Permission::orderBy('name')->get()
            ->groupBy(fn ($p) => preg_replace('/^(ver|crear|editar|eliminar|exportar|mostrar)-/', '', $p->name));

        return view('livewire.roles.form', compact('grupos'));
    }
}
