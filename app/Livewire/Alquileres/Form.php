<?php

namespace App\Livewire\Alquileres;

use App\Enums\EstadoUnidad;
use App\Models\Alquiler;
use App\Models\Cliente;
use App\Models\StockAlquiler;
use App\Models\TalleStock;
use App\Models\UnidadStock;
use App\Services\AlquilerService;
use App\Services\BarcodeService;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Form extends Component
{
    public ?Alquiler $alquiler = null;

    public string $cliente_id = '';

    public string $fecha_inicio = '';

    public string $fecha_fin = '';

    public ?string $costo_total = null;

    public ?string $garantia = null;

    /** @var array<int, array{stock_id:int, talle_id:int, nombre:string, talle:string, cantidad:int, unidad_ids:array<int,int>}> */
    public array $prendas = [];

    public string $stockSel = '';

    public string $talleSel = '';

    public int $cantidadSel = 1;

    // Escaneo de código de barra (unidad puntual, ALQ-)
    public string $codigoEscaneado = '';

    public ?string $mensajeEscaneo = null;

    public function mount(?Alquiler $alquiler = null): void
    {
        $this->fecha_inicio = now()->format('Y-m-d');
        $this->fecha_fin = now()->addDay()->format('Y-m-d');

        if ($alquiler?->exists) {
            abort_unless(auth()->user()->can('editar-alquiler'), 403);

            if (! $alquiler->estaActivo()) {
                session()->flash('error', 'Solo se pueden editar alquileres activos.');
                $this->redirectRoute('alquileres.index', navigate: true);

                return;
            }

            $this->alquiler = $alquiler;
            $this->cliente_id = (string) $alquiler->cliente_id;
            $this->fecha_inicio = $alquiler->fecha_inicio->format('Y-m-d');
            $this->fecha_fin = $alquiler->fecha_fin->format('Y-m-d');
            $this->costo_total = (string) $alquiler->costo_total;
            $this->garantia = (string) $alquiler->garantia;

            $this->prendas = $alquiler->load('unidades.talleStock.stock')->prendasAgrupadas()
                ->map(fn ($p) => [
                    'stock_id' => $p->stock_id,
                    'talle_id' => $p->talle_id,
                    'nombre' => $p->nombre,
                    'talle' => $p->talle,
                    'precio' => $p->precio,
                    'cantidad' => $p->cantidad,
                    'unidad_ids' => [],
                ])->all();
        } else {
            abort_unless(auth()->user()->can('crear-alquiler'), 403);
        }

        if ($codigo = request()->query('scan')) {
            $this->escanear($codigo);
            // Limpia el query param para que un F5 no vuelva a agregar la misma unidad.
            $this->js('history.replaceState(null, "", window.location.pathname)');
        }
    }

    public function updatedStockSel(): void
    {
        $this->talleSel = '';
    }

    /** Selecciona el cliente recién creado desde el modal rápido. */
    #[On('cliente-creado')]
    public function seleccionarCliente(int $id): void
    {
        $this->cliente_id = (string) $id;
    }

    public function agregarPrenda(): void
    {
        $this->validate([
            'stockSel' => ['required', 'exists:stock_alquiler,id'],
            'talleSel' => ['required', 'exists:talle_stock,id'],
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
                'nombre' => $talle->stock->nombre,
                'talle' => $talle->talle,
                'precio' => (int) $talle->stock->precio_alquiler,
                'cantidad' => $this->cantidadSel,
                'unidad_ids' => [],
            ];
        }

        $this->recalcularCosto();
        $this->reset('stockSel', 'talleSel');
        $this->cantidadSel = 1;
    }

    #[On('barcode-scanned')]
    public function escanear(string $codigo): void
    {
        $this->resetErrorBag('escaneo');
        $this->codigoEscaneado = '';
        $this->mensajeEscaneo = null;

        try {
            $parsed = app(BarcodeService::class)->parsear($codigo);
        } catch (InvalidArgumentException) {
            $this->addError('escaneo', "Código no reconocido: {$codigo}");

            return;
        }

        if ($parsed['tipo'] !== 'alquiler') {
            $this->addError('escaneo', 'Ese código no corresponde a una unidad de alquiler.');

            return;
        }

        $unidad = UnidadStock::with('talleStock.stock')->find($parsed['ref_id']);

        if (! $unidad || ! $unidad->talleStock) {
            $this->addError('escaneo', 'Unidad no encontrada.');

            return;
        }

        if ($unidad->estado !== EstadoUnidad::Disponible) {
            $this->addError('escaneo', "La unidad {$unidad->codigo} no está disponible.");

            return;
        }

        $yaEscaneada = collect($this->prendas)->contains(fn ($p) => in_array($unidad->id, $p['unidad_ids'], true));

        if ($yaEscaneada) {
            $this->addError('escaneo', 'Esa unidad ya está en el carrito.');

            return;
        }

        $talle = $unidad->talleStock;
        $indice = collect($this->prendas)->search(fn ($p) => $p['talle_id'] === $talle->id);

        if ($indice !== false) {
            $this->prendas[$indice]['cantidad']++;
            $this->prendas[$indice]['unidad_ids'][] = $unidad->id;
        } else {
            $this->prendas[] = [
                'stock_id' => $talle->stock_id,
                'talle_id' => $talle->id,
                'nombre' => $talle->stock->nombre,
                'talle' => $talle->talle,
                'precio' => (int) $talle->stock->precio_alquiler,
                'cantidad' => 1,
                'unidad_ids' => [$unidad->id],
            ];
        }

        $this->recalcularCosto();
        $this->mensajeEscaneo = "Agregado: {$talle->stock->nombre} T:{$talle->talle} (unidad {$unidad->codigo})";
    }

    public function removePrenda(int $index): void
    {
        unset($this->prendas[$index]);
        $this->prendas = array_values($this->prendas);
        $this->recalcularCosto();
    }

    /** Auto-suma el costo total a partir del precio de alquiler de cada prenda. */
    private function recalcularCosto(): void
    {
        $this->costo_total = (string) collect($this->prendas)->sum(fn ($p) => $p['precio'] * $p['cantidad']);
    }

    public function save(AlquilerService $service)
    {
        $this->validate([
            'cliente_id' => ['required', 'exists:clientes,id'],
            'fecha_inicio' => ['required', 'date'],
            'fecha_fin' => ['required', 'date', 'after_or_equal:fecha_inicio'],
            'costo_total' => ['required', 'integer', 'min:0'],
            'garantia' => ['required', 'integer', 'min:0'],
            'prendas' => ['required', 'array', 'min:1'],
        ], messages: [
            'cliente_id.required' => 'Seleccioná un cliente.',
            'prendas.required' => 'Agregá al menos una prenda.',
            'prendas.min' => 'Agregá al menos una prenda.',
            'fecha_fin.after_or_equal' => 'La devolución no puede ser anterior al inicio.',
        ]);

        $datos = [
            'cliente_id' => $this->cliente_id,
            'fecha_inicio' => $this->fecha_inicio,
            'fecha_fin' => $this->fecha_fin,
            'costo_total' => $this->costo_total,
            'garantia' => $this->garantia,
        ];
        $payload = collect($this->prendas)
            ->map(fn ($p) => [
                'stock_id' => $p['stock_id'],
                'talle_id' => $p['talle_id'],
                'cantidad' => $p['cantidad'],
                'unidad_ids' => $p['unidad_ids'] ?? [],
            ])
            ->all();

        try {
            $this->alquiler
                ? $service->actualizar($this->alquiler, $datos, $payload)
                : $service->crear($datos, $payload);
        } catch (ValidationException $e) {
            $this->addError('prendas', $e->validator->errors()->first());

            return;
        }

        session()->flash('success', $this->alquiler ? 'Alquiler actualizado correctamente.' : 'Alquiler creado correctamente.');

        return $this->redirectRoute('alquileres.index', navigate: true);
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

        return view('livewire.alquileres.form', compact('clientes', 'stockItems', 'tallesDisponibles'));
    }
}
