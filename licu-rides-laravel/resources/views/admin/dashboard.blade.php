@extends('layouts.app')

@section('title', 'Admin Dashboard - Licu Rides')

@section('content')
<div class="breadcrumb">
    <a href="{{ route('dashboard') }}">Dashboard</a> > <span>Admin Panel</span>
</div>

<section class="admin-section">
    <h2>Panel de Administración</h2>

    <div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin: 2rem 0;">
        <div class="stat-card" style="background: white; padding: 1.5rem; border-radius: 0.5rem; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            <h3 style="color: #666; font-size: 0.9rem; margin-bottom: 0.5rem;">Total Usuarios</h3>
            <p style="font-size: 2rem; font-weight: bold; color: #333;">{{ $stats['total_users'] }}</p>
            <small style="color: #666;">
                {{ $stats['active_users'] }} activos | 
                {{ $stats['pending_users'] }} pendientes
            </small>
        </div>

        <div class="stat-card" style="background: white; padding: 1.5rem; border-radius: 0.5rem; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            <h3 style="color: #666; font-size: 0.9rem; margin-bottom: 0.5rem;">Total Rides</h3>
            <p style="font-size: 2rem; font-weight: bold; color: #333;">{{ $stats['total_rides'] }}</p>
            <small style="color: #666;">
                {{ $stats['active_rides'] }} activos | 
                {{ $stats['completed_rides'] }} completados
            </small>
        </div>

        <div class="stat-card" style="background: white; padding: 1.5rem; border-radius: 0.5rem; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            <h3 style="color: #666; font-size: 0.9rem; margin-bottom: 0.5rem;">Total Vehículos</h3>
            <p style="font-size: 2rem; font-weight: bold; color: #333;">{{ $stats['total_vehicles'] }}</p>
        </div>

        <div class="stat-card" style="background: white; padding: 1.5rem; border-radius: 0.5rem; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            <h3 style="color: #666; font-size: 0.9rem; margin-bottom: 0.5rem;">Total Reservas</h3>
            <p style="font-size: 2rem; font-weight: bold; color: #333;">{{ $stats['total_reservations'] }}</p>
            <small style="color: #666;">
                {{ $stats['pending_reservations'] }} pendientes | 
                {{ $stats['accepted_reservations'] }} aceptadas
            </small>
        </div>
    </div>

    <div class="admin-actions" style="margin: 2rem 0; display: flex; gap: 1rem; flex-wrap: wrap;">
        <a href="{{ route('admin.users') }}" style="background: #007bff; color: white; padding: 0.75rem 1.5rem; border-radius: 0.25rem; text-decoration: none;">👥 Gestionar Usuarios</a>
        <a href="{{ route('admin.rides') }}" style="background: #28a745; color: white; padding: 0.75rem 1.5rem; border-radius: 0.25rem; text-decoration: none;">🚗 Gestionar Rides</a>
        <a href="{{ route('admin.reservations') }}" style="background: #ffc107; color: #333; padding: 0.75rem 1.5rem; border-radius: 0.25rem; text-decoration: none;">📋 Ver Reservas</a>
    </div>

    <div class="recent-activity" style="margin-top: 3rem;">
        <h3>Actividad Reciente</h3>
        <table style="width: 100%; background: white; border-radius: 0.5rem; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            <thead style="background: #f8f9fa;">
                <tr>
                    <th style="padding: 1rem; text-align: left;">Tipo</th>
                    <th style="padding: 1rem; text-align: left;">Descripción</th>
                    <th style="padding: 1rem; text-align: left;">Fecha</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentActivity as $activity)
                    <tr style="border-top: 1px solid #dee2e6;">
                        <td style="padding: 1rem;">
                            @switch($activity['type'])
                                @case('user')
                                    <span style="background: #007bff; color: white; padding: 0.25rem 0.5rem; border-radius: 0.25rem; font-size: 0.85rem;">Usuario</span>
                                    @break
                                @case('ride')
                                    <span style="background: #28a745; color: white; padding: 0.25rem 0.5rem; border-radius: 0.25rem; font-size: 0.85rem;">Ride</span>
                                    @break
                                @case('reservation')
                                    <span style="background: #ffc107; color: #333; padding: 0.25rem 0.5rem; border-radius: 0.25rem; font-size: 0.85rem;">Reserva</span>
                                    @break
                            @endswitch
                        </td>
                        <td style="padding: 1rem;">{{ $activity['description'] }}</td>
                        <td style="padding: 1rem; color: #666;">{{ $activity['date']->diffForHumans() }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" style="padding: 2rem; text-align: center; color: #666;">
                            No hay actividad reciente
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection
