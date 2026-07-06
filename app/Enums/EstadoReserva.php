<?php

namespace App\Enums;

enum EstadoReserva: string
{
    case Pendiente  = 'pendiente';
    case Confirmada = 'confirmada'; // seña pagada, stock reservado
    case Entregada  = 'entregada';  // convertida en alquiler
    case Cancelada  = 'cancelada';  // anulada, stock liberado

    public function label(): string
    {
        return match ($this) {
            self::Pendiente  => 'Pendiente',
            self::Confirmada => 'Confirmada',
            self::Entregada  => 'Entregada',
            self::Cancelada  => 'Cancelada',
        };
    }
}
