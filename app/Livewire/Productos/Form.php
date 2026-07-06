<?php

namespace App\Livewire\Productos;

use App\Enums\TipoProducto;
use App\Models\Producto;
use App\Services\ProductoService;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Form extends Component
{
    public ?Producto $producto = null;

    public string $nombre = '';

    public string $tipo = 'comprado';

    public ?string $precio_venta = null;

    public ?string $precio_compra = null;

    public ?string $fecha_compra = null;

    public bool $activo_para_venta = true;

    public string $observacion = '';

    /** @var array<int, array{id:int|null, talle:string, cantidad:mixed}> */
    public array $talles = [];

    public function mount(?Producto $producto = null): void
    {
        if ($producto?->exists) {
            abort_unless(auth()->user()->can('editar-producto'), 403);

            $this->producto = $producto;
            $this->nombre = $producto->nombre;
            $this->tipo = $producto->tipo->value;
            $this->precio_venta = (string) $producto->precio_venta;
            $this->precio_compra = $producto->precio_compra !== null ? (string) $producto->precio_compra : null;
            $this->fecha_compra = $producto->fecha_compra?->format('Y-m-d');
            $this->activo_para_venta = (bool) $producto->activo_para_venta;
            $this->observacion = (string) $producto->observacion;

            $this->talles = $producto->talles
                ->map(fn ($t) => ['id' => $t->id, 'talle' => $t->talle, 'cantidad' => $t->cantidad_total, 'codigo_barra' => $t->codigo_barra])
                ->all();
        } else {
            abort_unless(auth()->user()->can('crear-producto'), 403);
        }

        if (empty($this->talles)) {
            $this->talles = [['id' => null, 'talle' => '', 'cantidad' => 1, 'codigo_barra' => '']];
        }
    }

    public function addTalle(): void
    {
        $this->talles[] = ['id' => null, 'talle' => '', 'cantidad' => 1, 'codigo_barra' => ''];
    }

    public function removeTalle(int $index): void
    {
        unset($this->talles[$index]);
        $this->talles = array_values($this->talles);
    }

    protected function rules(): array
    {
        $rules = [
            'nombre' => ['required', 'string', 'max:255'],
            'tipo' => ['required', Rule::enum(TipoProducto::class)],
            'precio_venta' => ['required', 'integer', 'min:0'],
            'precio_compra' => ['nullable', 'required_if:tipo,comprado', 'integer', 'min:0'],
            'fecha_compra' => ['nullable', 'required_if:tipo,comprado', 'date'],
            'activo_para_venta' => ['boolean'],
            'observacion' => ['nullable', 'string', 'max:1000'],
            'talles' => ['required', 'array', 'min:1'],
            'talles.*.id' => ['nullable', 'integer'],
            'talles.*.talle' => ['required', 'string', 'max:50'],
            'talles.*.cantidad' => ['required', 'integer', 'min:0'],
        ];

        foreach ($this->talles as $i => $talle) {
            $rules["talles.{$i}.codigo_barra"] = [
                'nullable', 'string', 'max:20', 'regex:/^[0-9]+$/',
                Rule::unique('producto_talles', 'codigo_barra')->ignore($talle['id'] ?? null),
            ];
        }

        return $rules;
    }

    protected function messages(): array
    {
        return [
            'nombre.required' => 'El nombre es obligatorio.',
            'precio_venta.required' => 'El precio de venta es obligatorio.',
            'precio_compra.required_if' => 'El precio de compra es obligatorio para productos comprados.',
            'fecha_compra.required_if' => 'La fecha de compra es obligatoria para productos comprados.',
            'talles.required' => 'Debe registrar al menos un talle.',
            'talles.*.talle.required' => 'El talle es obligatorio.',
            'talles.*.codigo_barra.regex' => 'El código debe contener solo números (EAN).',
            'talles.*.codigo_barra.unique' => 'Ese código ya está en uso por otro talle.',
        ];
    }

    public function save(ProductoService $service)
    {
        $this->validate();

        $service->guardar(
            $this->producto ?? new Producto,
            [
                'nombre' => $this->nombre,
                'tipo' => $this->tipo,
                'precio_venta' => $this->precio_venta,
                'precio_compra' => $this->precio_compra !== '' ? $this->precio_compra : null,
                'fecha_compra' => $this->fecha_compra ?: null,
                'activo_para_venta' => $this->activo_para_venta,
                'observacion' => $this->observacion ?: null,
            ],
            $this->talles,
        );

        session()->flash('success', $this->producto ? 'Producto actualizado correctamente.' : 'Producto creado correctamente.');

        return $this->redirectRoute('productos.index', navigate: true);
    }

    public function render()
    {
        return view('livewire.productos.form');
    }
}
