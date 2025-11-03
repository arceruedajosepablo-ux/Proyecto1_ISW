<?php
// API simple para cerrar sesión - limpia todo y manda de vuelta al inicio
require_once __DIR__ . '/../config.php';
session_start();
session_unset();      // Limpiar variables de sesión
session_destroy();    // Destruir la sesión completamente
// Redirigir a la página principal
header('Location: ' . BASE_URL . '/index.html');
exit;
?>
