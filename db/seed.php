<?php
require_once __DIR__ . '/../config.php';

$pdo = db_connect();

// Crear tablas ejecutando init.sql (si no se ha importado manualmente)
$sql = file_get_contents(__DIR__ . '/init.sql');
$pdo->exec($sql);

// Insertar administrador por defecto si no existe
$adminEmail = 'admin@local.test';
$stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
$stmt->execute([$adminEmail]);
if (!$stmt->fetch()) {
    $password = password_hash('Admin123!', PASSWORD_DEFAULT);
    $insert = $pdo->prepare('INSERT INTO users (role, nombre, apellido, cedula, email, password, status) VALUES (?,?,?,?,?,?,?)');
    $insert->execute(['admin', 'Admin', 'User', '0000000000', $adminEmail, $password, 'active']);
    echo "Administrador creado: $adminEmail / Contraseña: Admin123!\n";
} else {
    echo "Administrador ya existe: $adminEmail\n";
}

echo "Seed complete.\n";

?>
