<?php

namespace App\Livewire\StockAlquiler;

use App\Models\StockAlquiler;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Show extends Component
{
    public StockAlquiler $item;

    public function mount(StockAlquiler $item): void
    {
        $this->item = $item->load('talles');
    }

    public function render()
    {
        return view('livewire.stock-alquiler.show');
    }
}
