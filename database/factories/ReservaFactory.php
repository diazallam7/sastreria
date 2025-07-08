<?php

namespace Database\Factories;

use App\Models\Reserva;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReservaFactory extends Factory
{
    protected $model = Reserva::class;

    public function definition(): array
    {
        return [
            'cliente_id' => $this->faker->numberBetween(1, 7),
            'user_id' => $this->faker->numberBetween(1, 3),
            'fecha_reserva' => $this->faker->date(),
            'fecha_entrega_programada' => $this->faker->dateTimeBetween('+1 days', '+5 days')->format('Y-m-d'),
            'fecha_devolucion_programada' => $this->faker->dateTimeBetween('+6 days', '+10 days')->format('Y-m-d'),
            'monto_total' => $this->faker->randomFloat(2, 100, 1000),
            'garantia_total' => $this->faker->randomFloat(2, 50, 500),
            'seña_garantia' => $this->faker->randomFloat(2, 10, 100),
            'seña_devuelta' => $this->faker->randomFloat(2, 0, 50),
            'motivo_devolucion' => $this->faker->optional()->sentence(),
            'seña_alquiler' => $this->faker->randomFloat(2, 10, 100),
            'estado' => $this->faker->randomElement(['cancelada', 'confirmada']),
            'observaciones' => $this->faker->optional()->paragraph(),
            'alquiler_id' => null, // o número aleatorio si tenés registros
        ];
    }
}
