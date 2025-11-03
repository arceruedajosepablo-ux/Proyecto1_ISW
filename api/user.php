<?php
// API para manejar datos del usuario actual - ver perfil y actualizarlo
require_once __DIR__ . '/auth.php';

$pdo = db_connect();
$method = $_SERVER['REQUEST_METHOD'];

// GET: obtener datos del usuario actual
if ($method === 'GET') {
    require_login();
    $user_id = current_user_id();
    $stmt = $pdo->prepare('SELECT id, role, nombre, apellido, cedula, fecha_nacimiento, email, telefono, foto, status, created_at FROM users WHERE id = ?');
    $stmt->execute([$user_id]);
    $u = $stmt->fetch();
    header('Content-Type: application/json');
    echo json_encode($u);
    exit;
}

// POST: actualizar datos del usuario
if ($method === 'POST') {
    require_login();
    $action = $_POST['action'] ?? 'update';
    $user_id = current_user_id();

    if ($action === 'update') {
        // Obtener los nuevos datos del formulario
        $nombre = $_POST['nombre'] ?? '';
        $apellido = $_POST['apellido'] ?? '';
        $cedula = $_POST['cedula'] ?? '';
        $fecha_nacimiento = $_POST['fecha_nacimiento'] ?? null;
        $telefono = $_POST['telefono'] ?? '';
        $email = $_POST['email'] ?? '';

        $foto_path = null;
        if (!empty($_FILES['foto']['tmp_name'])) {
            $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
            $target = __DIR__ . '/../uploads/' . uniqid('pf_') . '.' . $ext;
            move_uploaded_file($_FILES['foto']['tmp_name'], $target);
            $foto_path = 'uploads/' . basename($target);
        }

        // Check email uniqueness if changed
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? AND id <> ?');
        $stmt->execute([$email, $user_id]);
        if ($stmt->fetch()) { http_response_code(400); echo 'Email ya en uso'; exit; }

        $sql = 'UPDATE users SET nombre=?, apellido=?, cedula=?, fecha_nacimiento=?, email=?, telefono=?';
        $params = [$nombre, $apellido, $cedula, $fecha_nacimiento, $email, $telefono];
        if ($foto_path) { $sql .= ', foto=?'; $params[] = $foto_path; }
        $sql .= ' WHERE id=?';
        $params[] = $user_id;
        $upd = $pdo->prepare($sql);
        $upd->execute($params);
        echo 'Perfil actualizado';
        exit;
    }

    if ($action === 'change_password') {
        $current = $_POST['current'] ?? '';
        $new = $_POST['new'] ?? '';
        if (empty($new)) { http_response_code(400); echo 'Password inválido'; exit; }
        $stmt = $pdo->prepare('SELECT password FROM users WHERE id = ?');
        $stmt->execute([$user_id]);
        $r = $stmt->fetch();
        if (!$r || !password_verify($current, $r['password'])) { http_response_code(400); echo 'Contraseña actual incorrecta'; exit; }
        $hash = password_hash($new, PASSWORD_DEFAULT);
        $upd = $pdo->prepare('UPDATE users SET password = ? WHERE id = ?');
        $upd->execute([$hash, $user_id]);
        echo 'Contraseña cambiada';
        exit;
    }

    http_response_code(400);
    echo 'Acción inválida';
    exit;
}

http_response_code(405);
echo 'Method not allowed';

?>
