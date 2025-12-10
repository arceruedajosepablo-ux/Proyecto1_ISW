<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Licu Rides</title>
    <link rel="stylesheet" href="{{ asset('css/styleLogin.css') }}">
    <link rel="icon" href="{{ asset('images/icon.png') }}">
</head>
<body>
    <div class="container">
        <img src="{{ asset('images/logo.png') }}" alt="Logo de Licu Rides" class="logo">
        
        @if(session('success'))
            <div style="background: #d4edda; color: #155724; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1rem; width: 100%;">
                {{ session('success') }}
            </div>
        @endif

        @if(session('warning'))
            <div style="background: #fff3cd; color: #856404; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1rem; width: 100%;">
                {{ session('warning') }}
            </div>
        @endif

        @if($errors->any())
            <div style="background: #f8d7da; color: #721c24; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1rem; width: 100%;">
                @foreach($errors->all() as $error)
                    <p style="margin: 0;">{{ $error }}</p>
                @endforeach
            </div>
        @endif
        
        <form action="{{ route('login') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="email">Correo Electrónico:</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required>
            </div>
            <div class="form-group">
                <label for="password">Contraseña:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit">Iniciar Sesión</button>
            <p>¿No tienes una cuenta? <a href="{{ route('register') }}">Regístrate aquí</a></p>
        </form>
    </div>
</body>
</html>
