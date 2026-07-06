<?php

namespace App\Enums;

enum TipoMedida: string
{
    case Saco = 'saco';
    case Pantalon = 'pantalon';
    case Chaleco = 'chaleco';

    /**
     * Campos de medida válidos para cada tipo de prenda.
     * Fuente única de verdad: validación y vistas se derivan de aquí.
     *
     * @return array<int, string>
     */
    public function campos(): array
    {
        return match ($this) {
            self::Saco => [
                'talle', 'largo', 'espalda', 'manga', 'pecho', 'cintura', 'cadera',
                'alto_hombro', 'plomo_trasero', 'plomo_delantero', 'sisa', 'puno',
            ],
            self::Pantalon => [
                'largo', 'cintura', 'cadera', 'entre_pierna', 'muslo', 'rodilla', 'bajo',
            ],
            self::Chaleco => [
                'talle', 'pecho', 'cintura', 'escote', 'largo', 'largo_trasero', 'cuello',
            ],
        };
    }

    /**
     * @return array<int, string> valores del enum ('saco', 'pantalon', 'chaleco')
     */
    public static function valores(): array
    {
        return array_column(self::cases(), 'value');
    }
}
