<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    public function index()
    {
        return view('settings');
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'apellido' => 'required|string|max:255',
            'cedula' => 'required|string|max:20',
            'fecha_nacimiento' => 'nullable|date',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'telefono' => 'required|string|max:20',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'current_password' => 'nullable|required_with:new_password',
            'new_password' => 'nullable|min:8|confirmed',
        ]);

        // Remover campos de contraseña del array validated
        unset($validated['current_password']);
        unset($validated['new_password']);
        
        // Actualizar foto si se subió una nueva
        if ($request->hasFile('foto')) {
            // Eliminar foto anterior si existe
            if ($user->foto) {
                Storage::disk('public')->delete($user->foto);
            }
            $validated['foto'] = $request->file('foto')->store('users', 'public');
        }

        // Verificar y cambiar contraseña solo si se proporcionaron todos los campos
        if ($request->filled('current_password') && $request->filled('new_password')) {
            if (!Hash::check($request->current_password, $user->password)) {
                return back()->withErrors(['current_password' => 'La contraseña actual no es correcta'])->withInput();
            }
            // Agregar la nueva contraseña hasheada
            $validated['password'] = Hash::make($request->new_password);
        }

        $user->update($validated);
        
        // Refrescar el usuario en la sesión
        $user->refresh();

        return back()->with('success', 'Datos actualizados exitosamente');
    }
}
