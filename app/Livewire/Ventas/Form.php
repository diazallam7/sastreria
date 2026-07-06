<?php

namespace App\Livewire\Ventas;

use App\Models\Cliente;
use App\Models\Producto;
use App\Models\ProductoTalle;
use App\Models\Venta;
use App\Services\VentaService;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Form extends Component
{
    public ?Venta $venta = null;

    public string $cliente_id = '';
    public string $fecha_venta = '';

    /** @var array<int, array{producto_talle_id:int, nombre:string, talle:string, precio:int, cantidad:int}> */
    public array $items = [];

    // Selector de producto
    public string $productoSel = '';
    public string $talleSel = '';
    public int $cantidadSel = 1;

    public function mount(?Venta $venta = null): void
    {
        $this->fecha_venta = now()->format('Y-m-d');

        if ($venta?->exists) {
            abort_unless(auth()->user()->can('editar-venta'), 403);

            $this->venta = $venta->load('detalles');
            $this->cliente_id = (string) $venta->cliente_id;
            $this->fecha_venta = $venta->fecha_venta->format('Y-m-d');
            $this->items = $venta->detalles
                ->filter(fn ($d) => $d->producto_talle_id !== null)
                ->map(fn ($d) => [
                    'producto_talle_id' => $d->producto_talle_id,
                    'nombre'            => $d->nombre_producto,
                    'talle'             => $d->talle,
                    'precio'            => (int) $d->precio_unitario,
                    'cantidad'          => $d->cantidad,
                ])->values()->all();
        } else {
            abort_unless(auth()->user()->can('crear-venta'), 403);
        }
    }

    public function updatedProductoSel(): void
    {
        $this->talleSel = '';
    }

    /** Selecciona el cliente recién creado desde el modal rápido. */
    #[On('cliente-creado')]
    public function seleccionarCliente(int $id): void
    {
        $this->cliente_id = (string) $id;
    }

    public function agregarItem(): void
    {
        $this->validate([
            'productoSel' => ['required', 'exists:productos,id'],
            'talleSel'    => ['required', 'exists:producto_talles,id'],
            'cantidadSel' => ['required', 'integer', 'min:1'],
        ], attributes: ['productoSel' => 'producto', 'talleSel' => 'talle', 'cantidadSel' => 'cantidad']);

        $talle = ProductoTalle::with('producto')->find($this->talleSel);

        if ((int) $talle->producto_id !== (int) $this->productoSel) {
            $this->addError('talleSel', 'El talle no corresponde al producto.');
            return;
        }

        $enCarrito = collect($this->items)->firstWhere('producto_talle_id', $talle->id)['cantidad'] ?? 0;

        if ($talle->cantidad_disponible < $enCarrito + $this->cantidadSel) {
            $this->addError('cantidadSel', "Stock insuficiente. Disponible: {$talle->cantidad_disponible}.");
            return;
        }

        $indice = collect($this->items)->search(fn ($i) => $i['producto_talle_id'] === $talle->id);

        if ($indice !== false) {
            $this->items[$indice]['cantidad'] += $this->cantidadSel;
        } else {
            $this->items[] = [
                'producto_talle_id' => $talle->id,
                'nombre'            => $talle->producto->nombre,
                'talle'             => $talle->talle,
                'precio'            => (int) $talle->producto->precio_venta,
                'cantidad'          => $this->cantidadSel,
            ];
        }

        $this->reset('productoSel', 'talleSel');
        $this->cantidadSel = 1;
    }

    public function removeItem(int $index): void
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    public function getTotalProperty(): int
    {
        return collect($this->items)->sum(fn ($i) => $i['precio'] * $i['cantidad']);
    }

    public function save(VentaService $service)
    {
        $this->validate([
            'cliente_id'  => ['required', 'exists:clientes,id'],
            'fecha_venta' => ['required', 'date'],
            'items'       => ['required', 'array', 'min:1'],
        ], messages: [
            'items.required' => 'Agregá al menos un producto.',
            'items.min'      => 'Agregá al menos un producto.',
            'cliente_id.required' => 'Seleccioná un cliente.',
        ]);

        $datos = ['cliente_id' => $this->cliente_id, 'fecha_venta' => $this->fecha_venta];
        $payload = collect($this->items)
            ->map(fn ($i) => ['producto_talle_id' => $i['producto_talle_id'], 'cantidad' => $i['cantidad']])
            ->all();

        try {
            $venta = $this->venta
                ? $service->actualizar($this->venta, $datos, $payload)
                : $service->crear($datos, $payload);
        } catch (ValidationException $e) {
            $this->addError('items', $e->validator->errors()->first());
            return;
        }

        session()->flash('success', $service->imprimirTicket($venta));

        return $this->redirectRoute('ventas.index', navigate: true);
    }

    public function render()
    {
        $clientes = Cliente::activos()->orderBy('nombre')->get();
        $productos = Producto::vendibles()
            ->with(['talles' => fn ($q) => $q->where('cantidad_disponible', '>', 0)])
            ->orderBy('nombre')->get();

        $tallesDisponibles = $this->productoSel
            ? ($productos->firstWhere('id', (int) $this->productoSel)?->talles ?? collect())
            : collect();

        return view('livewire.ventas.form', compact('clientes', 'productos', 'tallesDisponibles'));
    }
}
