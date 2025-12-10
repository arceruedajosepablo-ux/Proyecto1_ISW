@extends('layouts.app')

@section('title', 'Mis Vehículos - Licu Rides')

@section('content')
<div class="breadcrumb">
    <a href="{{ route('dashboard') }}">Dashboard</a> > <span>Vehículos</span>
</div>

<section class="vehicles-section">
    <div class="section-header">
        <h2>Mis Vehículos</h2>
        <a href="{{ route('vehicles.create') }}" class="add-button">+</a>
    </div>

    <div class="vehicles-grid">
        @forelse($vehicles as $vehicle)
            <div class="vehicle-card">
                @if($vehicle->foto)
                    <img src="{{ asset('storage/' . $vehicle->foto) }}" alt="{{ $vehicle->marca }} {{ $vehicle->modelo }}">
                @else
                    <div class="vehicle-placeholder" style="background: #f0f0f0; height: 200px; display: flex; align-items: center; justify-content: center; color: #999;">
                        Sin foto
                    </div>
                @endif
                <div class="vehicle-info">
                    <h3>{{ $vehicle->marca }} {{ $vehicle->modelo }}</h3>
                    <p><strong>Placa:</strong> {{ $vehicle->placa }}</p>
                    <p><strong>Año:</strong> {{ $vehicle->anio }}</p>
                    <p><strong>Color:</strong> {{ $vehicle->color ?? 'No especificado' }}</p>
                    <p><strong>Capacidad:</strong> {{ $vehicle->capacidad }} personas</p>
                </div>
                <div class="vehicle-actions">
                    <a href="{{ route('vehicles.edit', $vehicle) }}" class="btn-edit">Editar</a>
                    <form action="{{ route('vehicles.destroy', $vehicle) }}" method="POST" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-delete" onclick="return confirm('¿Estás seguro de eliminar este vehículo?')">Eliminar</button>
                    </form>
                </div>
            </div>
        @empty
            <div style="grid-column: 1 / -1; text-align: center; padding: 2rem;">
                <p>No tienes vehículos registrados.</p>
                <a href="{{ route('vehicles.create') }}" style="color: #007bff; text-decoration: underline;">Registra tu primer vehículo aquí</a>
            </div>
        @endforelse
    </div>
</section>
@endsection

@push('styles')
<style>
.vehicles-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 1.5rem;
    margin-top: 1.5rem;
}

.vehicle-card {
    background: white;
    border: 1px solid #ddd;
    border-radius: 0.5rem;
    overflow: hidden;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.vehicle-card img {
    width: 100%;
    height: 200px;
    object-fit: cover;
}

.vehicle-info {
    padding: 1rem;
}

.vehicle-info h3 {
    margin: 0 0 0.5rem 0;
    color: #007bff;
}

.vehicle-info p {
    margin: 0.25rem 0;
    font-size: 0.9rem;
}

.vehicle-actions {
    padding: 0 1rem 1rem 1rem;
    display: flex;
    gap: 0.5rem;
}

.btn-edit, .btn-delete {
    flex: 1;
    padding: 0.5rem;
    border-radius: 0.25rem;
    border: none;
    cursor: pointer;
    text-align: center;
    text-decoration: none;
    display: inline-block;
}

.btn-edit {
    background: #007bff;
    color: white;
}

.btn-delete {
    background: #dc3545;
    color: white;
}

.btn-edit:hover {
    background: #0056b3;
}

.btn-delete:hover {
    background: #c82333;
}
</style>
@endpush
