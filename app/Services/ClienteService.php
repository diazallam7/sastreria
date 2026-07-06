<?php

namespace App\Services;

use App\Enums\TipoMedida;
use App\Models\Cliente;
use Illuminate\Support\Facades\DB;

class ClienteService
{
    /**
     * Crea o actualiza un cliente y sincroniza sus medidas (versionadas).
     *
     * @param  array<string, mixed>  $datos           campos del cliente
     * @param  array<string, array>  $medidasInput    ['saco'=>[campo=>valor], ...]
     */
    public function guardar(Cliente $cliente, array $datos, array $medidasInput, ?string $observaciones): Cliente
    {
        return DB::transaction(function () use ($cliente, $datos, $medidasInput, $observaciones) {
            $cliente->fill($datos)->save();
            $this->sincronizarMedidas($cliente, $medidasInput, $observaciones);

            return $cliente;
        });
    }

    /**
     * Versiona por tipo de prenda: si las medidas cambian, archiva la versión
     * vigente y crea una nueva. Si no cambian, no hace nada.
     */
    private function sincronizarMedidas(Cliente $cliente, array $medidasInput, ?string $observaciones): void
    {
        foreach (TipoMedida::cases() as $tipo) {
            $datos = collect($medidasInput[$tipo->value] ?? [])
                ->filter(fn ($valor) => $valor !== null && $valor !== '')
                ->map(fn ($valor) => (float) $valor)
                ->all();

            if (empty($datos)) {
                continue;
            }

            $vigente = $cliente->medidaVigente($tipo)->first();

            $sinCambios = $vigente
                && $this->medidasIguales($vigente->medidas, $datos)
                && $vigente->observaciones === $observaciones;

            if ($sinCambios) {
                continue;
            }

            $vigente?->update(['vigente' => false]);

            $cliente->medidas()->create([
                'user_id'       => auth()->id(),
                'tipo'          => $tipo->value,
                'medidas'       => $datos,
                'observaciones' => $observaciones,
                'vigente'       => true,
            ]);
        }
    }

    private function medidasIguales(array $a, array $b): bool
    {
        ksort($a);
        ksort($b);

        return $a == $b;
    }

    /**
     * Reglas de validación del bloque de medidas (reutilizable por el componente).
     *
     * @return array<string, array<int, string>>
     */
    public static function reglasMedidas(): array
    {
        $reglas = [
            'medidas'               => ['nullable', 'array:' . implode(',', TipoMedida::valores())],
            'observaciones_medidas' => ['nullable', 'string', 'max:1000'],
        ];

        foreach (TipoMedida::cases() as $tipo) {
            $reglas["medidas.{$tipo->value}"]   = ['nullable', 'array:' . implode(',', $tipo->campos())];
            $reglas["medidas.{$tipo->value}.*"] = ['nullable', 'numeric', 'min:0', 'max:999.99'];
        }

        return $reglas;
    }
}
