<?php

namespace App\Livewire\Reservas;

use App\Enums\EstadoReserva;
use App\Models\Cliente;
use App\Models\Reserva;
use App\Models\StockAlquiler;
use App\Models\TalleStock;
use App\Services\ReservaService;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Form extends Component
{
    public ?Reserva $reserva = null;

    public string $cliente_id = '';
    public string $fecha_reserva = '';
    public string $fecha_entrega_programada = '';
    public string $fecha_devolucion_programada = '';
    public ?string $monto_total = null;
    public ?string $garantia_total = null;
    public ?string $senia_garantia = null;
    public ?string $senia_alquiler = null;
    public string $observaciones = '';

    /** @var array<int, array{stock_id:int, talle_id:int, nombre:string, talle:string, precio:int, cantidad:int}> */
    public array $prendas = [];

    public string $stockSel = '';
    public string $talleSel = '';
    public int $cantidadSel = 1;

    public function mount(?Reserva $reserva = null): void
    {
        $this->fecha_reserva = now()->format('Y-m-d');
        $this->fecha_entrega_programada = now()->addDay()->format('Y-m-d');
        $this->fecha_devolucion_programada = now()->addDays(2)->format('Y-m-d');

        if ($reserva?->exists) {
            abort_unless(auth()->user()->can('editar-reserva'), 403);

            if (! in_array($reserva->estado, [EstadoReserva::Pendiente, EstadoReserva::Confirmada], true)) {
                session()->flash('error', 'Solo se pueden editar reservas pendientes o confirmadas.');
                $this->redirectRoute('reservas.index', navigate: true);
                return;
            }

            $this->reserva = $reserva;
            $this->cliente_id = (string) $reserva->cliente_id;
            $this->fecha_reserva = $reserva->fecha_reserva->format('Y-m-d');
            $this->fecha_entrega_programada = $reserva->fecha_entrega_programada->format('Y-m-d');
            $this->fecha_devolucion_programada = $reserva->fecha_devolucion_programada->format('Y-m-d');
            $this->monto_total = (string) $reserva->monto_total;
            $this->garantia_total = (string) $reserva->garantia_total;
            $this->senia_garantia = (string) $reserva->senia_garantia;
            $this->senia_alquiler = (string) $reserva->senia_alquiler;
            $this->observaciones = (string) $reserva->observaciones;

            $this->prendas = $reserva->stockItems()->withPivot('talle_id', 'cantidad')->get()
                ->map(fn ($s) => [
                    'stock_id' => $s->id,
                    'talle_id' => $s->pivot->talle_id,
                    'nombre'   => $s->nombre,
                    'talle'    => TalleStock::find($s->pivot->talle_id)?->talle ?? '',
                    'precio'   => (int) $s->precio_alquiler,
                    'cantidad' => $s->pivot->cantidad,
                ])->all();
        } else {
            abort_unless(auth()->user()->can('crear-reserva'), 403);
        }
    }

    public function updatedStockSel(): void
    {
        $this->talleSel = '';
    }

    #[On('cliente-creado')]
    public function seleccionarCliente(int $id): void
    {
        $this->cliente_id = (string) $id;
    }

    public function agregarPrenda(): void
    {
        $this->validate([
            'stockSel'    => ['required', 'exists:stock_alquiler,id'],
            'talleSel'    => ['required', 'exists:talle_stock,id'],
            'cantidadSel' => ['required', 'integer', 'min:1'],
        ], attributes: ['stockSel' => 'prenda', 'talleSel' => 'talle', 'cantidadSel' => 'cantidad']);

        $talle = TalleStock::with('stock')->find($this->talleSel);

        if ((int) $talle->stock_id !== (int) $this->stockSel) {
            $this->addError('talleSel', 'El talle no corresponde a la prenda.');
            return;
        }

        $enCarrito = collect($this->prendas)->firstWhere('talle_id', $talle->id)['cantidad'] ?? 0;

        if ($talle->cantidad_disponible < $enCarrito + $this->cantidadSel) {
            $this->addError('cantidadSel', "Stock insuficiente. Disponible: {$talle->cantidad_disponible}.");
            return;
        }

        $indice = collect($this->prendas)->search(fn ($p) => $p['talle_id'] === $talle->id);

        if ($indice !== false) {
            $this->prendas[$indice]['cantidad'] += $this->cantidadSel;
        } else {
            $this->prendas[] = [
                'stock_id' => $talle->stock_id,
                'talle_id' => $talle->id,
                'nombre'   => $talle->stock->nombre,
                'talle'    => $talle->talle,
                'precio'   => (int) $talle->stock->precio_alquiler,
                'cantidad' => $this->cantidadSel,
            ];
        }

        $this->recalcularMonto();
        $this->reset('stockSel', 'talleSel');
        $this->cantidadSel = 1;
    }

    public function removePrenda(int $index): void
    {
        unset($this->prendas[$index]);
        $this->prendas = array_values($this->prendas);
        $this->recalcularMonto();
    }

    private function recalcularMonto(): void
    {
        $this->monto_total = (string) collect($this->prendas)->sum(fn ($p) => $p['precio'] * $p['cantidad']);
    }

    public function save(ReservaService $service)
    {
        $this->validate([
            'cliente_id'                  => ['required', 'exists:clientes,id'],
            'fecha_reserva'               => ['required', 'date'],
            'fecha_entrega_programada'    => ['required', 'date', 'after_or_equal:fecha_reserva'],
            'fecha_devolucion_programada' => ['required', 'date', 'after_or_equal:fecha_entrega_programada'],
            'monto_total'                 => ['required', 'integer', 'min:0'],
            'garantia_total'              => ['required', 'integer', 'min:0'],
            'senia_garantia'              => ['required', 'integer', 'min:0', 'lte:garantia_total'],
            'senia_alquiler'              => ['nullable', 'integer', 'min:0', 'lte:monto_total'],
            'observaciones'               => ['nullable', 'string', 'max:1000'],
            'prendas'                     => ['required', 'array', 'min:1'],
        ], messages: [
            'cliente_id.required' => 'Seleccioná un cliente.',
            'prendas.required'    => 'Agregá al menos una prenda.',
            'prendas.min'         => 'Agregá al menos una prenda.',
            'senia_garantia.lte'  => 'La seña de garantía no puede superar la garantía total.',
            'senia_alquiler.lte'  => 'La seña de alquiler no puede superar el monto total.',
        ]);

        $datos = [
            'cliente_id'                  => $this->cliente_id,
            'fecha_reserva'               => $this->fecha_reserva,
            'fecha_entrega_programada'    => $this->fecha_entrega_programada,
            'fecha_devolucion_programada' => $this->fecha_devolucion_programada,
            'monto_total'                 => $this->monto_total,
            'garantia_total'              => $this->garantia_total,
            'senia_garantia'              => $this->senia_garantia,
            'senia_alquiler'              => $this->senia_alquiler ?: 0,
            'observaciones'               => $this->observaciones ?: null,
        ];
        $payload = collect($this->prendas)
            ->map(fn ($p) => ['stock_id' => $p['stock_id'], 'talle_id' => $p['talle_id'], 'cantidad' => $p['cantidad']])
            ->all();

        try {
            $this->reserva
                ? $service->actualizar($this->reserva, $datos, $payload)
                : $service->crear($datos, $payload);
        } catch (ValidationException $e) {
            $this->addError('prendas', $e->validator->errors()->first());
            return;
        }

        session()->flash('success', $this->reserva ? 'Reserva actualizada correctamente.' : 'Reserva creada correctamente.');

        return $this->redirectRoute('reservas.index', navigate: true);
    }

    public function render()
    {
        $clientes = Cliente::activos()->orderBy('nombre')->get();
        $stockItems = StockAlquiler::disponibles()
            ->with(['talles' => fn ($q) => $q->where('cantidad_disponible', '>', 0)])
            ->orderBy('nombre')->get();

        $tallesDisponibles = $this->stockSel
            ? ($stockItems->firstWhere('id', (int) $this->stockSel)?->talles ?? collect())
            : collect();

        return view('livewire.reservas.form', compact('clientes', 'stockItems', 'tallesDisponibles'));
    }
}
