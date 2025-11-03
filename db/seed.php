<?php
// Script para inicializar la base de datos con datos básicos
// Crea las tablas y el usuario administrador por defecto
require_once __DIR__ . '/../config.php';

$pdo = db_connect();

// Ejecutar el script de creación de tablas
$sql = file_get_contents(__DIR__ . '/init.sql');
$pdo->exec($sql);

// Crear el administrador por defecto si no existe
$adminEmail = 'admin@local.test';
$stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
$stmt->execute([$adminEmail]);

if (!$stmt->fetch()) {
    // No existe entonces crearlo
    $password = password_hash('Admin123!', PASSWORD_DEFAULT);
    $insert = $pdo->prepare('INSERT INTO users (role, nombre, apellido, cedula, email, password, status) VALUES (?,?,?,?,?,?,?)');
    $insert->execute(['admin', 'Admin', 'toor', '0000000000', $adminEmail, $password, 'active']);
    echo "Administrador creado: $adminEmail / Contraseña: Admin123!\n";
} else {
    echo "El administrador ya existe: $adminEmail\n";
}

echo "Inicialización completada.\n";

?>
