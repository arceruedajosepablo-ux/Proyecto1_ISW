<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Vehicle>
 */
class VehicleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $marcas = ['Toyota', 'Honda', 'Ford', 'Chevrolet', 'Nissan'];
        $modelos = ['Corolla', 'Civic', 'Focus', 'Cruze', 'Sentra'];
        $colores = ['Rojo', 'Azul', 'Negro', 'Blanco', 'Gris'];
        
        return [
            'user_id' => \App\Models\User::factory(),
            'marca' => fake()->randomElement($marcas),
            'modelo' => fake()->randomElement($modelos),
            'placa' => strtoupper(fake()->bothify('???###')),
            'color' => fake()->randomElement($colores),
            'capacidad' => fake()->numberBetween(2, 5),
        ];
    }
}
