<?php
require_once __DIR__ . '/../config.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // show a minimal form fallback
    echo '<form method="POST"><input name="email"><input name="password"><button>Login</button></form>';
    exit;
}

$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

$pdo = db_connect();
$stmt = $pdo->prepare('SELECT id, password, role, status FROM users WHERE email = ?');
$stmt->execute([$email]);
$user = $stmt->fetch();

if (!$user) {
    die('Credenciales inválidas');
}

if ($user['status'] !== 'active') {
    die('Cuenta pendiente o inactiva. Estado: ' . $user['status']);
}

if (!password_verify($password, $user['password'])) {
    die('Credenciales inválidas');
}

// Autenticado
$_SESSION['user_id'] = $user['id'];
$_SESSION['role'] = $user['role'];

// Redirigir al dashboard protegido
header('Location: ' . BASE_URL . '/dashboard.php');
exit;

?>
