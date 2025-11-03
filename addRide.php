<?php
// Página para crear y editar rides - aquí los conductores publican sus viajes
require_once __DIR__ . '/private.php';
?>
<!DOCTYPE html>
<html lang="es">
<!-- 
    Formulario para que los conductores creen nuevos rides o editen los existentes
    Aquí definen origen, destino, fecha, hora, precio y cuántos espacios ofrecen
-->
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/CSS/styleAddRide.css">
    <title>Crear Ride</title>

</head>


<body>

    <!-- Contenedor del modal cargado mediante iframe -->
    <iframe id="modalFrame" src="/modal.html" style="display: none; width: 100%; height: 100%; border: none; position:fixed"></iframe>
    <div class="container">


        <header>
            <img src="/imagenes/logo.png" alt="Logo" class="logo">
            <div class="user-welcome">
                <span>Bienvenido, <?php echo htmlspecialchars($currentUser['nombre']); ?></span>
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
            <a href="/dashboard.php">Dashboard ></a> <a href="/addRide.php">Rides></a> <span>Nuevo</span>
        </div>
        <form id="addRideForm" method="POST">
            <div class="form-group">
                <label for="nombre">Nombre del Ride</label>
                <input type="text" id="nombre" name="nombre" required>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="origen">Inicia en</label>
                    <input type="text" id="origen" name="origen" required>
                </div>
                <div class="form-group">
                    <label for="destino">Finaliza en</label>
                    <input type="text" id="destino" name="destino" required>
                </div>
            </div>
            <div class="form-group">
                <label for="vehicle_id">Vehículo</label>
                <select id="vehicle_id" name="vehicle_id" required>
                    <option value="">Cargando...</option>
                </select>
            </div>
            <div class="form-group">
                <label for="fecha">Fecha</label>
                <input type="date" id="fecha" name="fecha" required>
            </div>
            <div class="form-group">
                <label for="hora">Hora</label>
                <input type="time" id="hora" name="hora" required>
            </div>
            <div class="form-group">
                <label for="costo">Costo por espacio</label>
                <input type="number" step="0.01" id="costo" name="costo" required>
            </div>
            <div class="form-group">
                <label for="espacios">Cantidad de espacios</label>
                <input type="number" id="espacios" name="espacios" min="1" value="1" required>
            </div>
            <input type="hidden" name="action" value="create">
            <input type="hidden" id="ride_id" name="id" value="">
            <div class="form-actions">
                <button type="reset" class="cancel" onclick="location.href='/dashboard.php'">Cancelar</button>
                <button type="submit" class="save">Guardar Ride</button>
            </div>
        </form>

        <script>
            // Verificar si estamos en modo edición
            const urlParams = new URLSearchParams(window.location.search);
            const rideId = urlParams.get('id');
            const isEditMode = !!rideId;

            // Función para cargar los datos del ride si estamos en modo edición
            async function loadRideData() {
                if (!isEditMode) return;
                
                try {
                    const response = await fetch(`/api/rides.php?id=${rideId}`);
                    if (!response.ok) throw new Error('No se pudo cargar el ride');
                    
                    const ride = await response.json();
                    
                    if (!ride || typeof ride !== 'object') {
                        throw new Error('El formato de respuesta no es válido');
                    }
                    
                    // Actualizar el título y breadcrumb
                    document.title = 'Editar Ride';
                    document.querySelector('.breadcrumb span').textContent = 'Editar';
                    
                    // Llenar el formulario con los datos del ride
                    document.getElementById('ride_id').value = ride.id;
                    document.getElementById('nombre').value = ride.nombre || '';
                    document.getElementById('origen').value = ride.origen || '';
                    document.getElementById('destino').value = ride.destino || '';
                    
                    // Formatear la fecha (asumiendo que viene en formato ISO o MySQL)
                    if (ride.fecha) {
                        const fecha = new Date(ride.fecha);
                        document.getElementById('fecha').value = fecha.toISOString().split('T')[0];
                    }
                    
                    // Formatear la hora (asumiendo que viene en formato HH:mm:ss)
                    if (ride.hora) {
                        document.getElementById('hora').value = ride.hora.substring(0, 5); // Tomar solo HH:mm
                    }
                    
                    document.getElementById('costo').value = ride.costo || '0';
                    document.getElementById('espacios').value = ride.espacios || '1';
                    
                    // Esperar a que se carguen los vehículos antes de seleccionar el correcto
                    await loadVehicles();
                    document.getElementById('vehicle_id').value = ride.vehicle_id;
                    
                } catch (error) {
                    console.error('Error:', error);
                    alert('Error al cargar los datos del ride');
                }
            }

            async function loadVehicles() {
                try {
                    const res = await fetch('/api/vehicles.php');
                    if (!res.ok) throw new Error('No autorizado o error');
                    const data = await res.json();
                    const sel = document.getElementById('vehicle_id');
                    sel.innerHTML = '';
                    if (data.length === 0) {
                        sel.innerHTML = '<option value="">No tienes vehículos registrados</option>';
                        document.querySelector('button[type="submit"]').disabled = true;
                        if (confirm('Necesitas registrar un vehículo antes de crear un ride. ¿Deseas agregar un vehículo ahora?')) {
                            window.location.href = '/vehicles.php';
                        }
                        return;
                    }
                    document.querySelector('button[type="submit"]').disabled = false;
                    data.forEach(v => {
                        const opt = document.createElement('option');
                        opt.value = v.id;
                        opt.textContent = v.marca + ' ' + v.modelo + ' (' + v.anio + ') - ' + v.placa;
                        sel.appendChild(opt);
                    });
                } catch (err) {
                    console.error(err);
                    const sel = document.getElementById('vehicle_id');
                    sel.innerHTML = '<option value="">Error cargando vehículos</option>';
                }
            }
            document.addEventListener('DOMContentLoaded', () => {
                loadVehicles();
                loadRideData(); // Cargar datos del ride si estamos en modo edición
            });
            
            document.getElementById('addRideForm').addEventListener('submit', async function(e){
                e.preventDefault();
                const form = e.target;
                const data = new FormData(form);
                const method = isEditMode ? 'PUT' : 'POST';
                
                try {
                    const res = await fetch('/api/rides.php', { 
                        method: method, 
                        body: data 
                    });
                    const result = await res.json();
                    
                    if (result.success) {
                        alert(result.message);
                        // Usar URL absoluta
                        window.location.href = '/dashboard.php';
                    } else {
                        alert('Error: ' + result.error);
                    }
                } catch(err) { 
                    alert('Error de conexión. Por favor, intenta nuevamente.'+err);
                    console.error(err); 
                    console.log(err);
                    
                    ;   
                }
            });
        </script>

       
    </div>

    <script src="/JS/scripts.js"></script>
</body>

</html>
