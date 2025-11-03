<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../scripts/send_mail.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo 'Method not allowed';
    exit;
}

$role = isset($_POST['role']) && in_array($_POST['role'], ['driver','passenger']) ? $_POST['role'] : 'passenger';
$nombre = $_POST['nombre'] ?? '';
$apellido = $_POST['apellido'] ?? '';
$cedula = $_POST['cedula'] ?? '';
$fecha_nacimiento = $_POST['fecha_nacimiento'] ?? null;
$email = $_POST['email'] ?? '';
$telefono = $_POST['telefono'] ?? '';
$password = $_POST['password'] ?? '';
$password2 = $_POST['password2'] ?? '';

if (empty($email) || empty($password) || $password !== $password2) {
    die('Datos inválidos o contraseñas no coinciden');
}

// Manejar foto
$foto_path = null;
if (!empty($_FILES['foto']['tmp_name'])) {
    $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
    $target = __DIR__ . '/../uploads/' . uniqid('foto_') . '.' . $ext;
    move_uploaded_file($_FILES['foto']['tmp_name'], $target);
    $foto_path = 'uploads/' . basename($target);
}

$pdo = db_connect();

// Verificar email único
$stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
$stmt->execute([$email]);
if ($stmt->fetch()) {
    die('El correo ya está registrado');
}

$password_hash = password_hash($password, PASSWORD_DEFAULT);
$activation_token = bin2hex(random_bytes(16));

$insert = $pdo->prepare('INSERT INTO users (role, nombre, apellido, cedula, fecha_nacimiento, email, telefono, foto, password, activation_token, status) VALUES (?,?,?,?,?,?,?,?,?,?,?)');
$insert->execute([$role, $nombre, $apellido, $cedula, $fecha_nacimiento, $email, $telefono, $foto_path, $password_hash, $activation_token, 'pending']);

$activation_link = BASE_URL . '/api/activate.php?token=' . $activation_token;
$subject = 'Activar cuenta - Licu Rides';
$body = "<p>Hola " . htmlspecialchars($nombre) . ",</p>";
$body .= "<p>Para activar tu cuenta haz clic en el siguiente enlace:</p>";
$body .= "<p><a href=\"$activation_link\">Activar mi cuenta</a></p>";

$file = send_local_mail($email, $subject, $body);

echo "Registro completado. Revisa el correo (archivo creado: $file) para activar tu cuenta.";

?>
