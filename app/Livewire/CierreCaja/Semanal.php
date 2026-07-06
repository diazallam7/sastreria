<?php

namespace App\Livewire\CierreCaja;

use App\Services\ReporteCajaService;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Cierre semanal')]
class Semanal extends Component
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
        return view('livewire.cierre-caja.semanal', $service->resumenSemanal(Carbon::parse($this->fecha)));
    }
}
