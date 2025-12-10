@extends('layouts.app')

@section('title', 'Crear Vehículo - Licu Rides')

@section('content')
<div class="breadcrumb">
    <a href="{{ route('dashboard') }}">Dashboard</a> > <a href="{{ route('vehicles.index') }}">Vehículos</a> > <span>Nuevo</span>
</div>

<form action="{{ route('vehicles.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <div class="form-group">
        <label for="placa">Placa</label>
        <input type="text" id="placa" name="placa" value="{{ old('placa') }}" required>
    </div>
    <div class="form-row">
        <div class="form-group">
            <label for="marca">Marca</label>
            <input type="text" id="marca" name="marca" value="{{ old('marca') }}" required>
        </div>
        <div class="form-group">
            <label for="modelo">Modelo</label>
            <input type="text" id="modelo" name="modelo" value="{{ old('modelo') }}" required>
        </div>
    </div>
    <div class="form-row">
        <div class="form-group">
            <label for="ano">Año</label>
            <input type="number" id="ano" name="ano" value="{{ old('ano') }}" required min="1900" max="{{ date('Y') + 1 }}">
        </div>
        <div class="form-group">
            <label for="color">Color</label>
            <input type="text" id="color" name="color" value="{{ old('color') }}" required>
        </div>
    </div>
    <div class="form-group">
        <label for="capacidad">Capacidad (pasajeros)</label>
        <input type="number" id="capacidad" name="capacidad" value="{{ old('capacidad', 4) }}" required min="1" max="50">
    </div>
    <div class="form-group">
        <label for="foto">Foto del Vehículo</label>
        <input type="file" id="foto" name="foto" accept="image/*">
    </div>
    <div class="form-actions">
        <button type="button" class="cancel" onclick="location.href='{{ route('vehicles.index') }}'">Cancelar</button>
        <button type="submit" class="save">Guardar Vehículo</button>
    </div>
</form>
@endsection
