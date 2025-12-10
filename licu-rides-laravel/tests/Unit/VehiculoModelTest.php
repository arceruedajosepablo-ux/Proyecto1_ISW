<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Vehicle;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class VehiculoModelTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test que un vehículo pertenece a un usuario (conductor)
     */
    public function test_vehiculo_pertenece_a_usuario(): void
    {
        $user = User::factory()->create(['role' => 'driver']);
        $vehicle = Vehicle::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $vehicle->user);
        $this->assertEquals($user->id, $vehicle->user->id);
    }

    /**
     * Test que un vehículo tiene muchos rides
     */
    public function test_vehiculo_tiene_muchos_viajes(): void
    {
        $vehicle = Vehicle::factory()->create();
        
        $ride1 = \App\Models\Ride::factory()->create(['vehicle_id' => $vehicle->id]);
        $ride2 = \App\Models\Ride::factory()->create(['vehicle_id' => $vehicle->id]);

        $this->assertCount(2, $vehicle->rides);
        $this->assertTrue($vehicle->rides->contains($ride1));
        $this->assertTrue($vehicle->rides->contains($ride2));
    }

    /**
     * Test validación de capacidad del vehículo
     */
    public function test_vehiculo_tiene_capacidad(): void
    {
        $vehicle = Vehicle::factory()->create(['capacidad' => 4]);

        $this->assertEquals(4, $vehicle->capacidad);
        $this->assertIsInt($vehicle->capacidad);
    }

    /**
     * Test que un vehículo requiere campos obligatorios
     */
    public function test_vehiculo_requiere_campos_obligatorios(): void
    {
        $vehicle = Vehicle::factory()->create([
            'placa' => 'ABC123',
            'marca' => 'Toyota',
            'modelo' => 'Corolla'
        ]);

        $this->assertEquals('ABC123', $vehicle->placa);
        $this->assertEquals('Toyota', $vehicle->marca);
        $this->assertEquals('Corolla', $vehicle->modelo);
    }
}
