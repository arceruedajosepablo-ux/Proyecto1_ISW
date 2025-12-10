@extends('layouts.app')

@section('title', 'Gestionar Rides - Admin')

@section('content')
<div class="breadcrumb">
    <a href="{{ route('dashboard') }}">Dashboard</a> > 
    <a href="{{ route('admin.dashboard') }}">Admin</a> > 
    <span>Rides</span>
</div>

<section class="admin-section">
    <div class="section-header">
        <h2>Gestión de Rides</h2>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Conductor</th>
                <th>Origen → Destino</th>
                <th>Fecha</th>
                <th>Hora</th>
                <th>Costo</th>
                <th>Capacidad</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rides as $ride)
                <tr>
                    <td>{{ $ride->id }}</td>
                    <td>{{ $ride->nombre }}</td>
                    <td>{{ $ride->user->nombre }} {{ $ride->user->apellido }}</td>
                    <td>
                        <strong>{{ $ride->origen }}</strong><br>
                        <small style="color: #666;">↓</small><br>
                        <strong>{{ $ride->destino }}</strong>
                    </td>
                    <td>{{ $ride->fecha->format('d/m/Y') }}</td>
                    <td>{{ $ride->hora }}</td>
                    <td>₡{{ number_format($ride->costo, 0) }}</td>
                    <td>{{ $ride->espacios_disponibles }} / {{ $ride->espacios }}</td>
                    <td>
                        @if($ride->fecha->isFuture())
                            <span style="color: #28a745;">Activo</span>
                        @else
                            <span style="color: #6c757d;">Completado</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('rides.show', $ride) }}" style="background: #007bff; color: white; border: none; padding: 0.5rem 1rem; border-radius: 0.25rem; text-decoration: none; font-size: 0.85rem; display: inline-block; margin-right: 5px;">Ver</a>
                        <form action="{{ route('admin.rides.destroy', $ride) }}" method="POST" style="display: inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" onclick="return confirm('¿Eliminar ride {{ $ride->nombre }}?')" style="background: #dc3545; color: white; border: none; padding: 0.5rem 1rem; border-radius: 0.25rem; cursor: pointer; font-size: 0.85rem;">Eliminar</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div style="margin-top: 2rem;">
        {{ $rides->links() }}
    </div>
</section>
@endsection
