<?php

namespace Database\Seeders;

use App\Models\Configuracion;
use Illuminate\Database\Seeder;

class ConfiguracionSeeder extends Seeder
{
    /**
     * Parámetros de negocio editables desde Configuración.
     * La clave 'multa' la consume DevolucionService (multa diaria por retraso).
     */
    public function run(): void
    {
        $configs = [
            ['nombre' => 'multa', 'descripcion' => 'Multa diaria por día de retraso en la devolución de un alquiler.', 'valor' => 10000],
        ];

        foreach ($configs as $config) {
            Configuracion::firstOrCreate(
                ['nombre' => $config['nombre']],
                ['descripcion' => $config['descripcion'], 'valor' => $config['valor']],
            );
        }
    }
}
