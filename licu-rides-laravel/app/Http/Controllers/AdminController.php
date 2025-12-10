<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Ride;
use App\Models\Vehicle;
use App\Models\Reservation;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function dashboard()
    {
        $stats = [
            'total_users' => User::count(),
            'active_users' => User::where('status', 'active')->count(),
            'pending_users' => User::where('status', 'pending')->count(),
            'total_rides' => Ride::count(),
            'active_rides' => Ride::where('fecha', '>=', now())->count(),
            'completed_rides' => Ride::where('fecha', '<', now())->count(),
            'total_vehicles' => Vehicle::count(),
            'total_reservations' => Reservation::count(),
            'pending_reservations' => Reservation::where('status', 'pending')->count(),
            'accepted_reservations' => Reservation::where('status', 'accepted')->count(),
        ];

        // Actividad reciente
        $recentActivity = collect();

        // Usuarios recientes
        User::latest()->take(5)->get()->each(function ($user) use (&$recentActivity) {
            $recentActivity->push([
                'type' => 'user',
                'description' => "Nuevo usuario: {$user->nombre} {$user->apellido}",
                'date' => $user->created_at
            ]);
        });

        // Rides recientes
        Ride::latest()->take(5)->get()->each(function ($ride) use (&$recentActivity) {
            $recentActivity->push([
                'type' => 'ride',
                'description' => "Nuevo ride: {$ride->nombre}",
                'date' => $ride->created_at
            ]);
        });

        // Reservas recientes
        Reservation::latest()->take(5)->get()->each(function ($reservation) use (&$recentActivity) {
            $recentActivity->push([
                'type' => 'reservation',
                'description' => "Nueva reserva para: {$reservation->ride->nombre}",
                'date' => $reservation->created_at
            ]);
        });

        $recentActivity = $recentActivity->sortByDesc('date')->take(10);

        return view('admin.dashboard', compact('stats', 'recentActivity'));
    }

    public function users()
    {
        $users = User::orderBy('created_at', 'desc')->get();
        return view('admin.users', compact('users'));
    }

    public function activateUser(User $user)
    {
        $user->update(['status' => 'active']);
        return back()->with('success', 'Usuario activado exitosamente');
    }

    public function updateUserStatus(User $user, Request $request)
    {
        $request->validate([
            'status' => 'required|in:active,inactive,pending'
        ]);

        $user->update(['status' => $request->status]);
        
        $statusLabels = [
            'active' => 'activado',
            'inactive' => 'desactivado',
            'pending' => 'marcado como pendiente'
        ];
        
        return back()->with('success', 'Usuario ' . $statusLabels[$request->status] . ' exitosamente');
    }

    public function deleteUser(User $user)
    {
        $user->delete();
        return back()->with('success', 'Usuario eliminado exitosamente');
    }

    public function rides()
    {
        $rides = Ride::with(['user', 'vehicle'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        return view('admin.rides', compact('rides'));
    }

    public function deleteRide(Ride $ride)
    {
        $ride->delete();
        return back()->with('success', 'Ride eliminado exitosamente');
    }

    public function reservations()
    {
        $reservations = Reservation::with(['passenger', 'ride.user'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        return view('admin.reservations', compact('reservations'));
    }

    public function deleteReservation(Reservation $reservation)
    {
        $reservation->delete();
        return back()->with('success', 'Reserva eliminada exitosamente');
    }
}
