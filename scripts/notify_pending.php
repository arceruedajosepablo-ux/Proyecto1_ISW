<?php
// Script para recordar a los conductores sobre reservas pendientes
// Se ejecuta desde línea de comandos: php scripts/notify_pending.php [minutos]
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/send_mail.php';

$minutes = $argv[1] ?? 60; // Por defecto 60 minutos
$pdo = db_connect();

// Buscar reservas que lleven mucho tiempo pendientes
$sql = "SELECT r.id AS reservation_id, r.created_at, r.ride_id, rides.user_id AS driver_id, rides.nombre AS ride_name, u.email AS driver_email
        FROM reservations r
        JOIN rides ON r.ride_id = rides.id
        JOIN users u ON rides.user_id = u.id
        WHERE r.status = 'pending' AND r.created_at <= (NOW() - INTERVAL ? MINUTE)";

$stmt = $pdo->prepare($sql);
$stmt->execute([$minutes]);
$rows = $stmt->fetchAll();

if (!$rows) {
    echo "No hay reservas pendientes de hace más de $minutes minutos.\n";
    exit;
}

// Agrupar las reservas por conductor para mandar un solo email por conductor
$grouped = [];
foreach ($rows as $row) {
    $driver = $row['driver_email'];
    $grouped[$driver][] = $row;
}

foreach ($grouped as $driverEmail => $reservations) {
    $body = "<p>Tienes " . count($reservations) . " solicitudes de reserva pendientes.</p>";
    $body .= "<ul>";
    foreach ($reservations as $r) {
        $body .= "<li>Reserva #" . $r['reservation_id'] . " para ride: " . htmlspecialchars($r['ride_name']) . " (creada: " . $r['created_at'] . ")</li>";
    }
    $body .= "</ul>";
    $file = send_local_mail($driverEmail, 'Reservas pendientes', $body);
    echo "Correo creado para $driverEmail -> $file\n";
}

?>
