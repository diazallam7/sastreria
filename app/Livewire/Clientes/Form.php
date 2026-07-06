<?php

namespace App\Livewire\Clientes;

use App\Enums\TipoMedida;
use App\Models\Cliente;
use App\Services\ClienteService;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Form extends Component
{
    public ?Cliente $cliente = null;

    public string $nombre = '';
    public string $documento = '';
    public string $telefono = '';
    public string $correo = '';
    public string $direccion = '';

    /** @var array<string, array<string, mixed>> */
    public array $medidas = ['saco' => [], 'pantalon' => [], 'chaleco' => []];
    public string $observaciones_medidas = '';

    public function mount(?Cliente $cliente = null): void
    {
        if ($cliente?->exists) {
            abort_unless(auth()->user()->can('editar-cliente'), 403);

            $this->cliente = $cliente;
            $this->nombre = $cliente->nombre;
            $this->documento = (string) $cliente->documento;
            $this->telefono = (string) $cliente->telefono;
            $this->correo = (string) $cliente->correo;
            $this->direccion = (string) $cliente->direccion;

            foreach ($cliente->medidasVigentes as $medida) {
                $this->medidas[$medida->tipo->value] = $medida->medidas;
                if ($medida->observaciones) {
                    $this->observaciones_medidas = $medida->observaciones;
                }
            }
        } else {
            abort_unless(auth()->user()->can('crear-cliente'), 403);
        }
    }

    protected function rules(): array
    {
        $id = $this->cliente?->id;

        return array_merge([
            'nombre'    => ['required', 'string', 'max:255'],
            'documento' => ['nullable', 'string', 'max:20', Rule::unique('clientes', 'documento')->ignore($id)],
            'telefono'  => ['nullable', 'string', 'max:20'],
            'correo'    => ['nullable', 'email', 'max:255', Rule::unique('clientes', 'correo')->ignore($id)],
            'direccion' => ['nullable', 'string', 'max:255'],
        ], ClienteService::reglasMedidas());
    }

    protected function messages(): array
    {
        return [
            'nombre.required'  => 'El nombre es obligatorio.',
            'documento.unique' => 'Ya existe un cliente con ese documento.',
            'correo.unique'    => 'Ya existe un cliente con ese correo.',
            'correo.email'     => 'El correo no es válido.',
        ];
    }

    public function save(ClienteService $service)
    {
        $this->validate();

        $service->guardar(
            $this->cliente ?? new Cliente(),
            [
                'nombre'    => $this->nombre,
                'documento' => $this->documento ?: null,
                'telefono'  => $this->telefono ?: null,
                'correo'    => $this->correo ?: null,
                'direccion' => $this->direccion ?: null,
            ],
            $this->medidas,
            $this->observaciones_medidas ?: null,
        );

        session()->flash('success', $this->cliente ? 'Cliente actualizado correctamente.' : 'Cliente creado correctamente.');

        return $this->redirectRoute('clientes.index', navigate: true);
    }

    public function render()
    {
        return view('livewire.clientes.form', ['tipos' => TipoMedida::cases()]);
    }
}
