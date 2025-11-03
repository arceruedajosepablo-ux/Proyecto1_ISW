<?php
require_once __DIR__ . '/../config.php';

$token = $_GET['token'] ?? '';
if (empty($token)) {
    die('Token faltante');
}

$pdo = db_connect();
$stmt = $pdo->prepare('SELECT id, status FROM users WHERE activation_token = ?');
$stmt->execute([$token]);
$user = $stmt->fetch();
if (!$user) {
    die('Token inválido');
}

if ($user['status'] === 'active') {
    echo 'La cuenta ya está activa.';
    exit;
}

$update = $pdo->prepare('UPDATE users SET status = ?, activation_token = NULL WHERE id = ?');
$update->execute(['active', $user['id']]);

echo 'Cuenta activada. Ya puedes iniciar sesión.';

?>
