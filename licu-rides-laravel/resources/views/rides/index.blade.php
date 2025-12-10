@extends('layouts.app')

@section('title', 'Rides Disponibles - Licu Rides')

@section('content')
<div class="breadcrumb">
    <a href="{{ route('dashboard') }}">Dashboard</a> > 
    <a href="{{ route('rides.index') }}">Rides</a>
</div>

<section class="rides-section">
    <div class="section-header">
        <h2>Rides Disponibles</h2>
    </div>
    
    <!-- Filtros -->
    <div style="margin-bottom: 1rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
        <form method="GET" action="{{ route('rides.index') }}" style="display: flex; gap: 1rem; flex-wrap: wrap; align-items: end;">
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
                <a href="{{ route('rides.index') }}" style="background: #6c757d; color: white; padding: 0.5rem 1rem; border-radius: 4px; text-decoration: none; display: inline-block;">Limpiar</a>
            </div>
        </form>
        
        <form method="GET" action="{{ route('rides.index') }}" style="display: flex; align-items: center; gap: 0.5rem;">
            <input type="hidden" name="fecha" value="{{ request('fecha') }}">
            <input type="hidden" name="origen" value="{{ request('origen') }}">
            <input type="hidden" name="destino" value="{{ request('destino') }}">
            <input type="hidden" id="order" name="order" value="{{ request('order', 'asc') }}">
            
            <label for="sortBy" style="margin: 0;">Ordenar por:</label>
            <select id="sortBy" name="sortBy" style="padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px;" onchange="this.form.submit()">
                <option value="fecha" {{ request('sortBy', 'fecha') == 'fecha' ? 'selected' : '' }}>Fecha</option>
                <option value="origen" {{ request('sortBy') == 'origen' ? 'selected' : '' }}>Origen</option>
                <option value="destino" {{ request('sortBy') == 'destino' ? 'selected' : '' }}>Destino</option>
            </select>
            <button type="button" id="sortOrder" onclick="toggleSort()" style="background: #6c757d; color: white; border: none; padding: 0.5rem 0.75rem; border-radius: 4px; cursor: pointer; font-size: 1.2rem;" title="Cambiar orden">
                {{ request('order', 'asc') == 'asc' ? '↑' : '↓' }}
            </button>
        </form>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Conductor</th>
                <th>Start</th>
                <th>End</th>
                <th>Fecha</th>
                <th>Costo</th>
                <th>Espacios</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rides as $ride)
                <tr>
                    <td>{{ $ride->nombre }}</td>
                    <td>{{ $ride->user->nombre }} {{ $ride->user->apellido }}</td>
                    <td>{{ $ride->origen }}</td>
                    <td>{{ $ride->destino }}</td>
                    <td>{{ $ride->fecha->format('d/m/Y') }} {{ \Carbon\Carbon::parse($ride->hora)->format('H:i') }}</td>
                    <td>₡{{ number_format($ride->costo, 0) }}</td>
                    <td>{{ $ride->espacios_disponibles }}/{{ $ride->espacios }}</td>
                    <td>
                        <a href="{{ route('rides.show', $ride) }}">Ver detalles</a>
                        @auth
                            @if(auth()->user()->isPassenger() && $ride->espacios_disponibles > 0)
                                @php
                                    $userReservation = $ride->reservations->where('passenger_id', auth()->id())->first();
                                @endphp
                                @if(!$userReservation)
                                    - <a href="{{ route('rides.show', $ride) }}" style="color: #28a745;">Reservar</a>
                                @elseif($userReservation->status === 'pending')
                                    - <span style="color: #ffc107;">Pendiente</span>
                                @elseif($userReservation->status === 'accepted')
                                    - <span style="color: #28a745;">Confirmado</span>
                                @endif
                            @endif
                        @endauth
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" style="text-align: center;">No hay rides disponibles</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</section>

<script>
function toggleSort() {
    const orderInput = document.getElementById('order');
    const sortButton = document.getElementById('sortOrder');
    
    if (orderInput.value === 'asc') {
        orderInput.value = 'desc';
        sortButton.textContent = '↓';
    } else {
        orderInput.value = 'asc';
        sortButton.textContent = '↑';
    }
    
    // Submit el formulario automáticamente
    sortButton.closest('form').submit();
}
</script>
@endsection
