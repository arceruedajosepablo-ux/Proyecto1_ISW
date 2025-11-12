<?php
// Página para gestionar vehículos - solo para conductores y administradores
require_once __DIR__ . '/private.php';

// Solo conductores y admins pueden manejar vehículos
// Los pasajeros no tienen vehículos, obvio
if ($_SESSION['role'] !== 'driver' && $_SESSION['role'] !== 'admin') {
    header('Location: dashboard.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<!-- 
    Aquí los conductores pueden agregar, editar y eliminar sus vehículos
    Necesitan registrar al menos un vehículo para poder crear rides
-->
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./CSS/styleDash.css">
    <title>Mis Vehículos</title>
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
                <li><a href="#">Settings</a></li>
            </ul>
        </nav>

        <div class="breadcrumb">
            <a href="./dashboard.php">Dashboard ></a> <span>Vehículos</span>
        </div>

        <section class="vehicles-section">
            <div class="section-header">
                <h2>Mis Vehículos</h2>
                <button onclick="showAddVehicleForm()" class="add-button">+</button>
            </div>

            <div id="vehicleForm" style="display: none;" class="form-container">
                <form id="addVehicleForm" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="placa">Placa</label>
                        <input type="text" id="placa" name="placa" required>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="marca">Marca</label>
                            <input type="text" id="marca" name="marca" required>
                        </div>
                        <div class="form-group">
                            <label for="modelo">Modelo</label>
                            <input type="text" id="modelo" name="modelo" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="anio">Año</label>
                            <input type="number" id="anio" name="anio" required min="1900" max="2025">
                        </div>
                        <div class="form-group">
                            <label for="color">Color</label>
                            <input type="text" id="color" name="color" required>
                        </div>
                        <div class="form-group">
                            <label for="capacidad">Capacidad</label>
                            <input type="number" id="capacidad" name="capacidad" required min="1" max="20" value="4">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="foto">Foto del vehículo</label>
                        <input type="file" id="foto" name="foto" accept="image/*">
                    </div>
                    <input type="hidden" name="action" value="create">
                    <div class="form-actions">
                        <button type="button" onclick="hideAddVehicleForm()" class="cancel">Cancelar</button>
                        <button type="submit" class="save">Guardar Vehículo</button>
                    </div>
                </form>
            </div>

            <div id="vehiclesList" class="vehicles-grid">
                <p>Cargando vehículos...</p>
            </div>
        </section>
    </div>

    <script>
        function showAddVehicleForm() {
            document.getElementById('vehicleForm').style.display = 'block';
            document.getElementById('addVehicleForm').reset();
        }

        function hideAddVehicleForm() {
            document.getElementById('vehicleForm').style.display = 'none';
        }

        async function loadVehicles() {
            try {
                const res = await fetch('./api/vehicles.php');
                if (!res.ok) throw new Error('Error cargando vehículos');
                const vehicles = await res.json();
                const container = document.getElementById('vehiclesList');
                
                if (!vehicles.length) {
                    container.innerHTML = '<p>No tienes vehículos registrados. ¡Agrega uno!</p>';
                    return;
                }

                container.innerHTML = vehicles.map(v => `
                    <div class="vehicle-card">
                        ${v.foto ? `<img src="${v.foto}" alt="${v.marca} ${v.modelo}">` : '<div class="no-photo">Sin foto</div>'}
                        <div class="vehicle-info">
                            <h3>${escapeHtml(v.marca)} ${escapeHtml(v.modelo)} (${escapeHtml(v.anio)})</h3>
                            <p>Placa: ${escapeHtml(v.placa)}</p>
                            <p>Color: ${escapeHtml(v.color)}</p>
                            <p>Capacidad: ${escapeHtml(v.capacidad)} personas</p>
                            <div class="vehicle-actions">
                                <button onclick="editVehicle(${v.id})" class="edit">Editar</button>
                                <button onclick="deleteVehicle(${v.id})" class="delete">Eliminar</button>
                            </div>
                        </div>
                    </div>
                `).join('');
            } catch (err) {
                console.error(err);
                document.getElementById('vehiclesList').innerHTML = '<p>Error cargando vehículos</p>';
            }
        }

        async function deleteVehicle(id) {
            if (!confirm('¿Estás seguro de eliminar este vehículo?')) return;
            try {
                const form = new FormData();
                form.append('action', 'delete');
                form.append('id', id);
                const res = await fetch('./api/vehicles.php', {
                    method: 'POST',
                    body: form
                });
                const text = await res.text();
                alert(text);
                if (res.ok) loadVehicles();
            } catch (err) {
                console.error(err);
                alert('Error eliminando vehículo');
            }
        }

         async function editVehicle(id) {
            try {
                // Obtener datos del vehículo
                const res = await fetch(`./api/vehicles.php?id=${id}`);
                if (!res.ok) throw new Error('Error obteniendo datos del vehículo');
                const vehicles = await res.json();
                
                // Buscar el vehículo específico en la lista
                const vehicle = vehicles.find(v => v.id == id);
                if (!vehicle) {
                    throw new Error('Vehículo no encontrado');
                }
                
                // Llenar el formulario con los datos existentes
                document.getElementById('placa').value = vehicle.placa;
                document.getElementById('marca').value = vehicle.marca;
                document.getElementById('modelo').value = vehicle.modelo;
                document.getElementById('anio').value = vehicle.anio;
                document.getElementById('color').value = vehicle.color;
                document.getElementById('capacidad').value = vehicle.capacidad;
                
                // Cambiar la acción del formulario a editar
                const actionInput = document.querySelector('input[name="action"]');
                actionInput.value = 'edit';
                
                // Agregar el ID del vehículo al formulario
                let idInput = document.querySelector('input[name="id"]');
                if (!idInput) {
                    idInput = document.createElement('input');
                    idInput.type = 'hidden';
                    idInput.name = 'id';
                    document.getElementById('addVehicleForm').appendChild(idInput);
                }
                idInput.value = id;
                
                // Cambiar el texto del botón
                const submitButton = document.querySelector('.form-actions .save');
                submitButton.textContent = 'Actualizar Vehículo';
                
                // Mostrar el formulario
                document.getElementById('vehicleForm').style.display = 'block';
                
            } catch (err) {
                console.error(err);
                alert('Error cargando datos del vehículo');
            }
        }

        document.getElementById('addVehicleForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            try {
                const form = new FormData(e.target);
                const res = await fetch('./api/vehicles.php', {
                    method: 'POST',
                    body: form
                });
                const text = await res.text();
                alert(text);
                if (res.ok) {
                    hideAddVehicleForm();
                    loadVehicles();
                }
            } catch (err) {
                console.error(err);
                alert('Error guardando vehículo');
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

        document.addEventListener('DOMContentLoaded', loadVehicles);
    </script>
</body>
</html>