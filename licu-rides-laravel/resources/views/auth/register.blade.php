<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Usuario - Licu Rides</title>
    <link rel="stylesheet" href="{{ asset('css/styleRegi.css') }}">
    <link rel="icon" href="{{ asset('images/icon.png') }}">
</head>
<body>
    <div class="container">
        <form class="register-form" action="{{ route('register') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <h2>Registro de Usuario</h2>

            @if($errors->any())
                <div style="background: #f8d7da; color: #721c24; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1rem;">
                    <ul style="margin: 0; padding-left: 1.5rem;">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <label for="nombre">Nombre</label>
            <input type="text" id="nombre" name="nombre" value="{{ old('nombre') }}" required>

            <label for="apellido">Apellidos</label>
            <input type="text" id="apellido" name="apellido" value="{{ old('apellido') }}" required>

            <label for="telefono">Teléfono</label>
            <input type="tel" id="telefono" name="telefono" pattern="[0-9]{8,15}" value="{{ old('telefono') }}" required>

            <label for="cedula">Cédula</label>
            <input type="text" id="cedula" name="cedula" placeholder="#-####-####" value="{{ old('cedula') }}" required>

            <label for="fecha_nacimiento">Fecha de Nacimiento</label>
            <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" value="{{ old('fecha_nacimiento') }}">

            <label for="role">Tipo de usuario</label>
            <select id="role" name="role" required>
                <option value="passenger" {{ old('role') == 'passenger' ? 'selected' : '' }}>Pasajero</option>
                <option value="driver" {{ old('role') == 'driver' ? 'selected' : '' }}>Chofer</option>
            </select>

            <label for="email">Correo Electrónico</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}" required>

            <label for="foto">Fotografía</label>
            <input type="file" id="foto" name="foto" accept="image/*">

            <label for="password">Contraseña</label>
            <input type="password" id="password" name="password" required>

            <label for="password_confirmation">Confirmar Contraseña</label>
            <input type="password" id="password_confirmation" name="password_confirmation" required>

            <button type="submit">Registrarse</button>

            <div class="back-to-login">
                <a href="{{ route('login') }}" class="button">Regresar al inicio</a>
            </div>
        </form>
    </div>

    <script src="{{ asset('js/scripts.js') }}"></script>
</body>
</html>
