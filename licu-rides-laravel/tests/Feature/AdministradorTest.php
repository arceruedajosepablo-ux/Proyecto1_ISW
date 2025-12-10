<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Ride;
use App\Models\Reservation;
use App\Models\Vehicle;

class AdministradorTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test que un admin puede acceder al dashboard administrativo
     */
    public function test_admin_puede_acceder_al_dashboard(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);

        $this->actingAs($admin);

        $response = $this->get('/admin/dashboard');

        $response->assertStatus(200);
    }

    /**
     * Test que un conductor NO puede acceder al dashboard administrativo
     */
    public function test_conductor_no_puede_acceder_dashboard_admin(): void
    {
        $driver = User::factory()->create(['role' => 'driver', 'status' => 'active']);

        $this->actingAs($driver);

        $response = $this->get('/admin/dashboard');

        $response->assertStatus(403);
    }

    /**
     * Test que un pasajero NO puede acceder al dashboard administrativo
     */
    public function test_pasajero_no_puede_acceder_dashboard_admin(): void
    {
        $passenger = User::factory()->create(['role' => 'passenger', 'status' => 'active']);

        $this->actingAs($passenger);

        $response = $this->get('/admin/dashboard');

        $response->assertStatus(403);
    }

    /**
     * Test que un admin puede ver la lista de usuarios
     */
    public function test_admin_puede_ver_lista_usuarios(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        User::factory()->count(5)->create();

        $this->actingAs($admin);

        $response = $this->get('/admin/users');

        $response->assertStatus(200);
    }

    /**
     * Test que un admin puede activar usuarios
     */
    public function test_admin_puede_activar_usuario(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $user = User::factory()->create(['status' => 'pending']);

        $this->actingAs($admin);

        $response = $this->post("/admin/users/{$user->id}/activate");

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'status' => 'active'
        ]);
    }

    /**
     * Test que un admin puede cambiar el estado de un usuario
     */
    public function test_admin_puede_actualizar_estado_usuario(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $user = User::factory()->create(['status' => 'active']);

        $this->actingAs($admin);

        $response = $this->patch("/admin/users/{$user->id}/status", [
            'status' => 'inactive'
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'status' => 'inactive'
        ]);
    }

    /**
     * Test que un admin puede eliminar usuarios
     */
    public function test_admin_puede_eliminar_usuario(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $user = User::factory()->create();

        $this->actingAs($admin);

        $response = $this->delete("/admin/users/{$user->id}");

        $this->assertDatabaseMissing('users', [
            'id' => $user->id
        ]);
    }

    /**
     * Test que un admin puede ver todos los rides
     */
    public function test_admin_puede_ver_todos_los_viajes(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        
        $driver = User::factory()->create(['role' => 'driver', 'status' => 'active']);
        $vehicle = Vehicle::factory()->create(['user_id' => $driver->id]);
        Ride::factory()->count(3)->create(['user_id' => $driver->id, 'vehicle_id' => $vehicle->id]);

        $this->actingAs($admin);

        $response = $this->get('/admin/rides');

        $response->assertStatus(200);
    }

    /**
     * Test que un admin puede eliminar rides
     */
    public function test_admin_puede_eliminar_viaje(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $driver = User::factory()->create(['role' => 'driver', 'status' => 'active']);
        $vehicle = Vehicle::factory()->create(['user_id' => $driver->id]);
        $ride = Ride::factory()->create(['user_id' => $driver->id, 'vehicle_id' => $vehicle->id]);

        $this->actingAs($admin);

        $response = $this->delete("/admin/rides/{$ride->id}");

        $this->assertDatabaseMissing('rides', [
            'id' => $ride->id
        ]);
    }

    /**
     * Test que un conductor no puede eliminar usuarios
     */
    public function test_conductor_no_puede_eliminar_usuarios(): void
    {
        $driver = User::factory()->create(['role' => 'driver', 'status' => 'active']);
        $user = User::factory()->create();

        $this->actingAs($driver);

        $response = $this->delete("/admin/users/{$user->id}");

        $response->assertStatus(403);
    }
}
