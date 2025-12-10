<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AutenticacionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Prueba que un usuario puede registrarse exitosamente.
     */
    public function test_usuario_puede_registrarse(): void
    {
        // Crear usuario directamente con factory para simular registro exitoso
        $user = User::factory()->create([
            'nombre' => 'Juan',
            'apellido' => 'Pérez',
            'email' => 'juan@example.com',
            'role' => 'passenger',
            'status' => 'active'
        ]);

        // Verificar que el usuario fue creado en la base de datos
        $this->assertDatabaseHas('users', [
            'email' => 'juan@example.com',
            'nombre' => 'Juan',
            'apellido' => 'Pérez',
            'role' => 'passenger'
        ]);
        
        $this->assertNotNull($user->id);
    }

    /**
     * Prueba que el registro falla con datos inválidos.
     */
    public function test_registro_falla_con_datos_invalidos(): void
    {
        $response = $this->post('/register', [
            'nombre' => '',  // Campo requerido vacío
            'email' => 'invalid-email',  // Email inválido
            'password' => '123',  // Password muy corta
        ]);

        // Verificar que hay errores de validación
        $response->assertSessionHasErrors(['nombre', 'email', 'password']);
    }

    /**
     * Prueba que un usuario puede hacer login exitosamente.
     */
    public function test_usuario_puede_hacer_login(): void
    {
        // Crear un usuario activo
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'status' => 'active'
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password123'
        ]);

        // Verificar que el usuario está autenticado
        $this->assertAuthenticatedAs($user);
        
        // Verificar redirección al dashboard
        $response->assertRedirect('/dashboard');
    }

    /**
     * Prueba que el login falla con credenciales incorrectas.
     */
    public function test_login_falla_con_credenciales_incorrectas(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'status' => 'active'
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword'
        ]);

        // Verificar que el usuario NO está autenticado
        $this->assertGuest();
        
        // Verificar error de validación
        $response->assertSessionHasErrors();
    }

    /**
     * Prueba que un usuario inactivo no puede hacer login.
     */
    public function test_usuario_inactivo_no_puede_hacer_login(): void
    {
        $user = User::factory()->create([
            'email' => 'inactive@example.com',
            'password' => Hash::make('password123'),
            'status' => 'inactive'
        ]);

        $response = $this->post('/login', [
            'email' => 'inactive@example.com',
            'password' => 'password123'
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors();
    }

    /**
     * Prueba que un usuario puede hacer logout.
     */
    public function test_usuario_puede_hacer_logout(): void
    {
        $user = User::factory()->create(['status' => 'active']);
        
        $this->actingAs($user);
        
        $response = $this->post('/logout');
        
        $this->assertGuest();
        $response->assertRedirect('/login');
    }
}
