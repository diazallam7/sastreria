<?php

namespace App\Livewire\Reservas;

use App\Enums\EstadoReserva;
use App\Models\Reserva;
use App\Models\TalleStock;
use App\Services\ReservaService;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
#[Title('Reservas')]
class Index extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $buscar = '';

    // Convertir a alquiler
    public bool $modalConvertir = false;
    public ?int $reservaConvertirId = null;
    public string $fechaEntrega = '';
    public string $fechaDevolucion = '';
    public string $obsEntrega = '';

    public function updatingBuscar(): void
    {
        $this->resetPage();
    }

    public function abrirConvertir(Reserva $reserva): void
    {
        abort_unless(auth()->user()->can('editar-reserva'), 403);

        if ($reserva->estado !== EstadoReserva::Confirmada) {
            session()->flash('error', 'Solo se pueden convertir reservas confirmadas.');
            return;
        }

        $this->resetValidation();
        $this->reservaConvertirId = $reserva->id;
        $this->fechaEntrega = $reserva->fecha_entrega_programada->format('Y-m-d');
        $this->fechaDevolucion = $reserva->fecha_devolucion_programada->format('Y-m-d');
        $this->obsEntrega = '';
        $this->modalConvertir = true;
    }

    public function convertir(ReservaService $service): void
    {
        abort_unless(auth()->user()->can('editar-reserva'), 403);

        $this->validate([
            'fechaEntrega'    => ['required', 'date'],
            'fechaDevolucion' => ['required', 'date', 'after_or_equal:fechaEntrega'],
            'obsEntrega'      => ['nullable', 'string', 'max:1000'],
        ], attributes: ['fechaEntrega' => 'fecha de entrega', 'fechaDevolucion' => 'fecha de devolución']);

        $reserva = Reserva::findOrFail($this->reservaConvertirId);

        try {
            $alquiler = $service->convertirAAlquiler($reserva, $this->fechaEntrega, $this->fechaDevolucion, $this->obsEntrega ?: null);
        } catch (ValidationException $e) {
            $this->addError('fechaEntrega', $e->validator->errors()->first());
            return;
        }

        $this->modalConvertir = false;
        session()->flash('success', "Reserva convertida en alquiler #{$alquiler->id}.");
    }

    public function eliminar(Reserva $reserva, ReservaService $service): void
    {
        abort_unless(auth()->user()->can('eliminar-reserva'), 403);

        try {
            $service->anular($reserva);
        } catch (ValidationException $e) {
            session()->flash('error', $e->validator->errors()->first());
            return;
        }

        session()->flash('success', 'Reserva eliminada correctamente.');
    }

    public function render()
    {
        $reservas = Reserva::query()
            ->with(['cliente', 'stockItems'])
            ->when($this->buscar, fn ($q) => $q->whereHas('cliente', fn ($c) => $c->where('nombre', 'like', "%{$this->buscar}%")))
            ->latest('id')
            ->paginate(15);

        $talleIds = $reservas->flatMap(fn ($r) => $r->stockItems->pluck('pivot.talle_id'))->unique();
        $tallesNombres = TalleStock::whereIn('id', $talleIds)->pluck('talle', 'id');

        return view('livewire.reservas.index', compact('reservas', 'tallesNombres'));
    }
}
