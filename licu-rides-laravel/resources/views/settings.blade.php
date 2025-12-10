@extends('layouts.app')

@section('title', 'Configuración - Licu Rides')

@section('content')
<div class="breadcrumb">
    <a href="{{ route('dashboard') }}">Dashboard</a> > <span>Configuración</span>
</div>

<section class="settings-section">
    <h2>Configuración de Cuenta</h2>

    <div class="settings-form">
        <h3>Datos Personales</h3>
        <form action="{{ route('settings.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
            <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                <div class="form-group">
                    <label for="nombre">Nombre *</label>
                    <input type="text" id="nombre" name="nombre" value="{{ old('nombre', auth()->user()->nombre) }}" required style="width: 100%; padding: 0.5rem; border: 1px solid #ddd; border-radius: 0.25rem;">
                </div>

                <div class="form-group">
                    <label for="apellido">Apellido *</label>
                    <input type="text" id="apellido" name="apellido" value="{{ old('apellido', auth()->user()->apellido) }}" required style="width: 100%; padding: 0.5rem; border: 1px solid #ddd; border-radius: 0.25rem;">
                </div>
            </div>

            <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                <div class="form-group">
                    <label for="cedula">Cédula *</label>
                    <input type="text" id="cedula" name="cedula" value="{{ old('cedula', auth()->user()->cedula) }}" required style="width: 100%; padding: 0.5rem; border: 1px solid #ddd; border-radius: 0.25rem;">
                </div>

                <div class="form-group">
                    <label for="fecha_nacimiento">Fecha de Nacimiento</label>
                    <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" value="{{ old('fecha_nacimiento', auth()->user()->fecha_nacimiento ?? '') }}" style="width: 100%; padding: 0.5rem; border: 1px solid #ddd; border-radius: 0.25rem;">
                </div>
            </div>

            <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                <div class="form-group">
                    <label for="email">Correo Electrónico *</label>
                    <input type="email" id="email" name="email" value="{{ old('email', auth()->user()->email) }}" required style="width: 100%; padding: 0.5rem; border: 1px solid #ddd; border-radius: 0.25rem;">
                </div>

                <div class="form-group">
                    <label for="telefono">Teléfono *</label>
                    <input type="tel" id="telefono" name="telefono" value="{{ old('telefono', auth()->user()->telefono) }}" required style="width: 100%; padding: 0.5rem; border: 1px solid #ddd; border-radius: 0.25rem;">
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label for="foto">Foto de Perfil</label>
                <input type="file" id="foto" name="foto" accept="image/*" style="width: 100%; padding: 0.5rem; border: 1px solid #ddd; border-radius: 0.25rem;">
                @if(auth()->user()->foto)
                    <div style="margin-top: 1rem;">
                        <img src="{{ asset('storage/' . auth()->user()->foto) }}" alt="Foto actual" style="width: 100px; height: 100px; object-fit: cover; border-radius: 50%;">
                        <p style="margin-top: 0.5rem; font-size: 0.9rem; color: #666;">Foto actual</p>
                    </div>
                @endif
            </div>

            <hr style="margin: 2rem 0; border: none; border-top: 1px solid #dee2e6;">

            <h3>Cambiar Contraseña</h3>
            <p style="color: #666; font-size: 0.9rem; margin-bottom: 1rem;">Deja estos campos vacíos si no deseas cambiar la contraseña</p>

            <div class="form-group" style="margin-bottom: 1rem;">
                <label for="current_password">Contraseña Actual</label>
                <input type="password" id="current_password" name="current_password" style="width: 100%; padding: 0.5rem; border: 1px solid #ddd; border-radius: 0.25rem;">
            </div>

            <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem;">
                <div class="form-group">
                    <label for="new_password">Nueva Contraseña</label>
                    <input type="password" id="new_password" name="new_password" style="width: 100%; padding: 0.5rem; border: 1px solid #ddd; border-radius: 0.25rem;">
                </div>

                <div class="form-group">
                    <label for="new_password_confirmation">Confirmar Nueva Contraseña</label>
                    <input type="password" id="new_password_confirmation" name="new_password_confirmation" style="width: 100%; padding: 0.5rem; border: 1px solid #ddd; border-radius: 0.25rem;">
                </div>
            </div>

            <div class="form-actions" style="display: flex; gap: 1rem; justify-content: flex-end;">
                <a href="{{ route('dashboard') }}" style="padding: 0.75rem 1.5rem; background: #6c757d; color: white; text-decoration: none; border-radius: 0.25rem;">Cancelar</a>
                <button type="submit" style="padding: 0.75rem 1.5rem; background: #007bff; color: white; border: none; border-radius: 0.25rem; cursor: pointer;">Guardar Cambios</button>
            </div>
        </form>
    </div>
</section>
@endsection
