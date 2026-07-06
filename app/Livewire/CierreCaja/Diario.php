<?php

namespace App\Livewire\CierreCaja;

use App\Services\ReporteCajaService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Cierre de caja')]
class Diario extends Component
{
    #[Url]
    public string $fecha = '';

    public function mount(): void
    {
        if (! $this->fecha) {
            $this->fecha = now()->format('Y-m-d');
        }
    }

    public function render(ReporteCajaService $service)
    {
        return view('livewire.cierre-caja.diario', [
            'movimientos' => $service->movimientosDia($this->fecha),
        ]);
    }
}
