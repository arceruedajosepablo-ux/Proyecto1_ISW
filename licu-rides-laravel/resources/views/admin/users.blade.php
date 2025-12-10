@extends('layouts.app')

@section('title', 'Gestionar Usuarios - Admin')

@section('content')
<div class="breadcrumb">
    <a href="{{ route('dashboard') }}">Dashboard</a> > 
    <a href="{{ route('admin.dashboard') }}">Admin</a> > 
    <span>Usuarios</span>
</div>

<section class="admin-section">
    <div class="section-header">
        <h2>Gestión de Usuarios</h2>
        <div class="filters" style="display: flex; gap: 1rem; margin: 1rem 0;">
            <select id="roleFilter" onchange="filterUsers()" style="padding: 0.5rem; border: 1px solid #ddd; border-radius: 0.25rem;">
                <option value="">Todos los roles</option>
                <option value="admin">Administradores</option>
                <option value="driver">Conductores</option>
                <option value="passenger">Pasajeros</option>
            </select>
            <select id="statusFilter" onchange="filterUsers()" style="padding: 0.5rem; border: 1px solid #ddd; border-radius: 0.25rem;">
                <option value="">Todos los estados</option>
                <option value="active">Activos</option>
                <option value="inactive">Inactivos</option>
                <option value="pending">Pendientes</option>
            </select>
        </div>
    </div>

    <div style="overflow-x: auto; margin-top: 1rem;">
        <table style="width: 100%; border-collapse: collapse; background: white;">
            <thead>
                <tr style="background: #f8f9fa;">
                    <th style="padding: 0.75rem; text-align: left; border-bottom: 2px solid #dee2e6;">ID</th>
                    <th style="padding: 0.75rem; text-align: left; border-bottom: 2px solid #dee2e6;">Nombre</th>
                    <th style="padding: 0.75rem; text-align: left; border-bottom: 2px solid #dee2e6;">Email</th>
                    <th style="padding: 0.75rem; text-align: left; border-bottom: 2px solid #dee2e6;">Teléfono</th>
                    <th style="padding: 0.75rem; text-align: left; border-bottom: 2px solid #dee2e6;">Rol</th>
                    <th style="padding: 0.75rem; text-align: left; border-bottom: 2px solid #dee2e6;">Estado</th>
                    <th style="padding: 0.75rem; text-align: left; border-bottom: 2px solid #dee2e6;">Registro</th>
                    <th style="padding: 0.75rem; text-align: left; border-bottom: 2px solid #dee2e6;">Acciones</th>
                </tr>
            </thead>
        <tbody>
            @foreach($users as $user)
                <tr data-role="{{ $user->role }}" data-status="{{ $user->status }}" style="border-bottom: 1px solid #dee2e6;">
                    <td style="padding: 0.75rem;">{{ $user->id }}</td>
                    <td style="padding: 0.75rem;">{{ $user->nombre }} {{ $user->apellido }}</td>
                    <td style="padding: 0.75rem;">{{ $user->email }}</td>
                    <td style="padding: 0.75rem;">{{ $user->telefono }}</td>
                    <td style="padding: 0.75rem;">
                        @if($user->isAdmin())
                            <span style="background: #dc3545; color: white; padding: 0.25rem 0.5rem; border-radius: 0.25rem; font-size: 0.85rem;">Admin</span>
                        @elseif($user->isDriver())
                            <span style="background: #28a745; color: white; padding: 0.25rem 0.5rem; border-radius: 0.25rem; font-size: 0.85rem;">Conductor</span>
                        @else
                            <span style="background: #007bff; color: white; padding: 0.25rem 0.5rem; border-radius: 0.25rem; font-size: 0.85rem;">Pasajero</span>
                        @endif
                    </td>
                    <td style="padding: 0.75rem;">
                        @if($user->id === auth()->id())
                            <span style="color: #28a745;">Activo (Usuario actual)</span>
                        @else
                            <form action="{{ route('admin.users.updateStatus', $user) }}" method="POST" style="margin: 0;">
                                @csrf
                                @method('PATCH')
                                <select name="status" onchange="this.form.submit()" style="padding: 0.5rem; border: 1px solid #ddd; border-radius: 0.25rem; cursor: pointer; background: white;">
                                    <option value="active" {{ $user->status === 'active' ? 'selected' : '' }}>Activo</option>
                                    <option value="inactive" {{ $user->status === 'inactive' ? 'selected' : '' }}>Inactivo</option>
                                    <option value="pending" {{ $user->status === 'pending' ? 'selected' : '' }}>Pendiente</option>
                                </select>
                            </form>
                        @endif
                    </td>
                    <td style="padding: 0.75rem;">{{ $user->created_at->format('d/m/Y') }}</td>
                    <td style="padding: 0.75rem;">
                        @if($user->id === auth()->id())
                            <span style="color: #666; font-style: italic;">(Usuario actual)</span>
                        @else
                            <form action="{{ route('admin.users.destroy', $user) }}" method="POST" style="display: inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" onclick="return confirm('¿Eliminar usuario {{ $user->nombre }}?')" style="background: #dc3545; color: white; border: none; padding: 0.5rem 1rem; border-radius: 0.25rem; cursor: pointer; font-size: 0.85rem; white-space: nowrap;">Desactivar</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    </div>
</section>

<script>
function filterUsers() {
    const roleFilter = document.getElementById('roleFilter').value;
    const statusFilter = document.getElementById('statusFilter').value;
    const rows = document.querySelectorAll('tbody tr');
    
    rows.forEach(row => {
        const role = row.dataset.role;
        const status = row.dataset.status;
        
        const roleMatch = !roleFilter || role === roleFilter;
        const statusMatch = !statusFilter || status === statusFilter;
        
        row.style.display = (roleMatch && statusMatch) ? '' : 'none';
    });
}
</script>
@endsection
