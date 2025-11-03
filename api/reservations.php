<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../scripts/send_mail.php';

$pdo = db_connect();
$method = $_SERVER['REQUEST_METHOD'];

// List reservations for current user (passenger sees their, driver sees ones for their rides)
if ($method === 'GET') {
    require_login();
    header('Content-Type: application/json');
    
    try {
        $user_id = current_user_id();
        
    if ($_SESSION['role'] === 'driver' || $_SESSION['role'] === 'admin') {
            // Los conductores ven las reservas de sus rides
            $stmt = $pdo->prepare("
                SELECT 
                    res.*,
                    rides.nombre AS ride_name,
                    rides.origen,
                    rides.destino,
                    rides.fecha,
                    rides.hora,
                    u.nombre AS passenger_nombre,
                    u.apellido AS passenger_apellido,
                    u.email AS passenger_email
                FROM reservations res 
                JOIN rides ON res.ride_id = rides.id 
                JOIN users u ON res.passenger_id = u.id
                WHERE rides.user_id = ? AND res.status = 'pending'
                ORDER BY rides.fecha ASC, rides.hora ASC
            ");
            $stmt->execute([$user_id]);
        } else {
            // Los pasajeros ven sus propias reservas
            $stmt = $pdo->prepare("
                SELECT 
                    res.*,
                    rides.nombre AS ride_name,
                    rides.origen,
                    rides.destino,
                    rides.fecha,
                    rides.hora,
                    rides.costo,
                    v.marca,
                    v.modelo,
                    v.anio,
                    u.nombre AS driver_nombre,
                    u.apellido AS driver_apellido
                FROM reservations res 
                JOIN rides ON res.ride_id = rides.id 
                JOIN vehicles v ON rides.vehicle_id = v.id
                JOIN users u ON rides.user_id = u.id
                WHERE res.passenger_id = ? 
                ORDER BY rides.fecha ASC, rides.hora ASC
            ");
            $stmt->execute([$user_id]);
        }
        
        $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Formatear fechas y datos
        $reservations = array_map(function($res) {
            $res['fecha'] = date('Y-m-d', strtotime($res['fecha']));
            $res['hora'] = date('H:i:s', strtotime($res['hora']));
            if (isset($res['costo'])) {
                $res['costo'] = number_format((float)$res['costo'], 2, '.', '');
            }
            return $res;
        }, $reservations);
        
        echo json_encode([
            'success' => true,
            'reservations' => $reservations
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Error obteniendo las reservaciones: ' . $e->getMessage()
        ]);
    }
    exit;
}

// POST actions: create / accept / reject / cancel
if ($method === 'POST') {
    require_login();
    $action = $_POST['action'] ?? 'create';
    $user_id = current_user_id();

    if ($action === 'create') {
        header('Content-Type: application/json');
        
        if ($_SESSION['role'] !== 'passenger') {
            http_response_code(403);
            echo json_encode([
                'success' => false,
                'error' => 'Solo los pasajeros pueden hacer reservaciones'
            ]);
            exit;
        }

        try {
            // Obtener datos del POST o JSON
            $input = $_POST;
            if (empty($_POST)) {
                $input = json_decode(file_get_contents('php://input'), true);
            }
            
            $ride_id = (int)($input['ride_id'] ?? 0);
            
            // Verificar que el ride existe y tiene espacios
            $stmt = $pdo->prepare("
                SELECT r.*, 
                       (SELECT COUNT(*) FROM reservations res 
                        WHERE res.ride_id = r.id 
                        AND res.status IN ('accepted', 'pending')) as espacios_reservados
                FROM rides r
                WHERE r.id = ? AND CONCAT(r.fecha, ' ', r.hora) >= NOW()
            ");
            $stmt->execute([$ride_id]);
            $ride = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$ride) {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'error' => 'Ride no encontrado o ya pasó la fecha'
                ]);
                exit;
            }

            // Verificar espacios disponibles
            if ($ride['espacios_reservados'] >= $ride['espacios']) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'error' => 'No hay espacios disponibles en este ride'
                ]);
                exit;
            }

            // Verificar si el usuario ya tiene una reservación para este ride
            $stmt = $pdo->prepare("
                SELECT id FROM reservations 
                WHERE ride_id = ? AND passenger_id = ? 
                AND status IN ('pending', 'accepted')
            ");
            $stmt->execute([$ride_id, $user_id]);
            if ($stmt->fetch()) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'error' => 'Ya tienes una reservación pendiente o aceptada para este ride'
                ]);
                exit;
            }

            // Crear la reservación
            $stmt = $pdo->prepare("
                INSERT INTO reservations (ride_id, passenger_id, status, created_at) 
                VALUES (?, ?, 'pending', NOW())
            ");
            $stmt->execute([$ride_id, $user_id]);
            $reservation_id = $pdo->lastInsertId();

            // Enviar notificación al conductor
            $stmt = $pdo->prepare("
                SELECT u.email, u.nombre, rides.nombre as ride_name 
                FROM rides 
                JOIN users u ON rides.user_id = u.id 
                WHERE rides.id = ?
            ");
            $stmt->execute([$ride_id]);
            $driver = $stmt->fetch();

            if ($driver) {
                $body = "<p>Tienes una nueva solicitud de reservación para tu ride '{$driver['ride_name']}'.</p>";
                send_local_mail($driver['email'], 'Nueva solicitud de reservación', $body);
            }

            echo json_encode([
                'success' => true,
                'message' => 'Reservación creada exitosamente. El conductor será notificado.',
                'id' => $reservation_id
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Error creando la reservación: ' . $e->getMessage()
            ]);
        }
        exit;
    }

    if ($action === 'accept' || $action === 'reject') {
        if ($_SESSION['role'] !== 'driver' && $_SESSION['role'] !== 'admin') { http_response_code(403); echo 'Solo chofer o admin puede aceptar/rechazar'; exit; }
        $id = (int)($_POST['id'] ?? 0);
        // verificar que reservation pertenece to a ride of this driver
        $stmt = $pdo->prepare('SELECT res.*, rides.user_id AS driver_id, users.email AS passenger_email FROM reservations res JOIN rides ON res.ride_id = rides.id JOIN users ON res.passenger_id = users.id WHERE res.id = ?');
        $stmt->execute([$id]);
        $r = $stmt->fetch();
        if (!$r) { http_response_code(404); echo 'Reserva no encontrada'; exit; }
        if ($r['driver_id'] != $user_id) { http_response_code(403); echo 'No autorizado'; exit; }
        // Verificar que la reserva esté en estado pendiente
        if ($r['status'] !== 'pending') {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'La reserva ya no está en estado pendiente'
            ]);
            exit;
        }

        $newStatus = $action === 'accept' ? 'accepted' : 'rejected';
        $upd = $pdo->prepare('UPDATE reservations SET status = ? WHERE id = ?');
        $upd->execute([$newStatus, $id]);

        // Notificar al pasajero
        $body = "<p>Tu reserva #{$id} ha sido " . $newStatus . " por el chofer.</p>";
        send_local_mail($r['passenger_email'], 'Estado de tu reserva', $body);
        
        // Retornar respuesta JSON
        echo json_encode([
            'success' => true,
            'message' => 'Reserva ' . ($action === 'accept' ? 'aceptada' : 'rechazada') . ' correctamente',
            'newStatus' => $newStatus
        ]);
        exit;
    }

    if ($action === 'cancel') {
        // passenger cancel
        $id = (int)($_POST['id'] ?? 0);
        $stmt = $pdo->prepare('SELECT * FROM reservations WHERE id = ?');
        $stmt->execute([$id]);
        $r = $stmt->fetch();
        if (!$r) { http_response_code(404); echo 'Reserva no encontrada'; exit; }
        if ($r['passenger_id'] != $user_id && $_SESSION['role'] !== 'admin') { http_response_code(403); echo 'No autorizado'; exit; }
        $upd = $pdo->prepare('UPDATE reservations SET status = ? WHERE id = ?');
        $upd->execute(['cancelled', $id]);
        echo 'Reserva cancelada';
        exit;
    }

    http_response_code(400);
    echo 'Acción inválida';
    exit;
}

http_response_code(405);
echo 'Method not allowed';

?>
