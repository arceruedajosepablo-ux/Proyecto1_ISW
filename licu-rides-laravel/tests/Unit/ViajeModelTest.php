<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Ride;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\Reservation;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ViajeModelTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Prueba que un ride pertenece a un usuario (conductor).
     */
    public function test_viaje_pertenece_a_usuario(): void
    {
        // Crear un usuario conductor
        $driver = User::factory()->create(['role' => 'driver']);
        
        // Crear un vehículo para el conductor
        $vehicle = Vehicle::factory()->create(['user_id' => $driver->id]);
        
        // Crear un ride
        $ride = Ride::factory()->create([
            'user_id' => $driver->id,
            'vehicle_id' => $vehicle->id
        ]);

        // Verificar la relación
        $this->assertInstanceOf(User::class, $ride->user);
        $this->assertEquals($driver->id, $ride->user->id);
        $this->assertEquals('driver', $ride->user->role);
    }

    /**
     * Prueba que un ride pertenece a un vehículo.
     */
    public function test_viaje_pertenece_a_vehiculo(): void
    {
        $driver = User::factory()->create(['role' => 'driver']);
        $vehicle = Vehicle::factory()->create(['user_id' => $driver->id]);
        $ride = Ride::factory()->create([
            'user_id' => $driver->id,
            'vehicle_id' => $vehicle->id
        ]);

        $this->assertInstanceOf(Vehicle::class, $ride->vehicle);
        $this->assertEquals($vehicle->id, $ride->vehicle->id);
    }

    /**
     * Prueba el cálculo de espacios disponibles.
     * Solo debe contar las reservas con estado 'accepted'.
     */
    public function test_espacios_disponibles_calculation(): void
    {
        $driver = User::factory()->create(['role' => 'driver']);
        $vehicle = Vehicle::factory()->create(['user_id' => $driver->id, 'capacidad' => 4]);
        
        // Crear un ride con 4 espacios
        $ride = Ride::factory()->create([
            'user_id' => $driver->id,
            'vehicle_id' => $vehicle->id,
            'espacios' => 4
        ]);

        // Sin reservas, debe tener 4 espacios disponibles
        $this->assertEquals(4, $ride->espacios_disponibles);

        // Crear 2 reservas aceptadas de 1 asiento cada una
        $passenger1 = User::factory()->create(['role' => 'passenger']);
        $passenger2 = User::factory()->create(['role' => 'passenger']);
        
        Reservation::factory()->create([
            'ride_id' => $ride->id,
            'passenger_id' => $passenger1->id,
            'seats' => 1,
            'status' => 'accepted'
        ]);
        
        Reservation::factory()->create([
            'ride_id' => $ride->id,
            'passenger_id' => $passenger2->id,
            'seats' => 1,
            'status' => 'accepted'
        ]);

        // Refrescar el modelo para obtener las relaciones actualizadas
        $ride->refresh();

        // Ahora debe tener 2 espacios disponibles (4 - 2 aceptadas)
        $this->assertEquals(2, $ride->espacios_disponibles);

        // Crear una reserva pendiente (NO debe afectar espacios disponibles)
        $passenger3 = User::factory()->create(['role' => 'passenger']);
        Reservation::factory()->create([
            'ride_id' => $ride->id,
            'passenger_id' => $passenger3->id,
            'seats' => 1,
            'status' => 'pending'
        ]);

        $ride->refresh();

        // Debe seguir teniendo 2 espacios disponibles (no cuenta las pendientes)
        $this->assertEquals(2, $ride->espacios_disponibles);
    }

    /**
     * Prueba que un ride tiene muchas reservaciones.
     */
    public function test_viaje_tiene_muchas_reservaciones(): void
    {
        $driver = User::factory()->create(['role' => 'driver']);
        $vehicle = Vehicle::factory()->create(['user_id' => $driver->id]);
        $ride = Ride::factory()->create([
            'user_id' => $driver->id,
            'vehicle_id' => $vehicle->id
        ]);

        // Crear 3 reservas para este ride
        $passenger1 = User::factory()->create(['role' => 'passenger']);
        $passenger2 = User::factory()->create(['role' => 'passenger']);
        $passenger3 = User::factory()->create(['role' => 'passenger']);

        Reservation::factory()->create(['ride_id' => $ride->id, 'passenger_id' => $passenger1->id]);
        Reservation::factory()->create(['ride_id' => $ride->id, 'passenger_id' => $passenger2->id]);
        Reservation::factory()->create(['ride_id' => $ride->id, 'passenger_id' => $passenger3->id]);

        $ride->refresh();

        $this->assertCount(3, $ride->reservations);
    }
}
