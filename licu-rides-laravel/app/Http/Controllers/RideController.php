<?php

namespace App\Http\Controllers;

use App\Models\Ride;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RideController extends Controller
{
    public function index(Request $request)
    {
        $query = Ride::with(['user', 'vehicle', 'reservations'])
            ->where('fecha', '>=', now());

        // Filtros de búsqueda
        if ($request->filled('origen')) {
            $query->where('origen', 'like', '%' . $request->origen . '%');
        }

        if ($request->filled('destino')) {
            $query->where('destino', 'like', '%' . $request->destino . '%');
        }

        if ($request->filled('fecha')) {
            $query->whereDate('fecha', $request->fecha);
        }

        // Ordenamiento
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

        return view('rides.index', compact('rides'));
    }

    public function create()
    {
        $vehicles = Auth::user()->vehicles;

        if ($vehicles->isEmpty()) {
            return redirect()->route('vehicles.create')
                ->with('error', 'Primero debes registrar un vehículo');
        }

        return view('rides.create', compact('vehicles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'origen' => 'required|string|max:255',
            'destino' => 'required|string|max:255',
            'fecha' => 'required|date|after_or_equal:today',
            'hora' => 'required',
            'costo' => 'required|numeric|min:0',
            'espacios' => 'required|integer|min:1',
            'vehicle_id' => 'required|exists:vehicles,id'
        ]);

        // Validar que los espacios no excedan la capacidad del vehículo
        $vehicle = \App\Models\Vehicle::findOrFail($validated['vehicle_id']);
        if ($validated['espacios'] > $vehicle->capacidad) {
            return back()->withErrors(['espacios' => 'Los espacios no pueden exceder la capacidad del vehículo (' . $vehicle->capacidad . ' pasajeros)'])->withInput();
        }

        $validated['user_id'] = Auth::id();

        Ride::create($validated);

        return redirect()->route('rides.index')
            ->with('success', 'Ride creado exitosamente');
    }

    public function show(Ride $ride)
    {
        $ride->load(['user', 'vehicle', 'reservations.passenger']);
        return view('rides.show', compact('ride'));
    }

    public function edit(Ride $ride)
    {
        // Verificar que el usuario es el creador del ride
        if ($ride->user_id !== Auth::id()) {
            abort(403, 'No autorizado');
        }

        $vehicles = Auth::user()->vehicles;
        return view('rides.edit', compact('ride', 'vehicles'));
    }

    public function update(Request $request, Ride $ride)
    {
        // Verificar que el usuario es el creador del ride
        if ($ride->user_id !== Auth::id()) {
            abort(403, 'No autorizado');
        }

        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'origen' => 'required|string|max:255',
            'destino' => 'required|string|max:255',
            'fecha' => 'required|date|after_or_equal:today',
            'hora' => 'required',
            'costo' => 'required|numeric|min:0',
            'espacios' => 'required|integer|min:1',
            'vehicle_id' => 'required|exists:vehicles,id'
        ]);

        // Validar que los espacios no excedan la capacidad del vehículo
        $vehicle = \App\Models\Vehicle::findOrFail($validated['vehicle_id']);
        if ($validated['espacios'] > $vehicle->capacidad) {
            return back()->withErrors(['espacios' => 'Los espacios no pueden exceder la capacidad del vehículo (' . $vehicle->capacidad . ' pasajeros)'])->withInput();
        }

        $ride->update($validated);

        return redirect()->route('rides.show', $ride)
            ->with('success', 'Ride actualizado exitosamente');
    }

    public function destroy(Ride $ride)
    {
        // Verificar que el usuario es el creador del ride o es admin
        if ($ride->user_id !== Auth::id() && !Auth::user()->isAdmin()) {
            abort(403, 'No autorizado');
        }

        $ride->delete();

        return redirect()->route('rides.index')
            ->with('success', 'Ride eliminado exitosamente');
    }
}
