<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class VehicleController extends Controller
{
    public function index()
    {
        $vehicles = Auth::user()->vehicles;
        return view('vehicles.index', compact('vehicles'));
    }

    public function create()
    {
        return view('vehicles.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'placa' => 'required|string|max:20|unique:vehicles',
            'marca' => 'required|string|max:100',
            'modelo' => 'required|string|max:100',
            'ano' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'color' => 'required|string|max:50',
            'capacidad' => 'required|integer|min:1|max:50',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $validated['user_id'] = Auth::id();

        if ($request->hasFile('foto')) {
            $validated['foto'] = $request->file('foto')->store('vehicles', 'public');
        }

        Vehicle::create($validated);

        return redirect()->route('vehicles.index')
            ->with('success', 'Vehículo registrado exitosamente');
    }

    public function edit(Vehicle $vehicle)
    {
        // Verificar que el usuario es el dueño del vehículo
        if ($vehicle->user_id !== Auth::id()) {
            abort(403, 'No autorizado');
        }

        return view('vehicles.edit', compact('vehicle'));
    }

    public function update(Request $request, Vehicle $vehicle)
    {
        // Verificar que el usuario es el dueño del vehículo
        if ($vehicle->user_id !== Auth::id()) {
            abort(403, 'No autorizado');
        }

        $validated = $request->validate([
            'placa' => 'required|string|max:20|unique:vehicles,placa,' . $vehicle->id,
            'marca' => 'required|string|max:100',
            'modelo' => 'required|string|max:100',
            'ano' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'color' => 'required|string|max:50',
            'capacidad' => 'required|integer|min:1|max:50',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        if ($request->hasFile('foto')) {
            // Eliminar foto anterior si existe
            if ($vehicle->foto) {
                Storage::disk('public')->delete($vehicle->foto);
            }
            $validated['foto'] = $request->file('foto')->store('vehicles', 'public');
        }

        $vehicle->update($validated);

        return redirect()->route('vehicles.index')
            ->with('success', 'Vehículo actualizado exitosamente');
    }

    public function destroy(Vehicle $vehicle)
    {
        // Verificar que el usuario es el dueño del vehículo
        if ($vehicle->user_id !== Auth::id()) {
            abort(403, 'No autorizado');
        }

        // Eliminar foto si existe
        if ($vehicle->foto) {
            Storage::disk('public')->delete($vehicle->foto);
        }

        $vehicle->delete();

        return redirect()->route('vehicles.index')
            ->with('success', 'Vehículo eliminado exitosamente');
    }
}
