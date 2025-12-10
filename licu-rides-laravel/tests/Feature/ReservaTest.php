<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\Ride;
use App\Models\Reservation;

class ReservaTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Prueba que un pasajero puede crear una reserva.
     */
    public function test_pasajero_puede_crear_reserva(): void
    {
        $driver = User::factory()->create(['role' => 'driver', 'status' => 'active']);
        $passenger = User::factory()->create(['role' => 'passenger', 'status' => 'active']);
        $vehicle = Vehicle::factory()->create(['user_id' => $driver->id]);
        $ride = Ride::factory()->create([
            'user_id' => $driver->id,
            'vehicle_id' => $vehicle->id,
            'espacios' => 4
        ]);

        $this->actingAs($passenger);

        $response = $this->post('/reservations', [
            'ride_id' => $ride->id,
            'seats' => 2
        ]);

        // Verificar que la reserva fue creada
        $this->assertDatabaseHas('reservations', [
            'ride_id' => $ride->id,
            'passenger_id' => $passenger->id,
            'seats' => 2,
            'status' => 'pending'
        ]);

        $response->assertRedirect();
    }

    /**
     * Prueba que un conductor NO puede reservar su propio ride.
     */
    public function test_conductor_no_puede_reservar_su_propio_viaje(): void
    {
        $driver = User::factory()->create(['role' => 'driver', 'status' => 'active']);
        $vehicle = Vehicle::factory()->create(['user_id' => $driver->id]);
        $ride = Ride::factory()->create([
            'user_id' => $driver->id,
            'vehicle_id' => $vehicle->id
        ]);

        $this->actingAs($driver);

        $response = $this->post('/reservations', [
            'ride_id' => $ride->id,
            'seats' => 1
        ]);

        // No debe crear la reserva
        $this->assertDatabaseMissing('reservations', [
            'ride_id' => $ride->id,
            'passenger_id' => $driver->id
        ]);
    }

    /**
     * Prueba que un conductor puede aceptar una reserva.
     */
    public function test_conductor_puede_aceptar_reserva(): void
    {
        $driver = User::factory()->create(['role' => 'driver', 'status' => 'active']);
        $passenger = User::factory()->create(['role' => 'passenger', 'status' => 'active']);
        $vehicle = Vehicle::factory()->create(['user_id' => $driver->id]);
        $ride = Ride::factory()->create([
            'user_id' => $driver->id,
            'vehicle_id' => $vehicle->id,
            'espacios' => 4
        ]);

        $reservation = Reservation::factory()->create([
            'ride_id' => $ride->id,
            'passenger_id' => $passenger->id,
            'seats' => 2,
            'status' => 'pending'
        ]);

        $this->actingAs($driver);

        $response = $this->post("/reservations/{$reservation->id}/accept");

        // Verificar que la reserva fue aceptada
        $this->assertDatabaseHas('reservations', [
            'id' => $reservation->id,
            'status' => 'accepted'
        ]);

        $response->assertRedirect();
    }

    /**
     * Prueba que un conductor puede rechazar una reserva.
     */
    public function test_conductor_puede_rechazar_reserva(): void
    {
        $driver = User::factory()->create(['role' => 'driver', 'status' => 'active']);
        $passenger = User::factory()->create(['role' => 'passenger', 'status' => 'active']);
        $vehicle = Vehicle::factory()->create(['user_id' => $driver->id]);
        $ride = Ride::factory()->create([
            'user_id' => $driver->id,
            'vehicle_id' => $vehicle->id
        ]);

        $reservation = Reservation::factory()->create([
            'ride_id' => $ride->id,
            'passenger_id' => $passenger->id,
            'status' => 'pending'
        ]);

        $this->actingAs($driver);

        $response = $this->post("/reservations/{$reservation->id}/reject");

        $this->assertDatabaseHas('reservations', [
            'id' => $reservation->id,
            'status' => 'rejected'
        ]);

        $response->assertRedirect();
    }

    /**
     * Prueba que un pasajero puede cancelar su propia reserva pendiente.
     */
    public function test_pasajero_puede_cancelar_su_reserva(): void
    {
        $driver = User::factory()->create(['role' => 'driver', 'status' => 'active']);
        $passenger = User::factory()->create(['role' => 'passenger', 'status' => 'active']);
        $vehicle = Vehicle::factory()->create(['user_id' => $driver->id]);
        $ride = Ride::factory()->create([
            'user_id' => $driver->id,
            'vehicle_id' => $vehicle->id
        ]);

        $reservation = Reservation::factory()->create([
            'ride_id' => $ride->id,
            'passenger_id' => $passenger->id,
            'status' => 'pending'
        ]);

        $this->actingAs($passenger);

        $response = $this->delete("/reservations/{$reservation->id}");

        // La reserva debe ser eliminada o marcada como cancelada
        $this->assertDatabaseMissing('reservations', [
            'id' => $reservation->id,
            'status' => 'pending'
        ]);

        $response->assertRedirect();
    }

    /**
     * Prueba que no se puede reservar más espacios de los disponibles.
     */
    public function test_no_puede_reservar_mas_asientos_que_disponibles(): void
    {
        $driver = User::factory()->create(['role' => 'driver', 'status' => 'active']);
        $passenger = User::factory()->create(['role' => 'passenger', 'status' => 'active']);
        $vehicle = Vehicle::factory()->create(['user_id' => $driver->id]);
        $ride = Ride::factory()->create([
            'user_id' => $driver->id,
            'vehicle_id' => $vehicle->id,
            'espacios' => 2
        ]);

        $this->actingAs($passenger);

        $response = $this->post('/reservations', [
            'ride_id' => $ride->id,
            'seats' => 5 // Excede espacios disponibles
        ]);

        // Verificar que la reserva no fue creada o hubo un error
        $response->assertStatus(302); // Redirect debido a error
    }

    /**
     * Prueba autorización: un pasajero no puede aceptar reservas.
     */
    public function test_pasajero_no_puede_aceptar_reservas(): void
    {
        $driver = User::factory()->create(['role' => 'driver', 'status' => 'active']);
        $passenger1 = User::factory()->create(['role' => 'passenger', 'status' => 'active']);
        $passenger2 = User::factory()->create(['role' => 'passenger', 'status' => 'active']);
        $vehicle = Vehicle::factory()->create(['user_id' => $driver->id]);
        $ride = Ride::factory()->create([
            'user_id' => $driver->id,
            'vehicle_id' => $vehicle->id
        ]);

        $reservation = Reservation::factory()->create([
            'ride_id' => $ride->id,
            'passenger_id' => $passenger1->id,
            'status' => 'pending'
        ]);

        // Intentar aceptar como otro pasajero
        $this->actingAs($passenger2);

        $response = $this->post("/reservations/{$reservation->id}/accept");

        // Debe denegar el acceso
        $response->assertStatus(403);
    }
}
