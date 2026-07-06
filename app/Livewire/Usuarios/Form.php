<?php

namespace App\Livewire\Usuarios;

use App\Models\User;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Spatie\Permission\Models\Role;

#[Layout('components.layouts.app')]
class Form extends Component
{
    public ?User $usuario = null;

    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $role = '';

    public function mount(?User $user = null): void
    {
        if ($user?->exists) {
            abort_unless(auth()->user()->can('editar-user'), 403);

            // Usuarios ocultos no son editables desde la UI.
            abort_if($user->oculto, 404);

            $this->usuario = $user;
            $this->name = $user->name;
            $this->email = $user->email;
            $this->role = $user->getRoleNames()->first() ?? '';
        } else {
            abort_unless(auth()->user()->can('crear-user'), 403);
        }
    }

    protected function rules(): array
    {
        return [
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->usuario?->id)],
            'password' => [$this->usuario ? 'nullable' : 'required', 'string', 'min:8'],
            'role'     => ['required', Rule::exists('roles', 'name')],
        ];
    }

    public function save()
    {
        $this->validate();

        $user = $this->usuario ?? new User();
        $user->name = $this->name;
        $user->email = $this->email;

        // El cast 'password' => 'hashed' del modelo hashea; NO usar Hash::make (doble hash).
        if (! empty($this->password)) {
            $user->password = $this->password;
        }

        $user->save();
        $user->syncRoles([$this->role]);

        session()->flash('success', $this->usuario ? 'Usuario actualizado correctamente.' : 'Usuario creado correctamente.');

        return $this->redirectRoute('users.index', navigate: true);
    }

    public function render()
    {
        return view('livewire.usuarios.form', ['roles' => Role::orderBy('name')->get()]);
    }
}
