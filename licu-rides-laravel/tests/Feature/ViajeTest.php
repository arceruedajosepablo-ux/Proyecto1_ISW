<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\Ride;

class ViajeTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Prueba que un conductor puede crear un ride.
     */
    public function test_conductor_puede_crear_viaje(): void
    {
        $driver = User::factory()->create(['role' => 'driver', 'status' => 'active']);
        $vehicle = Vehicle::factory()->create(['user_id' => $driver->id, 'capacidad' => 4]);

        $this->actingAs($driver);

        $response = $this->post('/rides', [
            'nombre' => 'Ride a San José',
            'origen' => 'Alajuela',
            'destino' => 'San José',
            'vehicle_id' => $vehicle->id,
            'fecha' => now()->addDays(1)->format('Y-m-d'),
            'hora' => '10:00',
            'costo' => 5000,
            'espacios' => 3
        ]);

        $response->assertSessionHasNoErrors();

        // Verificar que el ride fue creado
        $this->assertDatabaseHas('rides', [
            'nombre' => 'Ride a San José',
            'origen' => 'Alajuela',
            'destino' => 'San José',
            'user_id' => $driver->id,
            'espacios' => 3
        ]);

        $response->assertRedirect('/rides');
    }

    /**
     * Prueba que un pasajero NO puede crear un ride.
     */
    public function test_pasajero_no_puede_crear_viaje(): void
    {
        $passenger = User::factory()->create(['role' => 'passenger', 'status' => 'active']);

        $this->actingAs($passenger);

        $response = $this->get('/rides/create');

        // Verificar que se niega el acceso (redirect o 403)
        $response->assertStatus(403);
    }

    /**
     * Prueba que no se puede crear un ride sin vehículo.
     */
    public function test_no_puede_crear_viaje_sin_vehiculo(): void
    {
        $driver = User::factory()->create(['role' => 'driver', 'status' => 'active']);

        $this->actingAs($driver);

        $response = $this->post('/rides', [
            'nombre' => 'Ride sin vehículo',
            'origen' => 'Alajuela',
            'destino' => 'San José',
            'vehicle_id' => 999, // ID inexistente
            'fecha' => now()->addDays(1)->format('Y-m-d'),
            'hora' => '10:00',
            'costo' => 5000,
            'espacios' => 3
        ]);

        $response->assertSessionHasErrors(['vehicle_id']);
    }

    /**
     * Prueba que un conductor puede editar su propio ride.
     */
    public function test_conductor_puede_editar_su_propio_viaje(): void
    {
        $driver = User::factory()->create(['role' => 'driver', 'status' => 'active']);
        $vehicle = Vehicle::factory()->create(['user_id' => $driver->id]);
        $ride = Ride::factory()->create([
            'user_id' => $driver->id,
            'vehicle_id' => $vehicle->id
        ]);

        $this->actingAs($driver);

        $response = $this->put("/rides/{$ride->id}", [
            'nombre' => 'Ride Actualizado',
            'origen' => $ride->origen,
            'destino' => $ride->destino,
            'vehicle_id' => $vehicle->id,
            'fecha' => $ride->fecha->format('Y-m-d'),
            'hora' => $ride->hora,
            'costo' => 6000,
            'espacios' => $ride->espacios
        ]);

        $this->assertDatabaseHas('rides', [
            'id' => $ride->id,
            'nombre' => 'Ride Actualizado',
            'costo' => 6000
        ]);
    }

    /**
     * Prueba que un conductor puede eliminar su propio ride.
     */
    public function test_conductor_puede_eliminar_su_propio_viaje(): void
    {
        $driver = User::factory()->create(['role' => 'driver', 'status' => 'active']);
        $vehicle = Vehicle::factory()->create(['user_id' => $driver->id]);
        $ride = Ride::factory()->create([
            'user_id' => $driver->id,
            'vehicle_id' => $vehicle->id
        ]);

        $this->actingAs($driver);

        $response = $this->delete("/rides/{$ride->id}");

        $this->assertDatabaseMissing('rides', [
            'id' => $ride->id
        ]);

        $response->assertRedirect('/rides');
    }

    /**
     * Prueba validación de espacios no exceda capacidad del vehículo.
     */
    public function test_espacios_viaje_no_exceden_capacidad_vehiculo(): void
    {
        $driver = User::factory()->create(['role' => 'driver', 'status' => 'active']);
        $vehicle = Vehicle::factory()->create([
            'user_id' => $driver->id,
            'capacidad' => 4
        ]);

        $this->actingAs($driver);

        $response = $this->post('/rides', [
            'nombre' => 'Ride con muchos espacios',
            'origen' => 'Alajuela',
            'destino' => 'San José',
            'vehicle_id' => $vehicle->id,
            'fecha' => now()->addDays(1)->format('Y-m-d'),
            'hora' => '10:00',
            'costo' => 5000,
            'espacios' => 6 // Excede capacidad del vehículo (4)
        ]);

        $response->assertSessionHasErrors(['espacios']);
    }
}
