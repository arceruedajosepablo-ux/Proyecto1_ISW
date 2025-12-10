@extends('layouts.app')

@section('title', 'Dashboard - Licu Rides')

@section('content')
<div class="breadcrumb">
    <a href="{{ route('dashboard') }}">Dashboard ></a>
</div>

<section class="rides-section">
    <div class="section-header">
        <h2>Mis Rides</h2>
        @if(auth()->user()->isDriver() || auth()->user()->isAdmin())
            <a href="{{ route('rides.create') }}" class="add-button">+</a>
        @endif
    </div>
    
    <!-- Filtros -->
    <div style="margin-bottom: 1rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
        <form method="GET" action="{{ route('dashboard') }}" style="display: flex; gap: 1rem; flex-wrap: wrap; align-items: end;">
            <div>
                <label for="fecha">Fecha:</label>
                <input type="date" id="fecha" name="fecha" value="{{ request('fecha') }}" style="padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px;">
            </div>
            <div>
                <label for="origen">Origen:</label>
                <input type="text" id="origen" name="origen" value="{{ request('origen') }}" placeholder="Origen" style="padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px;">
            </div>
            <div>
                <label for="destino">Destino:</label>
                <input type="text" id="destino" name="destino" value="{{ request('destino') }}" placeholder="Destino" style="padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px;">
            </div>
            <div style="display: flex; gap: 0.5rem;">
                <button type="submit" style="background: #007bff; color: white; border: none; padding: 0.5rem 1rem; border-radius: 4px; cursor: pointer;">Filtrar</button>
                <a href="{{ route('dashboard') }}" style="background: #6c757d; color: white; padding: 0.5rem 1rem; border-radius: 4px; text-decoration: none; display: inline-block;">Limpiar</a>
            </div>
        </form>
        
        <form method="GET" action="{{ route('dashboard') }}" style="display: flex; align-items: center; gap: 0.5rem;">
            <input type="hidden" name="fecha" value="{{ request('fecha') }}">
            <input type="hidden" name="origen" value="{{ request('origen') }}">
            <input type="hidden" name="destino" value="{{ request('destino') }}">
            <input type="hidden" id="order" name="order" value="{{ request('order', 'desc') }}">
            
            <label for="sortBy" style="margin: 0;">Ordenar por:</label>
            <select id="sortBy" name="sortBy" style="padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px;" onchange="this.form.submit()">
                <option value="fecha" {{ request('sortBy', 'fecha') == 'fecha' ? 'selected' : '' }}>Fecha</option>
                <option value="origen" {{ request('sortBy') == 'origen' ? 'selected' : '' }}>Origen</option>
                <option value="destino" {{ request('sortBy') == 'destino' ? 'selected' : '' }}>Destino</option>
            </select>
            <button type="button" id="sortOrder" onclick="toggleSort()" style="background: #6c757d; color: white; border: none; padding: 0.5rem 0.75rem; border-radius: 4px; cursor: pointer; font-size: 1.2rem;" title="Cambiar orden">
                {{ request('order', 'desc') == 'asc' ? '↑' : '↓' }}
            </button>
        </form>
    </div>
    
    <table id="myRidesTable">
        <thead>
            <tr>
                <th>Name</th>
                <th>Start</th>
                <th>End</th>
                <th>Fecha</th>
                <th>Espacios</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rides as $ride)
                <tr>
                    <td>{{ $ride->nombre }}</td>
                    <td>{{ $ride->origen }}</td>
                    <td>{{ $ride->destino }}</td>
                    <td>{{ $ride->fecha->format('d/m/Y') }} {{ \Carbon\Carbon::parse($ride->hora)->format('H:i') }}</td>
                    <td>{{ $ride->espacios_disponibles }}/{{ $ride->espacios }}</td>
                    <td>
                        @if(auth()->user()->isDriver() || auth()->user()->isAdmin())
                            <a href="{{ route('rides.edit', $ride) }}">Editar</a> - 
                            <form action="{{ route('rides.destroy', $ride) }}" method="POST" style="display: inline;">
                                @csrf
                                @method('DELETE')
                                <a href="#" onclick="event.preventDefault(); if(confirm('¿Estás seguro?')) this.closest('form').submit();">Eliminar</a>
                            </form>
                        @else
                            <a href="{{ route('rides.show', $ride) }}">Ver detalles</a>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">No tienes rides</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</section>

@if(auth()->user()->isDriver() || auth()->user()->isAdmin())
    <section class="reservations-section" style="margin-top: 2rem;">
        <h2>Solicitudes de Reserva Pendientes</h2>
        @if($pendingReservations->count() > 0)
            <table>
                <thead>
                    <tr>
                        <th>Pasajero</th>
                        <th>Ride</th>
                        <th>Fecha</th>
                        <th>Asientos</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pendingReservations as $reservation)
                    <tr>
                        <td>{{ $reservation->passenger->nombre }} {{ $reservation->passenger->apellido }}</td>
                        <td>{{ $reservation->ride->nombre }} ({{ $reservation->ride->origen }} → {{ $reservation->ride->destino }})</td>
                        <td>{{ $reservation->ride->fecha->format('d/m/Y') }}</td>
                        <td>{{ $reservation->seats }}</td>
                        <td>
                            <form action="{{ route('reservations.accept', $reservation) }}" method="POST" style="display: inline; margin-right: 5px;">
                                @csrf
                                <button type="submit" style="background: #28a745; color: white; border: none; padding: 0.5rem 1rem; border-radius: 0.25rem; cursor: pointer;">Aceptar</button>
                            </form>
                            <form action="{{ route('reservations.reject', $reservation) }}" method="POST" style="display: inline;">
                                @csrf
                                <button type="submit" style="background: #dc3545; color: white; border: none; padding: 0.5rem 1rem; border-radius: 0.25rem; cursor: pointer;">Rechazar</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p>No hay solicitudes pendientes</p>
        @endif
    </section>
@endif

<script>
function toggleSort() {
    const orderInput = document.getElementById('order');
    const sortButton = document.getElementById('sortOrder');
    
    if (orderInput.value === 'desc') {
        orderInput.value = 'asc';
        sortButton.textContent = '↑';
    } else {
        orderInput.value = 'desc';
        sortButton.textContent = '↓';
    }
    
    // Submit el formulario automáticamente
    sortButton.closest('form').submit();
}
</script>
@endsection
