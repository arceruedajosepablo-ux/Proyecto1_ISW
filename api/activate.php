<?php
// Página para activar cuentas nuevas - los usuarios llegan aquí desde el email
require_once __DIR__ . '/../config.php';

// Obtener el token del enlace que mandamos por correo
$token = $_GET['token'] ?? '';
if (empty($token)) {
    die('Falta el token de activación');
}

// Buscar el usuario con ese token
$pdo = db_connect();
$stmt = $pdo->prepare('SELECT id, status FROM users WHERE activation_token = ?');
$stmt->execute([$token]);
$user = $stmt->fetch();

if (!$user) {
    die('Token inválido o ya fue usado');
}

// Si ya está activo, no hacer nada
if ($user['status'] === 'active') {
    echo 'Tu cuenta ya está activa, podés iniciar sesión.';
    exit;
}

// Activar la cuenta y borrar el token
$update = $pdo->prepare('UPDATE users SET status = ?, activation_token = NULL WHERE id = ?');
$update->execute(['active', $user['id']]);

echo '¡Cuenta activada exitosamente! Ya podés iniciar sesión en el sistema.';

?>
