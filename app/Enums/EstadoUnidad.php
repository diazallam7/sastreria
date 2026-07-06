<?php

namespace App\Enums;

enum EstadoUnidad: string
{
    case Disponible = 'disponible';
    case Alquilada = 'alquilada';
    case Baja = 'baja';

    public function label(): string
    {
        return match ($this) {
            self::Disponible => 'Disponible',
            self::Alquilada => 'Alquilada',
            self::Baja => 'Baja',
        };
    }
}
