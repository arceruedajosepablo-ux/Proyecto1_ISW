<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Reservation;
use App\Models\User;
use App\Models\Ride;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ReservaModelTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Prueba que una reserva pertenece a un pasajero.
     */
    public function test_reserva_pertenece_a_pasajero(): void
    {
        $passenger = User::factory()->create(['role' => 'passenger']);
        $driver = User::factory()->create(['role' => 'driver']);
        $vehicle = Vehicle::factory()->create(['user_id' => $driver->id]);
        $ride = Ride::factory()->create(['user_id' => $driver->id, 'vehicle_id' => $vehicle->id]);

        $reservation = Reservation::factory()->create([
            'passenger_id' => $passenger->id,
            'ride_id' => $ride->id
        ]);

        $this->assertInstanceOf(User::class, $reservation->passenger);
        $this->assertEquals($passenger->id, $reservation->passenger->id);
    }

    /**
     * Prueba que una reserva pertenece a un ride.
     */
    public function test_reserva_pertenece_a_viaje(): void
    {
        $passenger = User::factory()->create(['role' => 'passenger']);
        $driver = User::factory()->create(['role' => 'driver']);
        $vehicle = Vehicle::factory()->create(['user_id' => $driver->id]);
        $ride = Ride::factory()->create(['user_id' => $driver->id, 'vehicle_id' => $vehicle->id]);

        $reservation = Reservation::factory()->create([
            'passenger_id' => $passenger->id,
            'ride_id' => $ride->id
        ]);

        $this->assertInstanceOf(Ride::class, $reservation->ride);
        $this->assertEquals($ride->id, $reservation->ride->id);
    }

    /**
     * Prueba los diferentes estados de una reserva.
     */
    public function test_reserva_cambia_estados(): void
    {
        $passenger = User::factory()->create(['role' => 'passenger']);
        $driver = User::factory()->create(['role' => 'driver']);
        $vehicle = Vehicle::factory()->create(['user_id' => $driver->id]);
        $ride = Ride::factory()->create(['user_id' => $driver->id, 'vehicle_id' => $vehicle->id]);

        // Crear reserva pendiente
        $reservation = Reservation::factory()->create([
            'passenger_id' => $passenger->id,
            'ride_id' => $ride->id,
            'status' => 'pending'
        ]);

        $this->assertEquals('pending', $reservation->status);

        // Cambiar a aceptada
        $reservation->update(['status' => 'accepted']);
        $this->assertEquals('accepted', $reservation->status);

        // Cambiar a rechazada
        $reservation->update(['status' => 'rejected']);
        $this->assertEquals('rejected', $reservation->status);

        // Cambiar a cancelada
        $reservation->update(['status' => 'cancelled']);
        $this->assertEquals('cancelled', $reservation->status);
    }

    /**
     * Prueba que una reserva tiene el número correcto de asientos.
     */
    public function test_reserva_tiene_asientos(): void
    {
        $passenger = User::factory()->create(['role' => 'passenger']);
        $driver = User::factory()->create(['role' => 'driver']);
        $vehicle = Vehicle::factory()->create(['user_id' => $driver->id]);
        $ride = Ride::factory()->create(['user_id' => $driver->id, 'vehicle_id' => $vehicle->id]);

        $reservation = Reservation::factory()->create([
            'passenger_id' => $passenger->id,
            'ride_id' => $ride->id,
            'seats' => 3
        ]);

        $this->assertEquals(3, $reservation->seats);
        $this->assertIsInt($reservation->seats);
    }
}
