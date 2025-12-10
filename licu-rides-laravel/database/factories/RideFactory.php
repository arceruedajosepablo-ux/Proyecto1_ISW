<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Ride>
 */
class RideFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $origenDestino = ['Santiago', 'Valparaíso', 'Viña del Mar', 'Concepción', 'La Serena'];
        $origen = fake()->randomElement($origenDestino);
        $destino = fake()->randomElement($origenDestino);
        
        return [
            'user_id' => \App\Models\User::factory(),
            'vehicle_id' => \App\Models\Vehicle::factory(),
            'nombre' => "Viaje de {$origen} a {$destino}",
            'origen' => $origen,
            'destino' => $destino,
            'fecha' => fake()->dateTimeBetween('now', '+30 days')->format('Y-m-d'),
            'hora' => fake()->time('H:i'),
            'espacios' => fake()->numberBetween(1, 4),
            'costo' => fake()->numberBetween(1000, 10000),
        ];
    }
}
