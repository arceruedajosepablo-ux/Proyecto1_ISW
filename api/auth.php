<?php
// Archivo de autenticación - funciones para verificar usuarios y permisos
// Todas las APIs lo usan para validar que solo entren usuarios autorizados
require_once __DIR__ . '/../config.php';
session_start();

// Función para verificar que el usuario esté logueado
function require_login() {
    if (empty($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(['error' => 'No estás logueado']);
        exit;
    }
}

// Función para verificar que el usuario tenga el rol correcto
// Por ejemplo, solo admins pueden gestionar usuarios
function require_role($roles = []) {
    require_login();
    if (!in_array($_SESSION['role'], (array)$roles)) {
        http_response_code(403);
        echo json_encode(['error' => 'No tienes permisos para esto']);
        exit;
    }
}

// Función simple para obtener el ID del usuario actual
function current_user_id() {
    return $_SESSION['user_id'] ?? null;
}

// Si se accede directamente a auth.php, devolver el estado de autenticación
if (basename($_SERVER['SCRIPT_FILENAME']) === basename(__FILE__)) {
    header('Content-Type: application/json');
    if (isset($_SESSION['user_id'])) {
        echo json_encode([
            'logged_in' => true,
            'user_id' => $_SESSION['user_id'],
            'role' => $_SESSION['role'],
            'nombre' => $_SESSION['nombre'] ?? '',
            'apellido' => $_SESSION['apellido'] ?? ''
        ]);
    } else {
        echo json_encode([
            'logged_in' => false
        ]);
    }
    exit;
}
?>
