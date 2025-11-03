<?php
// API para el inicio de sesión - verifica credenciales y crea la sesión
require_once __DIR__ . '/../config.php';
session_start();

// Si no es POST, mostrar un formulario básico de respaldo
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo '<form method="POST"><input name="email"><input name="password"><button>Login</button></form>';
    exit;
}

// Obtener los datos del formulario
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

// Buscar el usuario en la base de datos
$pdo = db_connect();
$stmt = $pdo->prepare('SELECT id, password, role, status FROM users WHERE email = ?');
$stmt->execute([$email]);
$user = $stmt->fetch();

// Verificar si el usuario existe
if (!$user) {
    die('Email o contraseña incorrectos');
}

// Solo usuarios activos pueden entrar - los pendientes tienen que activar su cuenta primero
if ($user['status'] !== 'active') {
    die('Tu cuenta está pendiente o inactiva. Estado: ' . $user['status']);
}

// Verificar que la contraseña sea correcta
if (!password_verify($password, $user['password'])) {
    die('Email o contraseña incorrectos');
}

// Todo bien, crear la sesión
$_SESSION['user_id'] = $user['id'];
$_SESSION['role'] = $user['role'];

// Redirigir al dashboard principal
header('Location: ' . BASE_URL . '/dashboard.php');
exit;

?>
