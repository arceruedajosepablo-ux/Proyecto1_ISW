@extends('layouts.app')

@section('title', 'Editar Vehículo - Licu Rides')

@section('content')
<div class="breadcrumb">
    <a href="{{ route('dashboard') }}">Dashboard</a> > <a href="{{ route('vehicles.index') }}">Vehículos</a> > <span>Editar</span>
</div>

<form action="{{ route('vehicles.update', $vehicle) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    <div class="form-group">
        <label for="placa">Placa</label>
        <input type="text" id="placa" name="placa" value="{{ old('placa', $vehicle->placa) }}" required>
    </div>
    <div class="form-row">
        <div class="form-group">
            <label for="marca">Marca</label>
            <input type="text" id="marca" name="marca" value="{{ old('marca', $vehicle->marca) }}" required>
        </div>
        <div class="form-group">
            <label for="modelo">Modelo</label>
            <input type="text" id="modelo" name="modelo" value="{{ old('modelo', $vehicle->modelo) }}" required>
        </div>
    </div>
    <div class="form-row">
        <div class="form-group">
            <label for="ano">Año</label>
            <input type="number" id="ano" name="ano" value="{{ old('ano', $vehicle->ano) }}" required min="1900" max="{{ date('Y') + 1 }}">
        </div>
        <div class="form-group">
            <label for="color">Color</label>
            <input type="text" id="color" name="color" value="{{ old('color', $vehicle->color) }}" required>
        </div>
    </div>
    <div class="form-group">
        <label for="capacidad">Capacidad (pasajeros)</label>
        <input type="number" id="capacidad" name="capacidad" value="{{ old('capacidad', $vehicle->capacidad) }}" required min="1" max="50">
    </div>
    <div class="form-group">
        <label for="foto">Foto del Vehículo</label>
        @if($vehicle->foto)
            <div style="margin-bottom: 0.5rem;">
                <img src="{{ asset('storage/' . $vehicle->foto) }}" alt="Foto actual" style="max-width: 200px; border-radius: 0.5rem;">
            </div>
        @endif
        <input type="file" id="foto" name="foto" accept="image/*">
        <small>Deja vacío si no quieres cambiar la foto</small>
    </div>
    <div class="form-actions">
        <button type="button" class="cancel" onclick="location.href='{{ route('vehicles.index') }}'">Cancelar</button>
        <button type="submit" class="save">Actualizar Vehículo</button>
    </div>
</form>
@endsection
