@extends('layouts.app')

@section('title', $ride->nombre . ' - Licu Rides')

@section('content')
<div class="breadcrumb">
    <a href="{{ route('dashboard') }}">Dashboard</a> > 
    <a href="{{ route('rides.index') }}">Rides</a> > 
    <span>{{ $ride->nombre }}</span>
</div>

<section class="ride-detail">
    <div class="ride-header" style="background: white; padding: 2rem; border-radius: 0.5rem; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 2rem;">
        <h2>{{ $ride->nombre }}</h2>
        <p style="color: #666; margin-top: 0.5rem;">{{ $ride->descripcion }}</p>
    </div>

    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem;">
        <div class="ride-info" style="background: white; padding: 2rem; border-radius: 0.5rem; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            <h3 style="margin-bottom: 1.5rem;">Información del Viaje</h3>
            
            <div class="info-grid" style="display: grid; gap: 1rem;">
                <div class="info-item">
                    <strong style="color: #666;">Origen:</strong>
                    <p style="font-size: 1.1rem; margin-top: 0.25rem;">{{ $ride->origen }}</p>
                </div>

                <div class="info-item">
                    <strong style="color: #666;">Destino:</strong>
                    <p style="font-size: 1.1rem; margin-top: 0.25rem;">{{ $ride->destino }}</p>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="info-item">
                        <strong style="color: #666;">Fecha:</strong>
                        <p style="font-size: 1.1rem; margin-top: 0.25rem;">{{ $ride->fecha->format('d/m/Y') }}</p>
                    </div>

                    <div class="info-item">
                        <strong style="color: #666;">Hora:</strong>
                        <p style="font-size: 1.1rem; margin-top: 0.25rem;">{{ $ride->hora }}</p>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="info-item">
                        <strong style="color: #666;">Costo por persona:</strong>
                        <p style="font-size: 1.1rem; margin-top: 0.25rem; color: #28a745; font-weight: bold;">₡{{ number_format($ride->costo, 0) }}</p>
                    </div>

                    <div class="info-item">
                        <strong style="color: #666;">Espacios disponibles:</strong>
                        <p style="font-size: 1.1rem; margin-top: 0.25rem;">{{ $ride->espacios_disponibles }} de {{ $ride->espacios }}</p>
                    </div>
                </div>
            </div>

            <hr style="margin: 2rem 0; border: none; border-top: 1px solid #dee2e6;">

            <h3 style="margin-bottom: 1.5rem;">Conductor</h3>
            <div class="driver-info" style="display: flex; align-items: center; gap: 1rem;">
                <div class="driver-avatar" style="width: 60px; height: 60px; border-radius: 50%; background: #007bff; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.5rem; font-weight: bold;">
                    {{ substr($ride->user->nombre, 0, 1) }}{{ substr($ride->user->apellido, 0, 1) }}
                </div>
                <div>
                    <p style="font-weight: bold; font-size: 1.1rem;">{{ $ride->user->nombre }} {{ $ride->user->apellido }}</p>
                    <p style="color: #666;">{{ $ride->user->email }}</p>
                    <p style="color: #666;">{{ $ride->user->telefono }}</p>
                </div>
            </div>

            <hr style="margin: 2rem 0; border: none; border-top: 1px solid #dee2e6;">

            <h3 style="margin-bottom: 1.5rem;">Vehículo</h3>
            <div class="vehicle-info">
                @if($ride->vehicle->foto)
                    <img src="{{ asset('storage/' . $ride->vehicle->foto) }}" alt="Vehículo" style="width: 100%; max-width: 400px; border-radius: 0.5rem; margin-bottom: 1rem;">
                @endif
                <p><strong>Marca:</strong> {{ $ride->vehicle->marca }}</p>
                <p><strong>Modelo:</strong> {{ $ride->vehicle->modelo }}</p>
                <p><strong>Año:</strong> {{ $ride->vehicle->ano }}</p>
                <p><strong>Color:</strong> {{ $ride->vehicle->color }}</p>
                <p><strong>Placa:</strong> {{ $ride->vehicle->placa }}</p>
                <p><strong>Capacidad:</strong> {{ $ride->vehicle->capacidad }} pasajeros</p>
            </div>

            @if($ride->reservations->where('status', 'accepted')->count() > 0)
                <hr style="margin: 2rem 0; border: none; border-top: 1px solid #dee2e6;">
                <h3 style="margin-bottom: 1.5rem;">Pasajeros Confirmados</h3>
                <div class="passengers-list">
                    @foreach($ride->reservations->where('status', 'accepted') as $reservation)
                        <div style="padding: 1rem; background: #f8f9fa; border-radius: 0.25rem; margin-bottom: 0.5rem; display: flex; justify-content: space-between;">
                            <div>
                                <strong>{{ $reservation->passenger->nombre }} {{ $reservation->passenger->apellido }}</strong>
                                <p style="color: #666; font-size: 0.9rem;">{{ $reservation->seats }} asiento(s)</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="ride-actions">
            @auth
                @if(auth()->id() === $ride->user_id)
                    <div class="owner-actions" style="background: white; padding: 1.5rem; border-radius: 0.5rem; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 1rem;">
                        <h4 style="margin-bottom: 1rem;">Gestionar Ride</h4>
                        <a href="{{ route('rides.edit', $ride) }}" style="display: block; text-align: center; background: #007bff; color: white; padding: 0.75rem; border-radius: 0.25rem; text-decoration: none; margin-bottom: 0.5rem;">Editar</a>
                        <form action="{{ route('rides.destroy', $ride) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" onclick="return confirm('¿Eliminar este ride?')" style="width: 100%; background: #dc3545; color: white; border: none; padding: 0.75rem; border-radius: 0.25rem; cursor: pointer;">Eliminar</button>
                        </form>
                    </div>
                @elseif(auth()->user()->isPassenger() && $ride->hasAvailableSpaces())
                    @php
                        $userReservation = $ride->reservations->where('passenger_id', auth()->id())->first();
                    @endphp
                    
                    @if(!$userReservation)
                        <div class="reservation-form" style="background: white; padding: 1.5rem; border-radius: 0.5rem; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                            <h4 style="margin-bottom: 1rem;">Reservar Asientos</h4>
                            <form action="{{ route('reservations.store') }}" method="POST">
                                @csrf
                                <input type="hidden" name="ride_id" value="{{ $ride->id }}">
                                
                                <label style="display: block; margin-bottom: 0.5rem;">
                                    <strong>Número de asientos:</strong>
                                    <input type="number" name="seats" min="1" max="{{ $ride->espacios_disponibles }}" value="1" required style="width: 100%; padding: 0.5rem; border: 1px solid #ddd; border-radius: 0.25rem; margin-top: 0.25rem;">
                                </label>

                                <button type="submit" style="width: 100%; background: #28a745; color: white; border: none; padding: 0.75rem; border-radius: 0.25rem; cursor: pointer; margin-top: 1rem;">Reservar Ahora</button>
                            </form>
                        </div>
                    @elseif($userReservation->status === 'pending')
                        <div class="reservation-status" style="background: #fff3cd; padding: 1.5rem; border-radius: 0.5rem; border: 1px solid #ffc107;">
                            <p style="text-align: center; color: #856404;">⏳ Reserva pendiente de aprobación</p>
                        </div>
                    @elseif($userReservation->status === 'accepted')
                        <div class="reservation-status" style="background: #d4edda; padding: 1.5rem; border-radius: 0.5rem; border: 1px solid #28a745;">
                            <p style="text-align: center; color: #155724;">✓ ¡Reserva confirmada!</p>
                            <p style="text-align: center; font-size: 0.9rem; color: #155724; margin-top: 0.5rem;">{{ $userReservation->seats }} asiento(s)</p>
                        </div>
                    @endif
                @endif
            @endauth

            @guest
                <div style="background: white; padding: 1.5rem; border-radius: 0.5rem; box-shadow: 0 2px 4px rgba(0,0,0,0.1); text-align: center;">
                    <p style="margin-bottom: 1rem;">Inicia sesión para reservar</p>
                    <a href="{{ route('login') }}" style="display: inline-block; background: #007bff; color: white; padding: 0.75rem 1.5rem; border-radius: 0.25rem; text-decoration: none;">Iniciar Sesión</a>
                </div>
            @endguest
        </div>
    </div>
</section>
@endsection
