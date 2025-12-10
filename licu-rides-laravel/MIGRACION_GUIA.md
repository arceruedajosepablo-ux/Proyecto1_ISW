# Guía de Migración: Licu Rides a Laravel

## ✅ Completado

### 1. Proyecto Laravel Creado
- ✅ Laravel 12 instalado en `licu-rides-laravel/`
- ✅ Base de datos SQLite configurada

### 2. Base de Datos y Migraciones
- ✅ Migración de users con campos personalizados (role, nombre, apellido, cedula, etc.)
- ✅ Migración de vehicles
- ✅ Migración de rides
- ✅ Migración de reservations
- ✅ Todas las migraciones ejecutadas correctamente

### 3. Modelos Eloquent
- ✅ User (con relaciones y métodos helper)
- ✅ Vehicle (con relaciones)
- ✅ Ride (con relaciones y cálculo de espacios disponibles)
- ✅ Reservation (con relaciones y métodos de estado)

### 4. Seeders
- ✅ UserSeeder creado con admin, driver y passenger de prueba
- ✅ DatabaseSeeder configurado

### 5. Middleware y Autenticación Base
- ✅ Middleware CheckRole creado
- ✅ AuthController con login, register, activate, logout

### 6. Sistema de Correo
- ✅ ActivationMail Mailable creado
- ✅ Vista de email de activación

---

## 🔨 Pendiente de Completar

### 7. Completar Controladores

#### RideController
```php
<?php

namespace App\Http\Controllers;

use App\Models\Ride;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RideController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:driver,admin')->except(['index', 'show']);
    }

    /**
     * Display rides available (public view)
     */
    public function index(Request $request)
    {
        $query = Ride::with(['vehicle', 'user'])
            ->whereRaw("DATETIME(fecha || ' ' || hora) >= DATETIME('now')")
            ->orderBy('fecha')
            ->orderBy('hora');

        // Filters
        if ($request->has('origen') && $request->origen) {
            $query->where('origen', 'like', '%' . $request->origen . '%');
        }

        if ($request->has('destino') && $request->destino) {
            $query->where('destino', 'like', '%' . $request->destino . '%');
        }

        $rides = $query->get()->filter(function ($ride) {
            return $ride->espacios_disponibles > 0;
        });

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'rides' => $rides
            ]);
        }

        return view('rides.index', compact('rides'));
    }

    /**
     * Show form to create ride
     */
    public function create()
    {
        $vehicles = Auth::user()->vehicles;
        return view('rides.create', compact('vehicles'));
    }

    /**
     * Store new ride
     */
    public function store(Request $request)
    {
        $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'nombre' => 'required|string|max:150',
            'origen' => 'required|string|max:150',
            'destino' => 'required|string|max:150',
            'fecha' => 'required|date|after_or_equal:today',
            'hora' => 'required',
            'costo' => 'required|numeric|min:0',
            'espacios' => 'required|integer|min:1',
        ]);

        // Verify vehicle belongs to user
        $vehicle = Vehicle::where('id', $request->vehicle_id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $ride = Ride::create([
            'user_id' => Auth::id(),
            'vehicle_id' => $request->vehicle_id,
            'nombre' => $request->nombre,
            'origen' => $request->origen,
            'destino' => $request->destino,
            'fecha' => $request->fecha,
            'hora' => $request->hora,
            'costo' => $request->costo,
            'espacios' => min($request->espacios, $vehicle->capacidad),
        ]);

        return redirect()->route('dashboard')
            ->with('success', 'Ride creado exitosamente');
    }

    /**
     * Show ride details
     */
    public function show(Ride $ride)
    {
        $ride->load(['vehicle', 'user', 'reservations.passenger']);
        return view('rides.show', compact('ride'));
    }

    /**
     * Show form to edit ride
     */
    public function edit(Ride $ride)
    {
        $this->authorize('update', $ride);
        $vehicles = Auth::user()->vehicles;
        return view('rides.edit', compact('ride', 'vehicles'));
    }

    /**
     * Update ride
     */
    public function update(Request $request, Ride $ride)
    {
        $this->authorize('update', $ride);

        $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'nombre' => 'required|string|max:150',
            'origen' => 'required|string|max:150',
            'destino' => 'required|string|max:150',
            'fecha' => 'required|date',
            'hora' => 'required',
            'costo' => 'required|numeric|min:0',
            'espacios' => 'required|integer|min:1',
        ]);

        $ride->update($request->all());

        return redirect()->route('dashboard')
            ->with('success', 'Ride actualizado exitosamente');
    }

    /**
     * Delete ride
     */
    public function destroy(Ride $ride)
    {
        $this->authorize('delete', $ride);
        $ride->delete();

        return redirect()->route('dashboard')
            ->with('success', 'Ride eliminado exitosamente');
    }
}
```

#### VehicleController
```php
<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class VehicleController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:driver,admin');
    }

    /**
     * Display user's vehicles
     */
    public function index()
    {
        $vehicles = Auth::user()->vehicles;
        return view('vehicles.index', compact('vehicles'));
    }

    /**
     * Show form to create vehicle
     */
    public function create()
    {
        return view('vehicles.create');
    }

    /**
     * Store new vehicle
     */
    public function store(Request $request)
    {
        $request->validate([
            'placa' => 'required|string|max:50',
            'color' => 'nullable|string|max:50',
            'marca' => 'nullable|string|max:100',
            'modelo' => 'nullable|string|max:100',
            'anio' => 'nullable|integer|min:1900|max:' . (date('Y') + 1),
            'capacidad' => 'required|integer|min:1|max:50',
            'foto' => 'nullable|image|max:2048',
        ]);

        $data = $request->all();
        $data['user_id'] = Auth::id();

        if ($request->hasFile('foto')) {
            $data['foto'] = $request->file('foto')->store('vehicles', 'public');
        }

        Vehicle::create($data);

        return redirect()->route('vehicles.index')
            ->with('success', 'Vehículo registrado exitosamente');
    }

    /**
     * Show form to edit vehicle
     */
    public function edit(Vehicle $vehicle)
    {
        $this->authorize('update', $vehicle);
        return view('vehicles.edit', compact('vehicle'));
    }

    /**
     * Update vehicle
     */
    public function update(Request $request, Vehicle $vehicle)
    {
        $this->authorize('update', $vehicle);

        $request->validate([
            'placa' => 'required|string|max:50',
            'color' => 'nullable|string|max:50',
            'marca' => 'nullable|string|max:100',
            'modelo' => 'nullable|string|max:100',
            'anio' => 'nullable|integer|min:1900|max:' . (date('Y') + 1),
            'capacidad' => 'required|integer|min:1|max:50',
            'foto' => 'nullable|image|max:2048',
        ]);

        $data = $request->all();

        if ($request->hasFile('foto')) {
            // Delete old photo
            if ($vehicle->foto) {
                Storage::disk('public')->delete($vehicle->foto);
            }
            $data['foto'] = $request->file('foto')->store('vehicles', 'public');
        }

        $vehicle->update($data);

        return redirect()->route('vehicles.index')
            ->with('success', 'Vehículo actualizado exitosamente');
    }

    /**
     * Delete vehicle
     */
    public function destroy(Vehicle $vehicle)
    {
        $this->authorize('delete', $vehicle);
        
        if ($vehicle->foto) {
            Storage::disk('public')->delete($vehicle->foto);
        }

        $vehicle->delete();

        return redirect()->route('vehicles.index')
            ->with('success', 'Vehículo eliminado exitosamente');
    }
}
```

#### ReservationController
```php
<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Ride;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\ReservationStatusMail;

class ReservationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display user's reservations
     */
    public function index()
    {
        if (Auth::user()->isPassenger()) {
            // Passenger sees their reservations
            $reservations = Auth::user()->reservations()
                ->with(['ride.vehicle', 'ride.user'])
                ->orderBy('created_at', 'desc')
                ->get();
        } else {
            // Driver/Admin sees reservations for their rides
            $reservations = Reservation::whereHas('ride', function ($query) {
                $query->where('user_id', Auth::id());
            })
            ->with(['ride', 'passenger'])
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->get();
        }

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'reservations' => $reservations
            ]);
        }

        return view('reservations.index', compact('reservations'));
    }

    /**
     * Create new reservation (passenger only)
     */
    public function store(Request $request)
    {
        if (!Auth::user()->isPassenger()) {
            return response()->json([
                'success' => false,
                'error' => 'Solo pasajeros pueden hacer reservaciones'
            ], 403);
        }

        $request->validate([
            'ride_id' => 'required|exists:rides,id',
            'seats' => 'required|integer|min:1',
        ]);

        $ride = Ride::findOrFail($request->ride_id);

        // Check if ride has available spaces
        if (!$ride->hasAvailableSpaces($request->seats)) {
            return back()->withErrors([
                'error' => 'No hay suficientes espacios disponibles'
            ]);
        }

        // Check if user already has a reservation for this ride
        $existingReservation = Reservation::where('ride_id', $request->ride_id)
            ->where('passenger_id', Auth::id())
            ->whereIn('status', ['pending', 'accepted'])
            ->first();

        if ($existingReservation) {
            return back()->withErrors([
                'error' => 'Ya tienes una reservación para este ride'
            ]);
        }

        $reservation = Reservation::create([
            'ride_id' => $request->ride_id,
            'passenger_id' => Auth::id(),
            'seats' => $request->seats,
            'status' => 'pending',
        ]);

        // Notify driver
        try {
            Mail::to($ride->user->email)->send(
                new ReservationStatusMail($reservation, 'created')
            );
        } catch (\Exception $e) {
            // Continue even if email fails
        }

        return redirect()->route('reservations.index')
            ->with('success', 'Reservación creada. Espera la confirmación del conductor.');
    }

    /**
     * Accept reservation (driver only)
     */
    public function accept(Reservation $reservation)
    {
        $this->authorize('update', $reservation);

        $reservation->update(['status' => 'accepted']);

        // Notify passenger
        try {
            Mail::to($reservation->passenger->email)->send(
                new ReservationStatusMail($reservation, 'accepted')
            );
        } catch (\Exception $e) {
            // Continue
        }

        return back()->with('success', 'Reservación aceptada');
    }

    /**
     * Reject reservation (driver only)
     */
    public function reject(Reservation $reservation)
    {
        $this->authorize('update', $reservation);

        $reservation->update(['status' => 'rejected']);

        // Notify passenger
        try {
            Mail::to($reservation->passenger->email)->send(
                new ReservationStatusMail($reservation, 'rejected')
            );
        } catch (\Exception $e) {
            // Continue
        }

        return back()->with('success', 'Reservación rechazada');
    }

    /**
     * Cancel reservation (passenger only)
     */
    public function destroy(Reservation $reservation)
    {
        if ($reservation->passenger_id !== Auth::id()) {
            abort(403, 'No autorizado');
        }

        $reservation->update(['status' => 'cancelled']);

        return back()->with('success', 'Reservación cancelada');
    }
}
```

#### AdminController
```php
<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Ride;
use App\Models\Reservation;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:admin');
    }

    /**
     * Show admin dashboard
     */
    public function index()
    {
        $stats = [
            'total_users' => User::count(),
            'total_rides' => Ride::count(),
            'total_reservations' => Reservation::count(),
            'total_vehicles' => Vehicle::count(),
            'pending_users' => User::where('status', 'pending')->count(),
            'pending_reservations' => Reservation::where('status', 'pending')->count(),
        ];

        return view('admin.dashboard', compact('stats'));
    }

    /**
     * Manage users
     */
    public function users()
    {
        $users = User::orderBy('created_at', 'desc')->get();
        return view('admin.users', compact('users'));
    }

    /**
     * Update user status
     */
    public function updateUserStatus(Request $request, User $user)
    {
        $request->validate([
            'status' => 'required|in:pending,active,inactive'
        ]);

        $user->update(['status' => $request->status]);

        return back()->with('success', 'Estado del usuario actualizado');
    }

    /**
     * Delete user
     */
    public function deleteUser(User $user)
    {
        if ($user->role === 'admin') {
            return back()->withErrors(['error' => 'No se puede eliminar un administrador']);
        }

        $user->delete();
        return back()->with('success', 'Usuario eliminado');
    }

    /**
     * View all rides
     */
    public function rides()
    {
        $rides = Ride::with(['user', 'vehicle'])->orderBy('fecha', 'desc')->get();
        return view('admin.rides', compact('rides'));
    }

    /**
     * View all reservations
     */
    public function reservations()
    {
        $reservations = Reservation::with(['ride', 'passenger'])
            ->orderBy('created_at', 'desc')
            ->get();
        return view('admin.reservations', compact('reservations'));
    }
}
```

### 8. Crear Policies para Autorización

```bash
php artisan make:policy RidePolicy --model=Ride
php artisan make:policy VehiclePolicy --model=Vehicle
php artisan make:policy ReservationPolicy --model=Reservation
```

**RidePolicy.php:**
```php
<?php

namespace App\Policies;

use App\Models\Ride;
use App\Models\User;

class RidePolicy
{
    public function update(User $user, Ride $ride): bool
    {
        return $user->id === $ride->user_id || $user->isAdmin();
    }

    public function delete(User $user, Ride $ride): bool
    {
        return $user->id === $ride->user_id || $user->isAdmin();
    }
}
```

**VehiclePolicy.php:**
```php
<?php

namespace App\Policies;

use App\Models\Vehicle;
use App\Models\User;

class VehiclePolicy
{
    public function update(User $user, Vehicle $vehicle): bool
    {
        return $user->id === $vehicle->user_id || $user->isAdmin();
    }

    public function delete(User $user, Vehicle $vehicle): bool
    {
        return $user->id === $vehicle->user_id || $user->isAdmin();
    }
}
```

**ReservationPolicy.php:**
```php
<?php

namespace App\Policies;

use App\Models\Reservation;
use App\Models\User;

class ReservationPolicy
{
    public function update(User $user, Reservation $reservation): bool
    {
        // Solo el conductor del ride puede aceptar/rechazar
        return $user->id === $reservation->ride->user_id || $user->isAdmin();
    }
}
```

### 9. Configurar Rutas

**routes/web.php:**
```php
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RideController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\AdminController;

// Public routes
Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::get('/activate/{token}', [AuthController::class, 'activate'])->name('activate');

// Protected routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // Rides
    Route::resource('rides', RideController::class);
    
    // Vehicles (Driver only)
    Route::resource('vehicles', VehicleController::class)
        ->middleware('role:driver,admin');
    
    // Reservations
    Route::resource('reservations', ReservationController::class);
    Route::post('/reservations/{reservation}/accept', [ReservationController::class, 'accept'])
        ->name('reservations.accept');
    Route::post('/reservations/{reservation}/reject', [ReservationController::class, 'reject'])
        ->name('reservations.reject');
    
    // Admin routes
    Route::prefix('admin')->middleware('role:admin')->group(function () {
        Route::get('/', [AdminController::class, 'index'])->name('admin.dashboard');
        Route::get('/users', [AdminController::class, 'users'])->name('admin.users');
        Route::patch('/users/{user}/status', [AdminController::class, 'updateUserStatus'])
            ->name('admin.users.status');
        Route::delete('/users/{user}', [AdminController::class, 'deleteUser'])
            ->name('admin.users.delete');
        Route::get('/rides', [AdminController::class, 'rides'])->name('admin.rides');
        Route::get('/reservations', [AdminController::class, 'reservations'])
            ->name('admin.reservations');
    });
});

// API routes for AJAX
Route::get('/api/auth/user', [AuthController::class, 'getCurrentUser'])
    ->name('api.auth.user');
```

**routes/api.php:**
```php
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RideController;
use App\Http\Controllers\ReservationController;

// Public API
Route::get('/rides', [RideController::class, 'index']);

// Protected API
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/reservations', [ReservationController::class, 'index']);
    Route::post('/reservations', [ReservationController::class, 'store']);
});
```

### 10. Registrar Middleware en bootstrap/app.php

```php
<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'role' => \App\Http\Middleware\CheckRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
```

### 11. Crear Comando Artisan para Notificaciones

```bash
php artisan make:command NotifyPendingReservations
```

**app/Console/Commands/NotifyPendingReservations.php:**
```php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\PendingReservationReminderMail;

class NotifyPendingReservations extends Command
{
    protected $signature = 'reservations:notify-pending {minutes=30}';
    protected $description = 'Notifica sobre reservaciones pendientes por más de X minutos';

    public function handle()
    {
        $minutes = $this->argument('minutes');
        $threshold = Carbon::now()->subMinutes($minutes);

        $reservations = Reservation::where('status', 'pending')
            ->where('created_at', '<=', $threshold)
            ->with(['ride.user', 'passenger'])
            ->get();

        $count = 0;
        foreach ($reservations as $reservation) {
            try {
                Mail::to($reservation->ride->user->email)->send(
                    new PendingReservationReminderMail($reservation)
                );
                $count++;
            } catch (\Exception $e) {
                $this->error("Error enviando email para reservación {$reservation->id}");
            }
        }

        $this->info("Se enviaron {$count} notificaciones.");
        return 0;
    }
}
```

### 12. Crear Mailables Adicionales

```bash
php artisan make:mail ReservationStatusMail
php artisan make:mail PendingReservationReminderMail
```

### 13. Crear Vistas Blade Base

**resources/views/layouts/app.blade.php:**
```blade
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Licu Rides')</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    @stack('styles')
</head>
<body>
    @include('layouts.navigation')
    
    <main>
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        
        @if($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        
        @yield('content')
    </main>
    
    <script src="{{ asset('js/app.js') }}"></script>
    @stack('scripts')
</body>
</html>
```

### 14. Copiar Assets (CSS, JS, Imágenes)

```bash
# Copiar desde el proyecto original
cp -r CSS/* licu-rides-laravel/public/css/
cp -r JS/* licu-rides-laravel/public/js/
cp -r imagenes/* licu-rides-laravel/public/images/
```

### 15. Configurar .env para Correos

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=tu-email@gmail.com
MAIL_PASSWORD=tu-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@licurides.com
MAIL_FROM_NAME="Licu Rides"
```

### 16. Crear Tests Unitarios

```bash
php artisan make:test UserTest
php artisan make:test RideTest
php artisan make:test ReservationTest
php artisan make:test VehicleTest
```

**tests/Feature/UserTest.php:**
```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register(): void
    {
        $response = $this->post('/register', [
            'nombre' => 'Juan',
            'apellido' => 'Pérez',
            'cedula' => '123456789',
            'email' => 'juan@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'passenger',
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'juan@example.com',
            'status' => 'pending',
        ]);
    }

    public function test_user_can_login(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'status' => 'active',
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect('/dashboard');
    }

    public function test_pending_user_cannot_login(): void
    {
        $user = User::factory()->create([
            'email' => 'pending@example.com',
            'password' => bcrypt('password'),
            'status' => 'pending',
        ]);

        $response = $this->post('/login', [
            'email' => 'pending@example.com',
            'password' => 'password',
        ]);

        $this->assertGuest();
    }
}
```

**tests/Feature/RideTest.php:**
```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\Ride;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RideTest extends TestCase
{
    use RefreshDatabase;

    public function test_driver_can_create_ride(): void
    {
        $driver = User::factory()->create(['role' => 'driver', 'status' => 'active']);
        $vehicle = Vehicle::factory()->create(['user_id' => $driver->id]);

        $response = $this->actingAs($driver)->post('/rides', [
            'vehicle_id' => $vehicle->id,
            'nombre' => 'Test Ride',
            'origen' => 'San José',
            'destino' => 'Cartago',
            'fecha' => now()->addDays(1)->format('Y-m-d'),
            'hora' => '08:00',
            'costo' => 2000,
            'espacios' => 3,
        ]);

        $this->assertDatabaseHas('rides', [
            'nombre' => 'Test Ride',
            'user_id' => $driver->id,
        ]);
    }

    public function test_passenger_cannot_create_ride(): void
    {
        $passenger = User::factory()->create(['role' => 'passenger', 'status' => 'active']);

        $response = $this->actingAs($passenger)->post('/rides', [
            'nombre' => 'Test Ride',
            'origen' => 'San José',
            'destino' => 'Cartago',
        ]);

        $response->assertStatus(403);
    }

    public function test_ride_calculates_available_spaces(): void
    {
        $ride = Ride::factory()->create(['espacios' => 4]);
        
        $this->assertEquals(4, $ride->espacios_disponibles);
    }
}
```

### 17. Ejecutar Tests

```bash
php artisan test
```

---

## 📋 Comandos Útiles

```bash
# Ejecutar migraciones
php artisan migrate

# Rehacer migraciones con seeders
php artisan migrate:fresh --seed

# Crear enlace simbólico para storage
php artisan storage:link

# Limpiar cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# Ver rutas
php artisan route:list

# Ejecutar servidor de desarrollo
php artisan serve

# Ejecutar tests
php artisan test

# Ejecutar comando personalizado
php artisan reservations:notify-pending 30
```

---

## 🎯 Checklist Final

- [ ] Completar todos los controladores
- [ ] Crear todas las policies
- [ ] Configurar rutas completas
- [ ] Crear vistas Blade para todas las páginas
- [ ] Copiar y adaptar CSS/JS del proyecto original
- [ ] Configurar correo electrónico (SMTP)
- [ ] Crear todos los Mailables
- [ ] Crear comando Artisan para notificaciones
- [ ] Escribir tests unitarios
- [ ] Ejecutar y verificar tests
- [ ] Documentar API endpoints
- [ ] Crear README.md actualizado
- [ ] Configurar .env.example
- [ ] Probar flujo completo de usuario

---

## 📝 Notas Adicionales

1. **Base de Datos**: Actualmente usa SQLite para desarrollo. Para producción, cambia a MySQL en `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=licu_rides
DB_USERNAME=root
DB_PASSWORD=
```

2. **Autenticación**: El proyecto usa autenticación nativa de Laravel con sesiones. No instalamos Breeze para mantener control total.

3. **Almacenamiento de Archivos**: Las fotos se guardan en `storage/app/public`. Ejecuta:
```bash
php artisan storage:link
```

4. **Migraciones vs Proyecto Original**: Las migraciones mantienen la misma estructura que `init.sql` pero usando Eloquent.

5. **Testing**: PHPUnit viene configurado con Laravel. Los tests usan `RefreshDatabase` para limpiar la BD entre tests.

---

## 🚀 Próximos Pasos Recomendados

1. Implementar todos los controladores faltantes
2. Crear las vistas Blade
3. Migrar los CSS y JS
4. Configurar el sistema de correos
5. Escribir tests completos
6. Documentar la API
7. Deploy a producción

---

¡La base del proyecto está lista! Ahora tienes una estructura profesional con Laravel siguiendo MVC, migraciones, seeders, y todo preparado para completar la migración. 🎉
