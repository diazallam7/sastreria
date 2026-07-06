<?php

namespace App\Livewire\Clientes;

use App\Models\Cliente;
use App\Services\ClienteService;
use Livewire\Attributes\On;
use Livewire\Component;

class CrearRapido extends Component
{
    public bool $abierto = false;

    public string $nombre = '';
    public string $documento = '';
    public string $telefono = '';
    public string $correo = '';

    #[On('abrir-crear-cliente')]
    public function abrir(): void
    {
        abort_unless(auth()->user()->can('crear-cliente'), 403);

        $this->reset('nombre', 'documento', 'telefono', 'correo');
        $this->resetValidation();
        $this->abierto = true;
    }

    public function cerrar(): void
    {
        $this->abierto = false;
    }

    public function guardar(ClienteService $service): void
    {
        abort_unless(auth()->user()->can('crear-cliente'), 403);

        $this->validate([
            'nombre'    => ['required', 'string', 'max:255'],
            'documento' => ['nullable', 'string', 'max:20', 'unique:clientes,documento'],
            'telefono'  => ['nullable', 'string', 'max:20'],
            'correo'    => ['nullable', 'email', 'max:255', 'unique:clientes,correo'],
        ]);

        $cliente = $service->guardar(new Cliente(), [
            'nombre'    => $this->nombre,
            'documento' => $this->documento ?: null,
            'telefono'  => $this->telefono ?: null,
            'correo'    => $this->correo ?: null,
        ], [], null);

        // El form padre escucha este evento para seleccionar el nuevo cliente.
        $this->dispatch('cliente-creado', id: $cliente->id);

        $this->abierto = false;
        $this->reset('nombre', 'documento', 'telefono', 'correo');
    }

    public function render()
    {
        return view('livewire.clientes.crear-rapido');
    }
}
