<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Vehicle;

class VehiculoTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Prueba que un conductor puede crear un vehículo
     */
    public function test_conductor_puede_crear_vehiculo(): void
    {
        $driver = User::factory()->create(['role' => 'driver', 'status' => 'active']);

        $this->actingAs($driver);

        $response = $this->post('/vehicles', [
            'placa' => 'ABC123',
            'marca' => 'Toyota',
            'modelo' => 'Corolla',
            'color' => 'Rojo',
            'ano' => 2020,
            'capacidad' => 4
        ]);

        $this->assertDatabaseHas('vehicles', [
            'placa' => 'ABC123',
            'marca' => 'Toyota',
            'user_id' => $driver->id
        ]);

        $response->assertRedirect();
    }

    /**
     * Prueba que un pasajero NO puede crear vehículos
     */
    public function test_pasajero_no_puede_crear_vehiculo(): void
    {
        $passenger = User::factory()->create(['role' => 'passenger', 'status' => 'active']);

        $this->actingAs($passenger);

        $response = $this->get('/vehicles/create');

        $response->assertStatus(403);
    }

    /**
     * Prueba que un conductor puede editar su propio vehículo
     */
    public function test_conductor_puede_editar_su_vehiculo(): void
    {
        $driver = User::factory()->create(['role' => 'driver', 'status' => 'active']);
        $vehicle = Vehicle::factory()->create(['user_id' => $driver->id]);

        $this->actingAs($driver);

        $response = $this->put("/vehicles/{$vehicle->id}", [
            'placa' => $vehicle->placa,
            'marca' => 'Honda',
            'modelo' => 'Civic',
            'color' => 'Azul',
            'ano' => 2021,
            'capacidad' => 5
        ]);

        $this->assertDatabaseHas('vehicles', [
            'id' => $vehicle->id,
            'marca' => 'Honda',
            'modelo' => 'Civic',
            'capacidad' => 5
        ]);
    }

    /**
     * Prueba que un conductor puede eliminar su propio vehículo
     */
    public function test_conductor_puede_eliminar_su_vehiculo(): void
    {
        $driver = User::factory()->create(['role' => 'driver', 'status' => 'active']);
        $vehicle = Vehicle::factory()->create(['user_id' => $driver->id]);

        $this->actingAs($driver);

        $response = $this->delete("/vehicles/{$vehicle->id}");

        $this->assertDatabaseMissing('vehicles', [
            'id' => $vehicle->id
        ]);

        $response->assertRedirect();
    }

    /**
     * Prueba validación: requiere placa
     */
    public function test_vehiculo_requiere_placa(): void
    {
        $driver = User::factory()->create(['role' => 'driver', 'status' => 'active']);

        $this->actingAs($driver);

        $response = $this->post('/vehicles', [
            'marca' => 'Toyota',
            'modelo' => 'Corolla',
            'capacidad' => 4
        ]);

        $response->assertSessionHasErrors(['placa']);
    }

    /**
     * Prueba que un conductor no puede editar vehículo de otro conductor
     */
    public function test_conductor_no_puede_editar_vehiculo_de_otro(): void
    {
        $driver1 = User::factory()->create(['role' => 'driver', 'status' => 'active']);
        $driver2 = User::factory()->create(['role' => 'driver', 'status' => 'active']);
        $vehicle = Vehicle::factory()->create(['user_id' => $driver1->id]);

        $this->actingAs($driver2);

        $response = $this->put("/vehicles/{$vehicle->id}", [
            'placa' => $vehicle->placa,
            'marca' => 'Honda',
            'modelo' => 'Civic',
            'ano' => 2021,
            'capacidad' => 5
        ]);

        $response->assertStatus(403);
    }
}
