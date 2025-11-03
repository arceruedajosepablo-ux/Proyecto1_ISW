<?php
// Página de administración - solo para los jefes del sistema
require_once __DIR__ . '/private.php';

// Verificar que sea administrador - si no, que se vaya al dashboard normal
if ($_SESSION['role'] !== 'admin') {
    header('Location: dashboard.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<!-- 
    Panel de administración donde se pueden gestionar todos los usuarios
    Crear nuevos admins, activar/desactivar usuarios, etc.
    Solo los administradores pueden ver esta página
-->
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./CSS/styleDash.css">
    <link rel="stylesheet" href="./CSS/styleAdmin.css">
    <title>Administración de Usuarios</title>
</head>
<body>
    <div class="container">
        <header>
            <img src="./imagenes/logo.png" alt="Logo" class="logo">
            <div class="user-welcome">
                <span>Bienvenido, <?php echo htmlspecialchars($currentUser['nombre'] . ' ' . $currentUser['apellido']); ?></span>
                <a href="./api/logout.php" class="logout-btn">Cerrar sesión</a>
            </div>
        </header>

        <nav class="navbar">
            <ul>
                <li><a href="./dashboard.php">Dashboard</a></li>
                <li><a href="./index.html" class="active">Rides</a></li>
                <?php if ($_SESSION['role'] === 'driver' or $_SESSION['role'] === 'admin' ): ?>
                <li><a href="./vehicles.php">Vehículos</a></li>
                <?php endif; ?>
                <?php if ($_SESSION['role'] === 'admin' ): ?>
                <li><a href="./admin.php">Admin</a></li>
                <?php endif; ?>
                <li><a href="/settings.html">Settings</a></li>
            </ul>
        </nav>

        <div class="breadcrumb">
            <a href="./dashboard.php">Dashboard ></a> <span>Administración</span>
        </div>

        <section class="admin-section">
            <div class="section-header">
                <h2>Gestión de Usuarios</h2>
                <button onclick="showAddAdminForm()" class="add-button">+ Admin</button>
            </div>

            <div id="adminForm" style="display: none;" class="form-container">
                <h3>Crear Nuevo Administrador</h3>
                <form id="addAdminForm">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="nombre">Nombre</label>
                            <input type="text" id="nombre" name="nombre" required>
                        </div>
                        <div class="form-group">
                            <label for="apellido">Apellido</label>
                            <input type="text" id="apellido" name="apellido" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Contraseña</label>
                        <input type="password" id="password" name="password" required minlength="8">
                    </div>
                    <input type="hidden" name="action" value="create_admin">
                    <div class="form-actions">
                        <button type="button" onclick="hideAddAdminForm()" class="cancel">Cancelar</button>
                        <button type="submit" class="save">Crear Administrador</button>
                    </div>
                </form>
            </div>

            <div class="users-table">
                <table>
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Email</th>
                            <th>Rol</th>
                            <th>Estado</th>
                            <th>Fecha Registro</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="usersList">
                        <tr><td colspan="6">Cargando usuarios...</td></tr>
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <script>
        function showAddAdminForm() {
            document.getElementById('adminForm').style.display = 'block';
        }

        function hideAddAdminForm() {
            document.getElementById('adminForm').style.display = 'none';
            document.getElementById('addAdminForm').reset();
        }

        async function loadUsers() {
            try {
                const res = await fetch('./api/admin.php');
                if (!res.ok) throw new Error('Error cargando usuarios');
                const data = await res.json();
                if (!data.success) throw new Error(data.error);

                const tbody = document.getElementById('usersList');
                tbody.innerHTML = data.data.map(u => `
                    <tr>
                        <td>${escapeHtml(u.nombre)} ${escapeHtml(u.apellido)}</td>
                        <td>${escapeHtml(u.email)}</td>
                        <td>${escapeHtml(u.role)}</td>
                        <td>
                            <select onchange="updateUserStatus(${u.id}, this.value)" ${u.id === <?php echo $_SESSION['user_id']; ?> ? 'disabled' : ''}>
                                <option value="active" ${u.status === 'active' ? 'selected' : ''}>Activo</option>
                                <option value="inactive" ${u.status === 'inactive' ? 'selected' : ''}>Inactivo</option>
                                <option value="pending" ${u.status === 'pending' ? 'selected' : ''}>Pendiente</option>
                            </select>
                        </td>
                        <td>${new Date(u.created_at).toLocaleDateString()}</td>
                        <td>
                            ${u.id === <?php echo $_SESSION['user_id']; ?> ? 
                                '<span class="current-user">(Usuario actual)</span>' : 
                                `<button onclick="updateUserStatus(${u.id}, '${u.status === 'active' ? 'inactive' : 'active'}')" class="status-btn ${u.status === 'active' ? 'deactivate' : 'activate'}">
                                    ${u.status === 'active' ? 'Desactivar' : 'Activar'}
                                </button>`
                            }
                        </td>
                    </tr>
                `).join('');
            } catch (err) {
                console.error(err);
                alert('Error cargando usuarios: ' + err.message);
            }
        }

        async function updateUserStatus(userId, newStatus) {
            try {
                const form = new FormData();
                form.append('action', 'update_status');
                form.append('user_id', userId);
                form.append('status', newStatus);

                const res = await fetch('./api/admin.php', {
                    method: 'POST',
                    body: form
                });

                const data = await res.json();
                if (!data.success) throw new Error(data.error);
                
                alert(data.message);
                loadUsers();
            } catch (err) {
                console.error(err);
                alert('Error actualizando estado: ' + err.message);
            }
        }

        document.getElementById('addAdminForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            try {
                const form = new FormData(e.target);
                const res = await fetch('./api/admin.php', {
                    method: 'POST',
                    body: form
                });

                const data = await res.json();
                if (!data.success) throw new Error(data.error);
                
                alert(data.message);
                hideAddAdminForm();
                loadUsers();
            } catch (err) {
                console.error(err);
                alert('Error creando administrador: ' + err.message);
            }
        });

        function escapeHtml(s) {
            return String(s).replace(/[&<>"']/g, c => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#39;'
            }[c]));
        }

        document.addEventListener('DOMContentLoaded', loadUsers);
    </script>
</body>
</html>