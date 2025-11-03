<?php
require_once __DIR__ . '/auth.php';

$method = $_SERVER['REQUEST_METHOD'];
$pdo = db_connect();

// Verificar que es admin
require_login();
if ($_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Acceso denegado']);
    exit;
}

// GET: listar usuarios
if ($method === 'GET') {
    $sql = "SELECT id, email, nombre, apellido, role, status, created_at, telefono 
            FROM users 
            ORDER BY created_at DESC";
    $stmt = $pdo->query($sql);
    $users = $stmt->fetchAll();
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'data' => $users]);
    exit;
}

// POST: crear admin o actualizar estado de usuario
if ($method === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'create_admin') {
        $email = $_POST['email'] ?? '';
        $nombre = $_POST['nombre'] ?? '';
        $apellido = $_POST['apellido'] ?? '';
        $password = $_POST['password'] ?? '';
        
        if (empty($email) || empty($password) || empty($nombre)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Datos incompletos']);
            exit;
        }

        // Verificar que el email no existe
        $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $check->execute([$email]);
        if ($check->fetch()) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'El email ya está registrado']);
            exit;
        }

        // Crear usuario admin
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (email, password, nombre, apellido, role, estado) VALUES (?, ?, ?, ?, 'admin', 'active')");
        if ($stmt->execute([$email, $hash, $nombre, $apellido])) {
            echo json_encode(['success' => true, 'message' => 'Administrador creado correctamente']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Error creando usuario']);
        }
        exit;
    }

    if ($action === 'update_status') {
        $user_id = (int)($_POST['user_id'] ?? 0);
        $status = $_POST['status'] ?? '';

        if (!in_array($status, ['active', 'inactive'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Estado inválido']);
            exit;
        }

        // No permitir desactivar al propio usuario
        if ($user_id === $_SESSION['user_id']) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'No puedes desactivar tu propia cuenta']);
            exit;
        }

        $stmt = $pdo->prepare("UPDATE users SET status = ? WHERE id = ?");
        if ($stmt->execute([$status, $user_id])) {
            echo json_encode(['success' => true, 'message' => 'Estado actualizado']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Error actualizando estado']);
        }
        exit;
    }

    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Acción inválida']);
    exit;
}

http_response_code(405);
echo json_encode(['success' => false, 'error' => 'Método no permitido']);
?>