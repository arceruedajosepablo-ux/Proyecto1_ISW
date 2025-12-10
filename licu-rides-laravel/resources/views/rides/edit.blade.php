@extends('layouts.app')

@section('title', 'Editar Ride - Licu Rides')

@section('content')
<div class="breadcrumb">
    <a href="{{ route('dashboard') }}">Dashboard</a> > <a href="{{ route('rides.index') }}">Rides</a> > <span>Editar</span>
</div>

<form action="{{ route('rides.update', $ride) }}" method="POST">
    @csrf
    @method('PUT')
    <div class="form-group">
        <label for="nombre">Nombre del Ride</label>
        <input type="text" id="nombre" name="nombre" value="{{ old('nombre', $ride->nombre) }}" required>
    </div>
    <div class="form-row">
        <div class="form-group">
            <label for="origen">Inicia en</label>
            <input type="text" id="origen" name="origen" value="{{ old('origen', $ride->origen) }}" required>
        </div>
        <div class="form-group">
            <label for="destino">Finaliza en</label>
            <input type="text" id="destino" name="destino" value="{{ old('destino', $ride->destino) }}" required>
        </div>
    </div>
    <div class="form-group">
        <label for="vehicle_id">Vehículo</label>
        <select id="vehicle_id" name="vehicle_id" required>
            @foreach($vehicles as $vehicle)
                <option value="{{ $vehicle->id }}" {{ old('vehicle_id', $ride->vehicle_id) == $vehicle->id ? 'selected' : '' }}>
                    {{ $vehicle->marca }} {{ $vehicle->modelo }} - {{ $vehicle->placa }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="form-group">
        <label for="fecha">Fecha</label>
        <input type="date" id="fecha" name="fecha" value="{{ old('fecha', $ride->fecha->format('Y-m-d')) }}" required>
    </div>
    <div class="form-group">
        <label for="hora">Hora</label>
        <input type="time" id="hora" name="hora" value="{{ old('hora', $ride->hora) }}" required>
    </div>
    <div class="form-group">
        <label for="costo">Costo por espacio (₡)</label>
        <input type="number" step="0.01" id="costo" name="costo" value="{{ old('costo', $ride->costo) }}" required>
    </div>
    <div class="form-group">
        <label for="espacios">Cantidad de espacios</label>
        <input type="number" id="espacios" name="espacios" min="1" value="{{ old('espacios', $ride->espacios) }}" required>
    </div>
    <div class="form-actions">
        <button type="button" class="cancel" onclick="location.href='{{ route('dashboard') }}'">Cancelar</button>
        <button type="submit" class="save">Actualizar Ride</button>
    </div>
</form>
@endsection
