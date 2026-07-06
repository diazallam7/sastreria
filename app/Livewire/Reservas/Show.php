<?php

namespace App\Livewire\Reservas;

use App\Enums\EstadoReserva;
use App\Models\Reserva;
use App\Models\TalleStock;
use App\Services\ReservaService;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Show extends Component
{
    public Reserva $reserva;

    // Convertir a alquiler
    public bool $modalConvertir = false;
    public string $fechaEntrega = '';
    public string $fechaDevolucion = '';
    public string $obsEntrega = '';

    // Cancelar
    public bool $modalCancelar = false;
    public ?string $seniaDevuelta = null;
    public string $motivo = '';
    public string $obsCancelacion = '';

    public function mount(Reserva $reserva): void
    {
        $this->reserva = $reserva->load('cliente', 'user', 'stockItems', 'alquiler');
    }

    public function abrirConvertir(): void
    {
        abort_unless(auth()->user()->can('editar-reserva'), 403);
        $this->resetValidation();
        $this->fechaEntrega = $this->reserva->fecha_entrega_programada->format('Y-m-d');
        $this->fechaDevolucion = $this->reserva->fecha_devolucion_programada->format('Y-m-d');
        $this->obsEntrega = '';
        $this->modalConvertir = true;
    }

    public function convertir(ReservaService $service)
    {
        abort_unless(auth()->user()->can('editar-reserva'), 403);

        $this->validate([
            'fechaEntrega'    => ['required', 'date'],
            'fechaDevolucion' => ['required', 'date', 'after_or_equal:fechaEntrega'],
            'obsEntrega'      => ['nullable', 'string', 'max:1000'],
        ], attributes: ['fechaEntrega' => 'fecha de entrega', 'fechaDevolucion' => 'fecha de devolución']);

        try {
            $alquiler = $service->convertirAAlquiler($this->reserva, $this->fechaEntrega, $this->fechaDevolucion, $this->obsEntrega ?: null);
        } catch (ValidationException $e) {
            $this->addError('fechaEntrega', $e->validator->errors()->first());
            return;
        }

        session()->flash('success', "Reserva convertida en alquiler #{$alquiler->id}.");

        return $this->redirectRoute('reservas.index', navigate: true);
    }

    public function abrirCancelar(): void
    {
        abort_unless(auth()->user()->can('editar-reserva'), 403);
        $this->resetValidation();
        $this->seniaDevuelta = null;
        $this->motivo = '';
        $this->obsCancelacion = '';
        $this->modalCancelar = true;
    }

    public function cancelar(ReservaService $service)
    {
        abort_unless(auth()->user()->can('editar-reserva'), 403);

        $this->validate([
            'seniaDevuelta'  => ['required', 'integer', 'min:0'],
            'motivo'         => ['required', 'string', 'max:255'],
            'obsCancelacion' => ['nullable', 'string', 'max:1000'],
        ], attributes: ['seniaDevuelta' => 'seña devuelta', 'motivo' => 'motivo']);

        try {
            $service->cancelar($this->reserva, (int) $this->seniaDevuelta, $this->motivo, $this->obsCancelacion ?: null);
        } catch (ValidationException $e) {
            $this->addError('seniaDevuelta', $e->validator->errors()->first());
            return;
        }

        session()->flash('success', 'Reserva cancelada correctamente.');

        return $this->redirectRoute('reservas.index', navigate: true);
    }

    public function render()
    {
        $tallesNombres = TalleStock::whereIn('id', $this->reserva->stockItems->pluck('pivot.talle_id'))
            ->pluck('talle', 'id');

        return view('livewire.reservas.show', [
            'tallesNombres' => $tallesNombres,
            'puedeConvertir' => $this->reserva->estado === EstadoReserva::Confirmada,
            'puedeCancelar' => ! in_array($this->reserva->estado, [EstadoReserva::Entregada, EstadoReserva::Cancelada], true),
        ]);
    }
}
