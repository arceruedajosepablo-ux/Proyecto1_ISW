<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\ActivationMail;

class AuthController extends Controller
{
    /**
     * Show login form
     */
    public function showLogin()
    {
        return view('auth.login');
    }

    /**
     * Show registration form
     */
    public function showRegister()
    {
        return view('auth.register');
    }

    /**
     * Handle login request
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return back()->withErrors([
                'email' => 'Las credenciales no son correctas.',
            ])->withInput();
        }

        if ($user->status !== 'active') {
            return back()->withErrors([
                'email' => 'Tu cuenta no está activa. Por favor revisa tu correo.',
            ])->withInput();
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended('/dashboard');
    }

    /**
     * Handle registration request
     */
    public function register(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:100',
            'apellido' => 'required|string|max:100',
            'cedula' => 'required|string|max:50|unique:users',
            'email' => 'required|email|max:150|unique:users',
            'password' => 'required|min:6|confirmed',
            'telefono' => 'nullable|string|max:50',
            'fecha_nacimiento' => 'nullable|date',
            'role' => 'required|in:driver,passenger',
        ]);

        $activationToken = Str::random(60);

        $user = User::create([
            'nombre' => $request->nombre,
            'apellido' => $request->apellido,
            'cedula' => $request->cedula,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'telefono' => $request->telefono,
            'fecha_nacimiento' => $request->fecha_nacimiento,
            'role' => $request->role,
            'status' => 'pending',
            'activation_token' => $activationToken,
        ]);

        // Send activation email
        try {
            Mail::to($user->email)->send(new ActivationMail($user, $activationToken));
            
            return redirect()->route('login')
                ->with('success', '¡Registro exitoso! Por favor revisa tu correo para activar tu cuenta.');
        } catch (\Exception $e) {
            return redirect()->route('login')
                ->with('warning', 'Cuenta creada pero no se pudo enviar el correo de activación. Contacta al administrador.');
        }
    }

    /**
     * Activate user account
     */
    public function activate($token)
    {
        $user = User::where('activation_token', $token)->first();

        if (!$user) {
            return redirect()->route('login')
                ->withErrors(['error' => 'Token de activación inválido.']);
        }

        $user->update([
            'status' => 'active',
            'activation_token' => null,
        ]);

        return redirect()->route('login')
            ->with('success', '¡Cuenta activada con éxito! Ya puedes iniciar sesión.');
    }

    /**
     * Handle logout request
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', 'Sesión cerrada correctamente.');
    }

    /**
     * Get current user info (for AJAX requests)
     */
    public function getCurrentUser(Request $request)
    {
        if (Auth::check()) {
            $user = Auth::user();
            return response()->json([
                'logged_in' => true,
                'user_id' => $user->id,
                'role' => $user->role,
                'nombre' => $user->nombre,
                'apellido' => $user->apellido,
            ]);
        }

        return response()->json(['logged_in' => false]);
    }
}
