<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Licu Rides</title>
    <link rel="stylesheet" href="{{ asset('CSS/styleIndex.css') }}">
    <link rel="icon" href="{{ asset('imagenes/icon.png') }}">
</head>
<body>
    <div class="container">
        <header>
            <img src="{{ asset('imagenes/logo.png') }}" alt="Licu Rides Logo" class="logo">
            <h1>Bienvenidos a LicuRides.com</h1>
            <div id="userArea">
                @auth
                    <a href="{{ route('dashboard') }}" class="login-button">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="login-button">Inicio de sesión</a>
                @endauth
            </div>
        </header>
        
        <div class="search-section">
            <h2>Búsqueda de Rides</h2>
            <form method="GET" action="{{ route('rides.index') }}" class="search-form">
                <div class="form-group">
                    <label for="origen">Origen</label>
                    <input type="text" id="origen" name="origen" value="{{ request('origen') }}" placeholder="¿Desde dónde sales?">
                </div>
                <div class="form-group">
                    <label for="destino">Destino</label>
                    <input type="text" id="destino" name="destino" value="{{ request('destino') }}" placeholder="¿A dónde vas?">
                </div>
                <button type="submit">Buscar Rides</button>
            </form>
        </div>
        
        <div class="results-section">
            <div class="results-header">
                <h3>Rides Disponibles</h3>
                <div class="sort-controls">
                    Ordenar por: 
                    <form method="GET" action="{{ route('rides.index') }}" style="display: inline;">
                        <input type="hidden" name="origen" value="{{ request('origen') }}">
                        <input type="hidden" name="destino" value="{{ request('destino') }}">
                        <select id="sortBy" name="sortBy" onchange="this.form.submit()">
                            <option value="fecha" {{ request('sortBy', 'fecha') == 'fecha' ? 'selected' : '' }}>Fecha</option>
                            <option value="origen" {{ request('sortBy') == 'origen' ? 'selected' : '' }}>Origen</option>
                            <option value="destino" {{ request('sortBy') == 'destino' ? 'selected' : '' }}>Destino</option>
                        </select>
                    </form>
                </div>
            </div>

            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Ride</th>
                            <th>Origen</th>
                            <th>Destino</th>
                            <th>Vehículo</th>
                            <th>Fecha y Hora</th>
                            <th>Espacios</th>
                            <th>Costo</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody id="ridesBody">
                        @forelse($rides as $ride)
                            <tr>
                                <td>{{ $ride->nombre }}</td>
                                <td>{{ $ride->origen }}</td>
                                <td>{{ $ride->destino }}</td>
                                <td>{{ $ride->vehicle->marca }} {{ $ride->vehicle->modelo }}</td>
                                <td>{{ $ride->fecha->format('d/m/Y') }} {{ \Carbon\Carbon::parse($ride->hora)->format('H:i') }}</td>
                                <td>{{ $ride->espacios_disponibles }} / {{ $ride->espacios }}</td>
                                <td>₡{{ number_format($ride->costo, 0, ',', '.') }}</td>
                                <td>
                                    <a href="{{ route('rides.show', $ride) }}">Ver Detalles</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" style="text-align: center;">Error cargando rides. Por favor intenta más tarde.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
