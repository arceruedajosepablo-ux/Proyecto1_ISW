<?php
// Middleware para proteger páginas privadas
session_start();

function requireRole($allowedRoles = []) {
    // Verificar si hay sesión activa
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.html');
        exit();
    }

    // Si no se especifican roles, solo verificar login
    if (empty($allowedRoles)) {
        return;
    }

    // Verificar si el rol del usuario está permitido
    if (!in_array($_SESSION['role'], $allowedRoles)) {
        header('HTTP/1.1 403 Forbidden');
        echo "Acceso denegado";
        exit();
    }
}
?>