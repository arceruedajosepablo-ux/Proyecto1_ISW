@extends('layouts.app')

@section('title', auth()->user()->isPassenger() ? 'Mis Reservas' : 'Solicitudes de Reserva' . ' - Licu Rides')

@section('content')
<div class="breadcrumb">
    <a href="{{ route('dashboard') }}">Dashboard</a> > <span>{{ auth()->user()->isPassenger() ? 'Mis Reservas' : 'Solicitudes' }}</span>
</div>

<section class="rides-section">
    <div class="section-header">
        <h2>{{ auth()->user()->isPassenger() ? 'Mis Reservas' : 'Solicitudes de Reserva' }}</h2>
    </div>

    <div style="overflow-x: auto;">
    <table>
        <thead>
            <tr>
                @if(auth()->user()->isPassenger())
                    <th>Ride</th>
                    <th>Conductor</th>
                    <th>Vehículo</th>
                    <th>Fecha</th>
                    <th>Hora</th>
                    <th>Costo</th>
                    <th>Asientos</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                @else
                    <th>Pasajero</th>
                    <th>Contacto</th>
                    <th>Ride</th>
                    <th>Fecha</th>
                    <th>Asientos</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @forelse($reservations as $reservation)
                <tr>
                    @if(auth()->user()->isPassenger())
                        <td>{{ $reservation->ride->nombre }}<br><small>{{ $reservation->ride->origen }} → {{ $reservation->ride->destino }}</small></td>
                        <td>{{ $reservation->ride->user->nombre }} {{ $reservation->ride->user->apellido }}</td>
                        <td>{{ $reservation->ride->vehicle->marca }} {{ $reservation->ride->vehicle->modelo }}</td>
                        <td>{{ $reservation->ride->fecha->format('d/m/Y') }}</td>
                        <td>{{ $reservation->ride->hora }}</td>
                        <td>₡{{ number_format($reservation->ride->costo * $reservation->seats, 0) }}</td>
                        <td>{{ $reservation->seats }}</td>
                        <td>
                            @switch($reservation->status)
                                @case('pending')
                                    <span style="color: #ffc107;">⏳ Pendiente</span>
                                    @break
                                @case('accepted')
                                    <span style="color: #28a745;">✓ Aceptada</span>
                                    @break
                                @case('rejected')
                                    <span style="color: #dc3545;">✗ Rechazada</span>
                                    @break
                                @case('cancelled')
                                    <span style="color: #6c757d;">Cancelada</span>
                                    @break
                            @endswitch
                        </td>
                        <td>
                            @if($reservation->status === 'pending')
                                <form action="{{ route('reservations.destroy', $reservation) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" onclick="return confirm('¿Cancelar esta reserva?')" style="background: #dc3545; color: white; border: none; padding: 0.5rem 1rem; border-radius: 0.25rem; cursor: pointer;">Cancelar</button>
                                </form>
                            @else
                                -
                            @endif
                        </td>
                    @else
                        <td>{{ $reservation->passenger->nombre }} {{ $reservation->passenger->apellido }}</td>
                        <td>
                            <small>{{ $reservation->passenger->email }}</small><br>
                            <small>{{ $reservation->passenger->telefono }}</small>
                        </td>
                        <td>{{ $reservation->ride->nombre }}<br><small>{{ $reservation->ride->origen }} → {{ $reservation->ride->destino }}</small></td>
                        <td>{{ $reservation->ride->fecha->format('d/m/Y') }}</td>
                        <td>{{ $reservation->seats }}</td>
                        <td>
                            @switch($reservation->status)
                                @case('pending')
                                    <span style="color: #ffc107;">⏳ Pendiente</span>
                                    @break
                                @case('accepted')
                                    <span style="color: #28a745;">✓ Aceptada</span>
                                    @break
                                @case('rejected')
                                    <span style="color: #dc3545;">✗ Rechazada</span>
                                    @break
                            @endswitch
                        </td>
                        <td style="white-space: nowrap;">
                            @if($reservation->status === 'pending')
                                <form action="{{ route('reservations.accept', $reservation) }}" method="POST" style="display: inline-block; margin-right: 5px;">
                                    @csrf
                                    <button type="submit" style="background: #28a745; color: white; border: none; padding: 0.4rem 0.8rem; border-radius: 0.25rem; cursor: pointer; font-size: 0.875rem;">Aceptar</button>
                                </form>
                                <form action="{{ route('reservations.reject', $reservation) }}" method="POST" style="display: inline-block;">
                                    @csrf
                                    <button type="submit" style="background: #dc3545; color: white; border: none; padding: 0.4rem 0.8rem; border-radius: 0.25rem; cursor: pointer; font-size: 0.875rem;">Rechazar</button>
                                </form>
                            @else
                                -
                            @endif
                        </td>
                    @endif
                </tr>
            @empty
                <tr>
                    <td colspan="{{ auth()->user()->isPassenger() ? '9' : '7' }}" style="text-align: center; padding: 2rem;">
                        @if(auth()->user()->isPassenger())
                            No tienes reservas. <a href="{{ route('rides.index') }}">Busca rides disponibles</a>
                        @else
                            No hay solicitudes pendientes
                        @endif
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
    </div>
</section>
@endsection
