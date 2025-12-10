@extends('layouts.app')

@section('title', 'Ver Reservas - Admin')

@section('content')
<div class="breadcrumb">
    <a href="{{ route('dashboard') }}">Dashboard</a> > 
    <a href="{{ route('admin.dashboard') }}">Admin</a> > 
    <span>Reservas</span>
</div>

<section class="admin-section">
    <div class="section-header">
        <h2>Todas las Reservas</h2>
        <div class="filters" style="display: flex; gap: 1rem; margin: 1rem 0;">
            <select id="statusFilter" onchange="filterReservations()" style="padding: 0.5rem; border: 1px solid #ddd; border-radius: 0.25rem;">
                <option value="">Todos los estados</option>
                <option value="pending">Pendientes</option>
                <option value="accepted">Aceptadas</option>
                <option value="rejected">Rechazadas</option>
                <option value="cancelled">Canceladas</option>
            </select>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Pasajero</th>
                <th>Ride</th>
                <th>Conductor</th>
                <th>Fecha</th>
                <th>Asientos</th>
                <th>Estado</th>
                <th>Creación</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach($reservations as $reservation)
                <tr data-status="{{ $reservation->status }}">
                    <td>{{ $reservation->id }}</td>
                    <td>
                        {{ $reservation->passenger->nombre }} {{ $reservation->passenger->apellido }}<br>
                        <small style="color: #666;">{{ $reservation->passenger->email }}</small>
                    </td>
                    <td>
                        {{ $reservation->ride->nombre }}<br>
                        <small style="color: #666;">{{ $reservation->ride->origen }} → {{ $reservation->ride->destino }}</small>
                    </td>
                    <td>{{ $reservation->ride->user->nombre }} {{ $reservation->ride->user->apellido }}</td>
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
                            @case('cancelled')
                                <span style="color: #6c757d;">Cancelada</span>
                                @break
                        @endswitch
                    </td>
                    <td>{{ $reservation->created_at->format('d/m/Y H:i') }}</td>
                    <td>
                        <form action="{{ route('admin.reservations.destroy', $reservation) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" onclick="return confirm('¿Eliminar esta reserva?')" style="background: #dc3545; color: white; border: none; padding: 0.5rem 1rem; border-radius: 0.25rem; cursor: pointer; font-size: 0.85rem;">Eliminar</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div style="margin-top: 2rem;">
        {{ $reservations->links() }}
    </div>
</section>

<script>
function filterReservations() {
    const statusFilter = document.getElementById('statusFilter').value;
    const rows = document.querySelectorAll('tbody tr');
    
    rows.forEach(row => {
        const status = row.dataset.status;
        row.style.display = (!statusFilter || status === statusFilter) ? '' : 'none';
    });
}
</script>
@endsection
