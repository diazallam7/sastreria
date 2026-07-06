<?php

namespace App\Livewire\Perfil;

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Mi perfil')]
class Index extends Component
{
    public string $name = '';
    public string $email = '';
    public string $password = '';

    public function mount(): void
    {
        $user = Auth::user();
        $this->name = $user->name;
        $this->email = $user->email;
    }

    public function save()
    {
        $user = Auth::user();

        $this->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8'],
        ]);

        $user->name = $this->name;
        $user->email = $this->email;

        // El cast 'password' => 'hashed' hashea; NO usar Hash::make (doble hash).
        if (! empty($this->password)) {
            $user->password = $this->password;
        }

        $user->save();

        $this->password = '';
        session()->flash('success', 'Cambios guardados.');
    }

    public function render()
    {
        return view('livewire.perfil.index');
    }
}
