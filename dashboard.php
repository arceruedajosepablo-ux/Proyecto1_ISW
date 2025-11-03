<?php
// Dashboard principal - aquí los usuarios manejan sus rides y reservas
// Solo pueden entrar usuarios logueados por el private.php
require_once __DIR__ . '/private.php';
?>
<!DOCTYPE html>
<html lang="es">
<!-- 
    Esta es la página principal después de loguearse
    Aquí cada usuario puede ver y manejar sus rides o reservas según su rol
-->
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/CSS/styleDash.css">
    <title>Dashboard de Rides</title>

</head>


<body>

    <!-- Contenedor del modal cargado mediante iframe -->
    <iframe id="modalFrame" src="/modal.html" style="display: none; width: 100%; height: 100%; border: none; position:fixed"></iframe>
    <div class="container">


        <header>
            <img src="/imagenes/logo.png" alt="Logo" class="logo">
            <div class="user-welcome">
                <span>Bienvenido, <?php echo htmlspecialchars($currentUser['nombre'] . ' ' . $currentUser['apellido']); ?></span>
                <a href="/api/logout.php" class="logout-btn">Cerrar sesión</a>
            </div>
            
        </header>

        <!-- Barra de Navegación estilo pestañas -->
        <nav class="navbar">
            <ul>
                <li><a href="/dashboard.php">Dashboard</a></li>
                <li><a href="/index.html" class="active">Rides</a></li>
                <?php if ($_SESSION['role'] === 'driver' or $_SESSION['role'] === 'admin' ): ?>
                <li><a href="/vehicles.php">Vehículos</a></li>
                <?php endif; ?>
                <?php if ($_SESSION['role'] === 'admin' ): ?>
                <li><a href="/admin.php">Admin</a></li>
                <?php endif; ?>
                <li><a href="/settings.html">Settings</a></li>
            </ul>
        </nav>

        <!-- Indicación de la sección actual -->
        <div class="breadcrumb">
            <a href="/dashboard.php">Dashboard ></a>
        </div>

        <section class="rides-section">
            <div class="section-header">
                <h2>Mis Rides</h2>
                <a href="/addRide.php" class="add-button">+</a>
            </div>
            <table id="myRidesTable">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Start</th>
                        <th>End</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td colspan="4">Cargando rides...</td></tr>
                </tbody>
            </table>
        </section>
    </div>

    <script>
        async function loadMyRides() {
            try {
                const res = await fetch('/api/rides.php');
                if (!res.ok) throw new Error('No autorizado');
                const data = await res.json();
                const tbody = document.querySelector('#myRidesTable tbody');
                tbody.innerHTML = '';
                if (!data.length) { tbody.innerHTML = '<tr><td colspan="4">No tienes rides</td></tr>'; return; }
                data.forEach(r => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `<td>${escapeHtml(r.nombre)}</td><td>${escapeHtml(r.origen)}</td><td>${escapeHtml(r.destino)}</td><td><a href="#" data-id="${r.id}" class="edit">Edit</a> - <a href="#" data-id="${r.id}" class="delete">Delete</a></td>`;
                    tbody.appendChild(tr);
                });

                // Agregar event listeners para los botones
                document.querySelectorAll('.edit').forEach(btn => {
                    btn.addEventListener('click', async (e) => {
                        e.preventDefault();
                        const rideId = e.target.dataset.id;
                        window.location.href = `/addRide.php?id=${rideId}`;
                    });
                });

                document.querySelectorAll('.delete').forEach(btn => {
                    btn.addEventListener('click', async (e) => {
                        e.preventDefault();
                        const rideId = e.target.dataset.id;
                        
                        if (confirm('¿Estás seguro de que quieres eliminar este ride?')) {
                            try {
                                const response = await fetch(`/api/rides.php?id=${rideId}`, {
                                    method: 'DELETE',
                                    headers: {
                                        'Accept': 'application/json'
                                    }
                                });

                                const result = await response.json();
                                
                                if (result.success) {
                                    alert('Ride eliminado exitosamente');
                                    loadMyRides(); // Recargar la tabla
                                } else {
                                    alert('Error al eliminar el ride: ' + result.error);
                                }
                            } catch (err) {
                                console.error('Error:', err);
                                alert('Error de conexión al intentar eliminar el ride');
                            }
                        }
                    });
                });
            } catch (err) { 
                console.error(err);
                document.querySelector('#myRidesTable tbody').innerHTML = '<tr><td colspan="4">Error al cargar los rides</td></tr>';
            }
        }
        function escapeHtml(s){ return String(s).replace(/[&<>"']/g, function(c){ return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":"&#39;"}[c]; }); }
        document.addEventListener('DOMContentLoaded', loadMyRides);
    </script>
</body>

</html>
