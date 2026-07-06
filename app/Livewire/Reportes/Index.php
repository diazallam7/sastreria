<?php

namespace App\Livewire\Reportes;

use App\Models\Alquiler;
use App\Models\Venta;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Reportes')]
class Index extends Component
{
    #[Url]
    public string $tipo = 'ventas'; // ventas | alquileres

    #[Url]
    public string $intervalo = 'mensual'; // mensual | anual

    public function render()
    {
        $formato = $this->intervalo === 'anual' ? '%Y' : '%Y-%m';

        if ($this->tipo === 'alquileres') {
            // Excluye cancelados; soft-deletes ya se excluyen por el modelo.
            $filas = Alquiler::query()
                ->where('estado', '!=', 'cancelado')
                ->selectRaw("DATE_FORMAT(fecha_inicio, ?) as periodo, COUNT(*) as cantidad, SUM(costo_total) as total", [$formato])
                ->groupBy('periodo')
                ->orderByDesc('periodo')
                ->get();
        } else {
            $filas = Venta::query()
                ->selectRaw("DATE_FORMAT(fecha_venta, ?) as periodo, COUNT(*) as cantidad, SUM(precio_total) as total", [$formato])
                ->groupBy('periodo')
                ->orderByDesc('periodo')
                ->get();
        }

        return view('livewire.reportes.index', compact('filas'));
    }
}
