<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RideController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\SettingsController;

// Página de inicio - muestra rides públicos
Route::get('/', [RideController::class, 'index'])->name('home');

// Rutas de autenticación
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/activate/{token}', [AuthController::class, 'activate'])->name('activate');

// Rutas protegidas (requieren autenticación)
Route::middleware('auth')->group(function () {
    // Dashboard
    Route::get('/dashboard', function (\Illuminate\Http\Request $request) {
        $user = auth()->user();
        
        if ($user->isDriver() || $user->isAdmin()) {
            // Mostrar los rides del conductor con filtros
            $query = \App\Models\Ride::where('user_id', $user->id)
                ->with(['vehicle', 'reservations']);
            
            // Aplicar filtros
            if ($request->filled('fecha')) {
                $query->whereDate('fecha', $request->fecha);
            }
            
            if ($request->filled('origen')) {
                $query->where('origen', 'like', '%' . $request->origen . '%');
            }
            
            if ($request->filled('destino')) {
                $query->where('destino', 'like', '%' . $request->destino . '%');
            }
            
            // Aplicar ordenamiento
            $sortBy = $request->input('sortBy', 'fecha');
            $order = $request->input('order', 'desc');
            
            if ($sortBy == 'fecha') {
                if ($order == 'desc') {
                    $query->orderBy('fecha', 'desc')->orderBy('hora', 'desc');
                } else {
                    $query->orderBy('fecha', 'asc')->orderBy('hora', 'asc');
                }
            } else {
                $query->orderBy($sortBy, $order);
            }
            
            $rides = $query->get();
            
            // Obtener reservas pendientes para los rides del conductor
            $pendingReservations = \App\Models\Reservation::with(['passenger', 'ride'])
                ->whereHas('ride', function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                })
                ->where('status', 'pending')
                ->orderBy('created_at', 'desc')
                ->get();
        } else {
            // Mostrar rides disponibles para pasajeros
            $query = \App\Models\Ride::with(['user', 'vehicle'])
                ->where('fecha', '>=', now());
            
            // Aplicar filtros
            if ($request->filled('fecha')) {
                $query->whereDate('fecha', $request->fecha);
            }
            
            if ($request->filled('origen')) {
                $query->where('origen', 'like', '%' . $request->origen . '%');
            }
            
            if ($request->filled('destino')) {
                $query->where('destino', 'like', '%' . $request->destino . '%');
            }
            
            // Aplicar ordenamiento
            $sortBy = $request->input('sortBy', 'fecha');
            $order = $request->input('order', 'asc');
            
            if ($sortBy == 'fecha') {
                if ($order == 'desc') {
                    $query->orderBy('fecha', 'desc')->orderBy('hora', 'desc');
                } else {
                    $query->orderBy('fecha', 'asc')->orderBy('hora', 'asc');
                }
            } else {
                $query->orderBy($sortBy, $order);
            }
            
            $rides = $query->get();
            $pendingReservations = collect(); // Colección vacía para pasajeros
        }
        
        return view('dashboard', compact('rides', 'pendingReservations'));
    })->name('dashboard');

    // Rides - Todos pueden ver
    Route::get('/rides', [RideController::class, 'index'])->name('rides.index');

    // Rides - Solo conductores pueden crear (estas rutas deben ir ANTES de la ruta con parámetro {ride})
    Route::middleware('role:driver')->group(function () {
        Route::get('/rides/create', [RideController::class, 'create'])->name('rides.create');
        Route::post('/rides', [RideController::class, 'store'])->name('rides.store');
        Route::get('/rides/{ride}/edit', [RideController::class, 'edit'])->name('rides.edit');
        Route::put('/rides/{ride}', [RideController::class, 'update'])->name('rides.update');
        Route::delete('/rides/{ride}', [RideController::class, 'destroy'])->name('rides.destroy');
    });
    
    // Esta ruta debe ir DESPUÉS de /rides/create para evitar conflictos
    Route::get('/rides/{ride}', [RideController::class, 'show'])->name('rides.show');

    // Vehículos - Solo conductores
    Route::middleware('role:driver')->group(function () {
        Route::get('/vehicles', [VehicleController::class, 'index'])->name('vehicles.index');
        Route::get('/vehicles/create', [VehicleController::class, 'create'])->name('vehicles.create');
        Route::post('/vehicles', [VehicleController::class, 'store'])->name('vehicles.store');
        Route::get('/vehicles/{vehicle}/edit', [VehicleController::class, 'edit'])->name('vehicles.edit');
        Route::put('/vehicles/{vehicle}', [VehicleController::class, 'update'])->name('vehicles.update');
        Route::delete('/vehicles/{vehicle}', [VehicleController::class, 'destroy'])->name('vehicles.destroy');
    });

    // Reservas
    Route::get('/reservations', [ReservationController::class, 'index'])->name('reservations.index');
    Route::post('/reservations', [ReservationController::class, 'store'])->name('reservations.store');
    Route::delete('/reservations/{reservation}', [ReservationController::class, 'destroy'])->name('reservations.destroy');
    
    // Aceptar/Rechazar reservas (solo el conductor del ride)
    Route::post('/reservations/{reservation}/accept', [ReservationController::class, 'accept'])->name('reservations.accept');
    Route::post('/reservations/{reservation}/reject', [ReservationController::class, 'reject'])->name('reservations.reject');

    // Settings - Configuración de cuenta
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings');
    Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');

    // Panel de administración - Solo admin
    Route::middleware('role:admin')->prefix('admin')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
        
        // Gestión de usuarios
        Route::get('/users', [AdminController::class, 'users'])->name('admin.users');
        Route::post('/users/{user}/activate', [AdminController::class, 'activateUser'])->name('admin.users.activate');
        Route::patch('/users/{user}/status', [AdminController::class, 'updateUserStatus'])->name('admin.users.updateStatus');
        Route::delete('/users/{user}', [AdminController::class, 'deleteUser'])->name('admin.users.destroy');
        
        // Gestión de rides
        Route::get('/rides', [AdminController::class, 'rides'])->name('admin.rides');
        Route::delete('/rides/{ride}', [AdminController::class, 'deleteRide'])->name('admin.rides.destroy');
        
        // Gestión de reservas
        Route::get('/reservations', [AdminController::class, 'reservations'])->name('admin.reservations');
        Route::delete('/reservations/{reservation}', [AdminController::class, 'deleteReservation'])->name('admin.reservations.destroy');
    });
});
