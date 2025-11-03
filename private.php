<?php
// Este archivo es como el guardia de seguridad del sistema
// Se incluye en todas las páginas privadas para asegurarse de que solo entren usuarios logueados
require_once __DIR__ . '/config.php';
session_start();

// Si la persona no está logueada, que se vaya al login
if (empty($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . '/login.html?msg=not_authenticated');
    exit;
}

// Verificar que el usuario todavía existe en la base de datos y está activo
$pdo = db_connect();
$stmt = $pdo->prepare('SELECT id, status, role, nombre, apellido FROM users WHERE id = ?');
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// Si no encontramos al usuario, limpiar la sesión y mandar al login
if (!$user) {
    session_unset();
    session_destroy();
    header('Location: ' . BASE_URL . '/login.html?msg=not_authenticated');
    exit;
}

// Solo usuarios activos pueden usar el sistema - si está pendiente o inactivo, fuera
if ($user['status'] !== 'active') {
    session_unset();
    session_destroy();
    header('Location: ' . BASE_URL . '/login.html?msg=account_' . $user['status']);
    exit;
}

// Exponer $currentUser para las páginas
$currentUser = $user;

?>
