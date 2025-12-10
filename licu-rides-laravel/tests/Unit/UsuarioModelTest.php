<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class UsuarioModelTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test que un usuario puede ser conductor
     */
    public function test_usuario_es_conductor(): void
    {
        $user = User::factory()->create(['role' => 'driver']);

        $this->assertTrue($user->isDriver());
        $this->assertFalse($user->isPassenger());
        $this->assertFalse($user->isAdmin());
    }

    /**
     * Test que un usuario puede ser pasajero
     */
    public function test_usuario_es_pasajero(): void
    {
        $user = User::factory()->create(['role' => 'passenger']);

        $this->assertTrue($user->isPassenger());
        $this->assertFalse($user->isDriver());
        $this->assertFalse($user->isAdmin());
    }

    /**
     * Test que un usuario puede ser administrador
     */
    public function test_usuario_es_administrador(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $this->assertTrue($user->isAdmin());
        $this->assertFalse($user->isDriver());
        $this->assertFalse($user->isPassenger());
    }

    /**
     * Prueba que la contraseña se hashea correctamente.
     */
    public function test_contrasena_se_hashea(): void
    {
        $password = 'password123';
        $user = User::factory()->create([
            'password' => Hash::make($password)
        ]);

        $this->assertTrue(Hash::check($password, $user->password));
        $this->assertNotEquals($password, $user->password);
    }

    /**
     * Test que un conductor tiene vehículos
     */
    public function test_conductor_tiene_vehiculos(): void
    {
        $driver = User::factory()->create(['role' => 'driver']);
        
        // Crear 2 vehículos para el conductor
        \App\Models\Vehicle::factory()->count(2)->create(['user_id' => $driver->id]);

        $driver->refresh();

        $this->assertCount(2, $driver->vehicles);
    }
}
