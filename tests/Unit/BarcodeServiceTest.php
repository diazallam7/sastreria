<?php

namespace Tests\Unit;

use App\Services\BarcodeService;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class BarcodeServiceTest extends TestCase
{
    private BarcodeService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new BarcodeService;
    }

    public function test_genera_codigo_de_venta_con_prefijo_y_padding(): void
    {
        $this->assertSame('PRD-0000123', $this->service->generarCodigoVenta(123));
        $this->assertSame('PRD-0000001', $this->service->generarCodigoVenta(1));
    }

    public function test_genera_codigo_de_unidad_con_prefijo_y_padding(): void
    {
        $this->assertSame('ALQ-0000045', $this->service->generarCodigoUnidad(45));
    }

    public function test_genera_codigo_sin_truncar_ids_mas_largos_que_el_padding(): void
    {
        $this->assertSame('PRD-12345678', $this->service->generarCodigoVenta(12345678));
    }

    public function test_parsear_clasifica_codigo_de_venta(): void
    {
        $resultado = $this->service->parsear('PRD-0000123');

        $this->assertSame('venta', $resultado['tipo']);
        $this->assertSame(123, $resultado['ref_id']);
        $this->assertSame('PRD-0000123', $resultado['raw']);
    }

    public function test_parsear_clasifica_codigo_de_alquiler(): void
    {
        $resultado = $this->service->parsear('ALQ-0000045');

        $this->assertSame('alquiler', $resultado['tipo']);
        $this->assertSame(45, $resultado['ref_id']);
        $this->assertSame('ALQ-0000045', $resultado['raw']);
    }

    public function test_parsear_clasifica_ean_como_venta_sin_ref_id(): void
    {
        $resultado = $this->service->parsear('5901234123457');

        $this->assertSame('venta', $resultado['tipo']);
        $this->assertNull($resultado['ref_id']);
        $this->assertSame('5901234123457', $resultado['raw']);
    }

    public function test_parsear_lanza_excepcion_con_codigo_no_reconocido(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->service->parsear('XYZ-abc');
    }

    public function test_parsear_lanza_excepcion_con_codigo_vacio(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->service->parsear('');
    }

    public function test_png_base64_genera_data_uri_para_codigo_interno(): void
    {
        $resultado = $this->service->pngBase64('PRD-0000123');

        $this->assertStringStartsWith('data:image/png;base64,', $resultado);
    }

    public function test_png_base64_genera_data_uri_para_ean(): void
    {
        $resultado = $this->service->pngBase64('5901234123457');

        $this->assertStringStartsWith('data:image/png;base64,', $resultado);
    }
}
