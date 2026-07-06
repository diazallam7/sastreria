<?php

namespace App\Enums;

enum EstadoAlquiler: string
{
    case Activo     = 'activo';     // prendas entregadas, en poder del cliente
    case Completado = 'completado'; // devuelto correctamente
    case Cancelado  = 'cancelado';  // anulado, stock liberado sin devolución normal

    public function label(): string
    {
        return match ($this) {
            self::Activo     => 'Activo',
            self::Completado => 'Completado',
            self::Cancelado  => 'Cancelado',
        };
    }

    public function esActivo(): bool
    {
        return $this === self::Activo;
    }
}
