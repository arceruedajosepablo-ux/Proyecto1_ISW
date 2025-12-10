@extends('layouts.app')

@section('title', 'Crear Ride - Licu Rides')

@section('content')
<div class="breadcrumb">
    <a href="{{ route('dashboard') }}">Dashboard</a> > <a href="{{ route('rides.index') }}">Rides</a> > <span>Nuevo</span>
</div>

<form action="{{ route('rides.store') }}" method="POST">
    @csrf
    <div class="form-group">
        <label for="nombre">Nombre del Ride</label>
        <input type="text" id="nombre" name="nombre" value="{{ old('nombre') }}" required>
    </div>
    <div class="form-row">
        <div class="form-group">
            <label for="origen">Inicia en</label>
            <input type="text" id="origen" name="origen" value="{{ old('origen') }}" required>
        </div>
        <div class="form-group">
            <label for="destino">Finaliza en</label>
            <input type="text" id="destino" name="destino" value="{{ old('destino') }}" required>
        </div>
    </div>
    <div class="form-group">
        <label for="vehicle_id">Vehículo</label>
        <select id="vehicle_id" name="vehicle_id" required>
            <option value="">Selecciona un vehículo</option>
            @foreach($vehicles as $vehicle)
                <option value="{{ $vehicle->id }}" {{ old('vehicle_id') == $vehicle->id ? 'selected' : '' }}>
                    {{ $vehicle->marca }} {{ $vehicle->modelo }} - {{ $vehicle->placa }}
                </option>
            @endforeach
        </select>
        @if($vehicles->isEmpty())
            <p style="color: #dc3545; font-size: 0.875rem; margin-top: 0.5rem;">
                No tienes vehículos registrados. <a href="{{ route('vehicles.create') }}">Registra uno aquí</a>
            </p>
        @endif
    </div>
    <div class="form-group">
        <label for="fecha">Fecha</label>
        <input type="date" id="fecha" name="fecha" value="{{ old('fecha') }}" min="{{ date('Y-m-d') }}" required>
    </div>
    <div class="form-group">
        <label for="hora">Hora</label>
        <input type="time" id="hora" name="hora" value="{{ old('hora') }}" required>
    </div>
    <div class="form-group">
        <label for="costo">Costo por espacio (₡)</label>
        <input type="number" step="0.01" id="costo" name="costo" value="{{ old('costo') }}" required>
    </div>
    <div class="form-group">
        <label for="espacios">Cantidad de espacios</label>
        <input type="number" id="espacios" name="espacios" min="1" value="{{ old('espacios', 1) }}" required>
    </div>
    <div class="form-actions">
        <button type="button" class="cancel" onclick="location.href='{{ route('dashboard') }}'">Cancelar</button>
        <button type="submit" class="save">Guardar Ride</button>
    </div>
</form>
@endsection
