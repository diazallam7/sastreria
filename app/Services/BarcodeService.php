<?php

namespace App\Services;

use InvalidArgumentException;
use Picqer\Barcode\BarcodeGeneratorPNG;

class BarcodeService
{
    private const PREFIJO_VENTA = 'PRD-';

    private const PREFIJO_ALQUILER = 'ALQ-';

    private const PADDING = 7;

    public function generarCodigoVenta(int $productoTalleId): string
    {
        return self::PREFIJO_VENTA.str_pad((string) $productoTalleId, self::PADDING, '0', STR_PAD_LEFT);
    }

    public function generarCodigoUnidad(int $unidadId): string
    {
        return self::PREFIJO_ALQUILER.str_pad((string) $unidadId, self::PADDING, '0', STR_PAD_LEFT);
    }

    /**
     * Clasifica un código escaneado según la convención de prefijos (docs/barcode-spec.md §1.3).
     *
     * @return array{tipo: 'venta'|'alquiler', ref_id: int|null, raw: string}
     */
    public function parsear(string $codigo): array
    {
        if (str_starts_with($codigo, self::PREFIJO_VENTA)) {
            return ['tipo' => 'venta', 'ref_id' => $this->extraerId($codigo, self::PREFIJO_VENTA), 'raw' => $codigo];
        }

        if (str_starts_with($codigo, self::PREFIJO_ALQUILER)) {
            return ['tipo' => 'alquiler', 'ref_id' => $this->extraerId($codigo, self::PREFIJO_ALQUILER), 'raw' => $codigo];
        }

        // EAN de fábrica: solo dígitos, sin ref_id (se resuelve por lookup de columna codigo_barra).
        if ($codigo !== '' && ctype_digit($codigo)) {
            return ['tipo' => 'venta', 'ref_id' => null, 'raw' => $codigo];
        }

        throw new InvalidArgumentException("Código de barra no reconocido: {$codigo}");
    }

    /**
     * PNG en data-URI base64 para incrustar en PDF (Code128 para PRD-/ALQ-, EAN-13 para EAN crudo).
     */
    public function pngBase64(string $codigo): string
    {
        $generator = new BarcodeGeneratorPNG;

        $tipo = ctype_digit($codigo)
            ? BarcodeGeneratorPNG::TYPE_EAN_13
            : BarcodeGeneratorPNG::TYPE_CODE_128;

        $png = $generator->getBarcode($codigo, $tipo);

        return 'data:image/png;base64,'.base64_encode($png);
    }

    private function extraerId(string $codigo, string $prefijo): int
    {
        return (int) substr($codigo, strlen($prefijo));
    }
}
