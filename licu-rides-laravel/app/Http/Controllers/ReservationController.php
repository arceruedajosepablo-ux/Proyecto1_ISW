<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Ride;
use App\Mail\ReservationNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class ReservationController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->isPassenger()) {
            // Mostrar las reservas del pasajero
            $reservations = Reservation::with(['ride.user', 'ride.vehicle'])
                ->where('passenger_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->get();
        } else {
            // Mostrar las solicitudes de reserva para los rides del conductor
            $reservations = Reservation::with(['passenger', 'ride'])
                ->whereHas('ride', function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                })
                ->orderBy('created_at', 'desc')
                ->get();
        }

        return view('reservations.index', compact('reservations'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'ride_id' => 'required|exists:rides,id',
            'seats' => 'required|integer|min:1'
        ]);

        $ride = Ride::findOrFail($validated['ride_id']);

        // Verificar que haya espacios disponibles
        if (!$ride->hasAvailableSpaces() || $validated['seats'] > $ride->espacios_disponibles) {
            return back()->with('error', 'No hay suficientes espacios disponibles');
        }

        // Verificar que el usuario no sea el conductor
        if ($ride->user_id === Auth::id()) {
            return back()->with('error', 'No puedes reservar tu propio ride');
        }

        // Verificar que no tenga una reserva activa (pending o accepted)
        $existingReservation = Reservation::where('ride_id', $ride->id)
            ->where('passenger_id', Auth::id())
            ->whereIn('status', ['pending', 'accepted'])
            ->first();

        if ($existingReservation) {
            return back()->with('error', 'Ya tienes una reserva activa para este ride');
        }

        $reservation = Reservation::create([
            'ride_id' => $ride->id,
            'passenger_id' => Auth::id(),
            'seats' => $validated['seats'],
            'status' => 'pending'
        ]);

        // Enviar correo al conductor
        Mail::to($ride->user->email)->send(new ReservationNotification($reservation, 'new'));

        return redirect()->route('reservations.index')
            ->with('success', 'Reserva solicitada exitosamente');
    }

    public function accept(Reservation $reservation)
    {
        // Verificar que el usuario es el conductor del ride
        if ($reservation->ride->user_id !== Auth::id()) {
            abort(403, 'No autorizado');
        }

        // Verificar que la reserva esté pendiente
        if ($reservation->status !== 'pending') {
            return back()->with('error', 'Esta reserva ya fue procesada');
        }

        // Verificar que haya espacios disponibles (espacios_disponibles solo cuenta accepted)
        if ($reservation->seats > $reservation->ride->espacios_disponibles) {
            return back()->with('error', 'No hay suficientes espacios disponibles');
        }

        $reservation->update(['status' => 'accepted']);

        // Enviar correo al pasajero
        Mail::to($reservation->passenger->email)->send(new ReservationNotification($reservation, 'accepted'));

        return back()->with('success', 'Reserva aceptada');
    }

    public function reject(Reservation $reservation)
    {
        // Verificar que el usuario es el conductor del ride
        if ($reservation->ride->user_id !== Auth::id()) {
            abort(403, 'No autorizado');
        }

        $reservation->update(['status' => 'rejected']);

        // Enviar correo al pasajero
        Mail::to($reservation->passenger->email)->send(new ReservationNotification($reservation, 'rejected'));

        return back()->with('success', 'Reserva rechazada');
    }

    public function destroy(Reservation $reservation)
    {
        // Verificar que el usuario es el pasajero de la reserva
        if ($reservation->passenger_id !== Auth::id()) {
            abort(403, 'No autorizado');
        }

        // Solo se puede cancelar si está pendiente
        if ($reservation->status !== 'pending') {
            return back()->with('error', 'Solo puedes cancelar reservas pendientes');
        }

        $reservation->update(['status' => 'cancelled']);

        return back()->with('success', 'Reserva cancelada');
    }
}
