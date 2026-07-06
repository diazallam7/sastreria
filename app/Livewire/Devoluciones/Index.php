<?php

namespace App\Livewire\Devoluciones;

use App\Models\Alquiler;
use App\Models\Configuracion;
use App\Models\TalleStock;
use App\Services\DevolucionService;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
#[Title('Devoluciones')]
class Index extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $buscar = '';

    // Modal procesar devolución
    public bool $modalProcesar = false;
    public ?int $alquilerId = null;
    public string $clienteNombre = '';
    public int $multaCalculada = 0;
    public int $garantiaOriginal = 0;
    public int $diasRetraso = 0;
    public ?string $multaAplicada = null;
    public string $motivo = '';
    public string $observaciones = '';

    public function updatingBuscar(): void
    {
        $this->resetPage();
    }

    public function abrirProcesar(Alquiler $alquiler, DevolucionService $service): void
    {
        abort_unless(auth()->user()->can('crear-devolucion'), 403);

        if (! $alquiler->estaActivo()) {
            session()->flash('error', 'Este alquiler ya fue devuelto.');
            return;
        }

        $calc = $service->calcular($alquiler);
        $this->resetValidation();
        $this->alquilerId = $alquiler->id;
        $this->clienteNombre = $alquiler->cliente?->nombre ?? '—';
        $this->multaCalculada = $calc['multa_calculada'];
        $this->garantiaOriginal = $calc['garantia_original'];
        $this->diasRetraso = $calc['dias_retraso'];
        $this->multaAplicada = (string) $calc['multa_calculada'];
        $this->motivo = '';
        $this->observaciones = '';
        $this->modalProcesar = true;
    }

    public function getMultaAplicadaIntProperty(): int
    {
        return (int) ($this->multaAplicada ?: 0);
    }

    public function getAjustadaProperty(): bool
    {
        return $this->multaAplicadaInt !== $this->multaCalculada;
    }

    public function getMontoDevolverProperty(): int
    {
        return max(0, $this->garantiaOriginal - $this->multaAplicadaInt);
    }

    public function getFaltanteProperty(): int
    {
        return max(0, $this->multaAplicadaInt - $this->garantiaOriginal);
    }

    public function procesar(DevolucionService $service)
    {
        abort_unless(auth()->user()->can('crear-devolucion'), 403);

        $this->validate([
            'multaAplicada' => ['required', 'integer', 'min:0'],
            'motivo'        => [$this->ajustada ? 'required' : 'nullable', 'string', 'max:255'],
            'observaciones' => ['nullable', 'string', 'max:1000'],
        ], [
            'motivo.required' => 'Indicá el motivo del ajuste de multa.',
        ]);

        $alquiler = Alquiler::findOrFail($this->alquilerId);

        try {
            $devolucion = $service->procesar(
                $alquiler,
                $this->multaAplicadaInt,
                $this->ajustada ? $this->motivo : null,
                $this->observaciones ?: null,
            );
        } catch (ValidationException $e) {
            $this->addError('multaAplicada', $e->validator->errors()->first());
            return;
        }

        $this->modalProcesar = false;
        session()->flash('success', 'Devolución procesada. Monto devuelto: ₲ ' . number_format($devolucion->monto_devuelto, 0, ',', '.'));
    }

    public function render()
    {
        $multaDiaria = (int) (Configuracion::where('nombre', 'multa')->value('valor') ?? 10000);

        $alquileres = Alquiler::query()
            ->with(['cliente', 'stockItems'])
            ->activos()
            ->when($this->buscar, fn ($q) => $q->whereHas('cliente', fn ($c) => $c->where('nombre', 'like', "%{$this->buscar}%")))
            ->orderBy('fecha_fin')
            ->paginate(15);

        $talleIds = $alquileres->flatMap(fn ($a) => $a->stockItems->pluck('pivot.talle_id'))->unique();
        $tallesNombres = TalleStock::whereIn('id', $talleIds)->pluck('talle', 'id');

        return view('livewire.devoluciones.index', compact('alquileres', 'tallesNombres', 'multaDiaria'));
    }
}
