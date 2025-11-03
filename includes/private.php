<?php
// Funciones auxiliares para proteger páginas del sistema
// Como un portero que verifica quién puede entrar y quién no
session_start();

function requireRole($allowedRoles = []) {
    // Primero revisar si la persona está logueada
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.html');
        exit();
    }

    // Si no importa el rol, con que esté logueado basta
    if (empty($allowedRoles)) {
        return;
    }

    // Verificar que tenga el rol correcto (admin, conductor, pasajero)
    if (!in_array($_SESSION['role'], $allowedRoles)) {
        header('HTTP/1.1 403 Forbidden');
        echo "No tenés permisos para ver esto";
        exit();
    }
}
?>