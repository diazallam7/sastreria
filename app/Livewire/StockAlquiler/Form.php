<?php

namespace App\Livewire\StockAlquiler;

use App\Models\StockAlquiler;
use App\Services\StockAlquilerService;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Form extends Component
{
    public ?StockAlquiler $item = null;

    public string $codigo = '';

    public string $nombre = '';

    public ?string $precio_alquiler = null;

    public string $descripcion = '';

    /** @var array<int, array{id:int|null, talle:string, cantidad:mixed}> */
    public array $talles = [];

    public function mount(?StockAlquiler $item = null): void
    {
        if ($item?->exists) {
            abort_unless(auth()->user()->can('editar-stock-alquiler'), 403);

            $this->item = $item;
            $this->codigo = $item->codigo;
            $this->nombre = $item->nombre;
            $this->precio_alquiler = (string) $item->precio_alquiler;
            $this->descripcion = (string) $item->descripcion;

            $this->talles = $item->talles
                ->map(fn ($t) => ['id' => $t->id, 'talle' => $t->talle, 'cantidad' => $t->cantidad_total])
                ->all();
        } else {
            abort_unless(auth()->user()->can('crear-stock-alquiler'), 403);
        }

        if (empty($this->talles)) {
            $this->talles = [['id' => null, 'talle' => '', 'cantidad' => 1]];
        }
    }

    public function addTalle(): void
    {
        $this->talles[] = ['id' => null, 'talle' => '', 'cantidad' => 1];
    }

    public function removeTalle(int $index): void
    {
        unset($this->talles[$index]);
        $this->talles = array_values($this->talles);
    }

    protected function rules(): array
    {
        return [
            'codigo' => ['nullable', 'string', 'max:50', Rule::unique('stock_alquiler', 'codigo')->ignore($this->item?->id)],
            'nombre' => ['required', 'string', 'max:255'],
            'precio_alquiler' => ['required', 'integer', 'min:0'],
            'descripcion' => ['nullable', 'string', 'max:1000'],
            'talles' => ['required', 'array', 'min:1'],
            'talles.*.id' => ['nullable', 'integer'],
            'talles.*.talle' => ['required', 'string', 'max:50'],
            'talles.*.cantidad' => ['required', 'integer', 'min:0'],
        ];
    }

    protected function messages(): array
    {
        return [
            'codigo.unique' => 'Ya existe una prenda con ese código.',
            'nombre.required' => 'El nombre es obligatorio.',
            'talles.required' => 'Debe registrar al menos un talle.',
            'talles.*.talle.required' => 'El talle es obligatorio.',
        ];
    }

    public function save(StockAlquilerService $service)
    {
        $this->validate();

        $service->guardar(
            $this->item ?? new StockAlquiler,
            [
                'codigo' => $this->codigo !== '' ? $this->codigo : null,
                'nombre' => $this->nombre,
                'precio_alquiler' => $this->precio_alquiler,
                'descripcion' => $this->descripcion ?: null,
            ],
            $this->talles,
        );

        session()->flash('success', $this->item ? 'Prenda actualizada correctamente.' : 'Prenda agregada al stock.');

        return $this->redirectRoute('stock.alquiler.index', navigate: true);
    }

    public function render()
    {
        return view('livewire.stock-alquiler.form');
    }
}
