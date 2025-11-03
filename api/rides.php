<?php
// API para manejar rides - crear, listar, editar y eliminar viajes
require_once __DIR__ . '/auth.php';

$method = $_SERVER['REQUEST_METHOD'];
$pdo = db_connect();

// Listado público de rides para la página principal
// Cualquiera puede ver esto, no necesita estar logueado
if ($method === 'GET' && isset($_GET['public']) && $_GET['public'] == '1') {
    header('Content-Type: application/json');

    try {
        // Filtros opcionales para buscar rides específicos
        $origen = $_GET['origen'] ?? null;
        $destino = $_GET['destino'] ?? null;
        $sort_by = $_GET['sort_by'] ?? 'fecha'; // Cómo ordenar los resultados
        $order = (isset($_GET['order']) && strtolower($_GET['order']) === 'desc') ? 'DESC' : 'ASC';

        // Validar campos de ordenamiento permitidos
        $allowedSort = [
            'fecha' => "CONCAT(r.fecha, ' ', r.hora)",
            'origen' => 'r.origen',
            'destino' => 'r.destino'
        ];
        $sortExpr = $allowedSort[$sort_by] ?? $allowedSort['fecha'];

        // Consulta base con JOIN para obtener datos del vehículo
        $sql = "SELECT 
                r.id,
                r.nombre,
                r.origen,
                r.destino,
                r.fecha,
                r.hora,
                r.costo,
                r.espacios,
                v.marca,
                v.modelo,
                v.anio,
                (SELECT COUNT(*) FROM reservations res WHERE res.ride_id = r.id AND res.status IN ('accepted', 'pending')) as espacios_reservados
            FROM rides r 
            JOIN vehicles v ON r.vehicle_id = v.id 
            WHERE CONCAT(r.fecha, ' ', r.hora) >= NOW()";
        
        $params = [];

        // Aplicar filtros de búsqueda
        if ($origen) {
            $sql .= " AND r.origen LIKE ?";
            $params[] = '%' . $origen . '%';
        }
        if ($destino) {
            $sql .= " AND r.destino LIKE ?";
            $params[] = '%' . $destino . '%';
        }

        // Solo mostrar rides con espacios disponibles
        $sql .= " HAVING espacios > espacios_reservados";
        
        // Aplicar ordenamiento
        $sql .= " ORDER BY {$sortExpr} {$order}";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $rides = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Formatear datos para el frontend
        $rides = array_map(function($ride) {
            // Calcular espacios disponibles
            $ride['espacios_disponibles'] = $ride['espacios'] - $ride['espacios_reservados'];
            unset($ride['espacios_reservados']); // No enviamos este dato al frontend

            // Formatear fecha y hora
            $ride['fecha'] = date('Y-m-d', strtotime($ride['fecha']));
            $ride['hora'] = date('H:i:s', strtotime($ride['hora']));
            
            // Formatear costo
            $ride['costo'] = number_format((float)$ride['costo'], 2, '.', '');
            
            return $ride;
        }, $rides);

        echo json_encode([
            'success' => true,
            'rides' => $rides
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Error obteniendo los rides: ' . $e->getMessage()
        ]);
    }
    exit;
}

// GET: list rides or get specific ride
if ($method === 'GET') {
    require_login();
    $user_id = current_user_id();
    
    // Get specific ride by ID
    if (isset($_GET['id'])) {
        $id = (int)$_GET['id'];
        $stmt = $pdo->prepare('SELECT r.*, v.marca, v.modelo, v.anio 
                              FROM rides r 
                              JOIN vehicles v ON r.vehicle_id = v.id 
                              WHERE r.id = ? AND (r.user_id = ? OR ? = "admin")');
        $stmt->execute([$id, $user_id, $_SESSION['role']]);
        $ride = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$ride) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Ride no encontrado']);
            exit;
        }
        
        // Formato consistente para fecha y hora
        $ride['fecha'] = date('Y-m-d', strtotime($ride['fecha']));
        $ride['hora'] = date('H:i:s', strtotime($ride['hora']));
        
        header('Content-Type: application/json');
        echo json_encode($ride);
        exit;
    }
    
    // List all rides
    if ($_SESSION['role'] === 'admin' && isset($_GET['all'])) {
        $stmt = $pdo->query('SELECT * FROM rides');
    } else {
        $stmt = $pdo->prepare('SELECT * FROM rides WHERE user_id = ?');
        $stmt->execute([$user_id]);
    }
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    header('Content-Type: application/json');
    echo json_encode($rows);
    exit;
}

// Handle PUT requests for updates
// Handle DELETE requests
if ($method === 'DELETE') {
    require_login();
    $user_id = current_user_id();
    
    // Get ID from URL parameter
    $id = (int)($_GET['id'] ?? 0);
    if (!$id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'ID del ride es requerido']);
        exit;
    }
    
    // Verificar propiedad del ride
    $stmt = $pdo->prepare('SELECT user_id FROM rides WHERE id = ?');
    $stmt->execute([$id]);
    $ride = $stmt->fetch();
    
    if (!$ride) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Ride no encontrado']);
        exit;
    }
    
    if ($ride['user_id'] != $user_id && $_SESSION['role'] !== 'admin') {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'No autorizado para eliminar este ride']);
        exit;
    }
    
    try {
        $del = $pdo->prepare('DELETE FROM rides WHERE id = ?');
        $del->execute([$id]);
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Ride eliminado exitosamente']);
    } catch (PDOException $e) {
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'error' => 'Error eliminando el ride: ' . $e->getMessage()
        ]);
    }
    exit;
}

if ($method === 'PUT') {
    require_login();
    $user_id = current_user_id();
    
    // Parse PUT data
    parse_str(file_get_contents("php://input"), $_PUT);
    
    $id = (int)($_PUT['id'] ?? 0);
    if (!$id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'ID del ride es requerido']);
        exit;
    }
    
    // Verificar propiedad del ride
    $stmt = $pdo->prepare('SELECT user_id FROM rides WHERE id = ?');
    $stmt->execute([$id]);
    $ride = $stmt->fetch();
    
    if (!$ride) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Ride no encontrado']);
        exit;
    }
    
    if ($ride['user_id'] != $user_id && $_SESSION['role'] !== 'admin') {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'No autorizado para editar este ride']);
        exit;
    }
    
    // Validar y actualizar datos
    $vehicle_id = (int)($_PUT['vehicle_id'] ?? 0);
    $nombre = trim($_PUT['nombre'] ?? '');
    $origen = trim($_PUT['origen'] ?? '');
    $destino = trim($_PUT['destino'] ?? '');
    $fecha = $_PUT['fecha'] ?? '';
    $hora = $_PUT['hora'] ?? '';
    $costo = floatval($_PUT['costo'] ?? 0);
    $espacios = (int)($_PUT['espacios'] ?? 1);
    
    try {
        $upd = $pdo->prepare('UPDATE rides SET 
            vehicle_id = ?, 
            nombre = ?, 
            origen = ?, 
            destino = ?, 
            fecha = ?, 
            hora = ?, 
            costo = ?, 
            espacios = ? 
            WHERE id = ?');
            
        $upd->execute([$vehicle_id, $nombre, $origen, $destino, $fecha, $hora, $costo, $espacios, $id]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Ride actualizado exitosamente'
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Error actualizando el ride: ' . $e->getMessage()
        ]);
    }
    exit;
}

// POST actions: create/edit/delete
if ($method === 'POST') {
    require_login();
    $action = $_POST['action'] ?? 'create';
    $user_id = current_user_id();

    if ($action === 'create') {
        if ($_SESSION['role'] !== 'driver' && $_SESSION['role'] !== 'admin') { 
            http_response_code(403); 
            echo json_encode(['success' => false, 'error' => 'Solo choferes pueden crear rides']); 
            exit; 
        }

        // Validar datos requeridos
        $required = ['vehicle_id', 'nombre', 'origen', 'destino', 'fecha', 'hora', 'costo', 'espacios'];
        foreach ($required as $field) {
            if (empty($_POST[$field])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => "El campo {$field} es requerido"]);
                exit;
            }
        }

        $vehicle_id = (int)($_POST['vehicle_id']);
        // verificar vehículo
        $vstmt = $pdo->prepare('SELECT id, capacidad FROM vehicles WHERE id = ? AND user_id = ?');
        $vstmt->execute([$vehicle_id, $user_id]);
        $vehicle = $vstmt->fetch();
        if (!$vehicle) { 
            http_response_code(400); 
            echo json_encode(['success' => false, 'error' => 'Vehículo inválido o no te pertenece']); 
            exit; 
        }

        $nombre = trim($_POST['nombre']);
        $origen = trim($_POST['origen']);
        $destino = trim($_POST['destino']);
        $fecha = $_POST['fecha'];
        $hora = $_POST['hora'];
        $costo = floatval($_POST['costo']);
        $espacios = (int)$_POST['espacios'];

        // Validaciones adicionales
        if ($espacios > $vehicle['capacidad']) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => "El número de espacios no puede ser mayor que la capacidad del vehículo ({$vehicle['capacidad']})"]);
            exit;
        }

        if ($fecha < date('Y-m-d')) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'La fecha no puede ser anterior a hoy']);
            exit;
        }

        if ($fecha == date('Y-m-d') && $hora < date('H:i')) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'La hora no puede ser anterior a la actual para viajes de hoy']);
            exit;
        }

        if ($costo < 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'El costo no puede ser negativo']);
            exit;
        }

        try {
            $stmt = $pdo->prepare('INSERT INTO rides (user_id, vehicle_id, nombre, origen, destino, fecha, hora, costo, espacios) VALUES (?,?,?,?,?,?,?,?,?)');
            $stmt->execute([$user_id, $vehicle_id, $nombre, $origen, $destino, $fecha, $hora, $costo, $espacios]);
            $rideId = $pdo->lastInsertId();
            echo json_encode([
                'success' => true, 
                'message' => 'Ride creado exitosamente',
                'id' => $rideId
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Error creando el ride: ' . $e->getMessage()]);
        }
        exit;
    }

    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        $stmt = $pdo->prepare('SELECT user_id FROM rides WHERE id = ?');
        $stmt->execute([$id]);
        $r = $stmt->fetch();
        if (!$r) { 
            http_response_code(404); 
            echo json_encode(['success' => false, 'error' => 'Ride no encontrado']); 
            exit; 
        }
        if ($r['user_id'] != $user_id && $_SESSION['role'] !== 'admin') { 
            http_response_code(403); 
            echo json_encode(['success' => false, 'error' => 'No autorizado para eliminar este ride']); 
            exit; 
        }
        $del = $pdo->prepare('DELETE FROM rides WHERE id = ?');
        $del->execute([$id]);
        echo json_encode(['success' => true, 'message' => 'Ride eliminado exitosamente']);
        exit;
    }

    if ($action === 'edit') {
        $id = (int)($_POST['id'] ?? 0);
        $stmt = $pdo->prepare('SELECT user_id FROM rides WHERE id = ?');
        $stmt->execute([$id]);
        $r = $stmt->fetch();
        if (!$r) { http_response_code(404); echo 'No encontrado'; exit; }
        if ($r['user_id'] != $user_id && $_SESSION['role'] !== 'admin') { http_response_code(403); echo 'No autorizado'; exit; }
        $vehicle_id = (int)($_POST['vehicle_id'] ?? 0);
        $nombre = $_POST['nombre'] ?? '';
        $origen = $_POST['origen'] ?? '';
        $destino = $_POST['destino'] ?? '';
        $fecha = $_POST['fecha'] ?? '';
        $hora = $_POST['hora'] ?? '';
        $costo = $_POST['costo'] ?? 0;
        $espacios = $_POST['espacios'] ?? 1;
        $upd = $pdo->prepare('UPDATE rides SET vehicle_id=?, nombre=?, origen=?, destino=?, fecha=?, hora=?, costo=?, espacios=? WHERE id=?');
        $upd->execute([$vehicle_id, $nombre, $origen, $destino, $fecha, $hora, $costo, $espacios, $id]);
        echo 'Ride actualizado';
        exit;
    }

    http_response_code(400);
    echo 'Acción inválida';
    exit;
}

http_response_code(405);
echo 'Method not allowed';

?>
