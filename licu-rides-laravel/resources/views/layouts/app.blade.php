<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Licu Rides')</title>
    <link rel="stylesheet" href="{{ asset('css/' . ($cssFile ?? 'styleDash.css')) }}">
    <link rel="icon" href="{{ asset('images/icon.png') }}">
    @stack('styles')
</head>
<body>
    @auth
    <div class="container">
        <header>
            <img src="{{ asset('images/logo.png') }}" alt="Logo" class="logo">
            <div class="user-welcome">
                <span>Bienvenido, {{ auth()->user()->nombre }} {{ auth()->user()->apellido }}</span>
                <form action="{{ route('logout') }}" method="POST" style="display: inline;">
                    @csrf
                    <button type="submit" class="logout-btn" style="background: none; border: none; color: #007bff; cursor: pointer; font-size: 1rem;">Cerrar sesión</button>
                </form>
            </div>
        </header>

        <!-- Barra de Navegación -->
        <nav class="navbar">
            <ul>
                <li><a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">Dashboard</a></li>
                <li><a href="{{ route('rides.index') }}" class="{{ request()->routeIs('rides.*') ? 'active' : '' }}">Rides</a></li>
                @if(auth()->user()->isDriver() || auth()->user()->isAdmin())
                <li><a href="{{ route('vehicles.index') }}" class="{{ request()->routeIs('vehicles.*') ? 'active' : '' }}">Vehículos</a></li>
                @endif
                @if(auth()->user()->isAdmin())
                <li><a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.*') ? 'active' : '' }}">Admin</a></li>
                @endif
                <li><a href="{{ route('reservations.index') }}" class="{{ request()->routeIs('reservations.*') ? 'active' : '' }}">{{ auth()->user()->isPassenger() ? 'Mis Reservas' : 'Solicitudes' }}</a></li>
                <li><a href="{{ route('settings') }}" class="{{ request()->routeIs('settings') ? 'active' : '' }}">Settings</a></li>
            </ul>
        </nav>

        @if(session('success'))
            <div class="alert alert-success" style="background: #d4edda; color: #155724; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1rem; border: 1px solid #c3e6cb;">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger" style="background: #f8d7da; color: #721c24; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1rem; border: 1px solid #f5c6cb;">
                {{ session('error') }}
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger" style="background: #f8d7da; color: #721c24; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1rem; border: 1px solid #f5c6cb;">
                <ul style="margin: 0; padding-left: 1.5rem;">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </div>
    @else
        @yield('content')
    @endauth

    <script src="{{ asset('js/scripts.js') }}"></script>
    @stack('scripts')
</body>
</html>
