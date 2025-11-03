<?php
// Include en páginas privadas para forzar login y estado activo
require_once __DIR__ . '/config.php';
session_start();

// Si no hay sesión, redirigir a login
if (empty($_SESSION['user_id'])) {
    // podemos añadir un parámetro para mostrar mensaje
    header('Location: ' . BASE_URL . '/login.html?msg=not_authenticated');
    exit;
}

$pdo = db_connect();
$stmt = $pdo->prepare('SELECT id, status, role, nombre, apellido FROM users WHERE id = ?');
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();
if (!$user) {
    session_unset();
    session_destroy();
    header('Location: ' . BASE_URL . '/login.html?msg=not_authenticated');
    exit;
}

if ($user['status'] !== 'active') {
    // Si está pending o inactive no permitir acceso
    session_unset();
    session_destroy();
    header('Location: ' . BASE_URL . '/login.html?msg=account_' . $user['status']);
    exit;
}

// Exponer $currentUser para las páginas
$currentUser = $user;

?>
