<?php

namespace App\Enums;

enum TipoProducto: string
{
    /** Adquirido a terceros para reventa. Tiene costo de compra → egreso en caja. */
    case Comprado = 'comprado';

    /** Fabricado por el taller para la venta (antes "producto manual"). Sin costo de compra. */
    case Fabricado = 'fabricado';

    public function esComprado(): bool
    {
        return $this === self::Comprado;
    }

    public function label(): string
    {
        return match ($this) {
            self::Comprado  => 'Comprado',
            self::Fabricado => 'Fabricado',
        };
    }

    /** @return array<int, string> */
    public static function valores(): array
    {
        return array_column(self::cases(), 'value');
    }
}
