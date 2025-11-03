<?php
// API adicional de administración de usuarios - funciones específicas para admins
require_once __DIR__ . '/auth.php';

$pdo = db_connect();
$method = $_SERVER['REQUEST_METHOD'];

// Solo administradores pueden usar estas funciones
require_role(['admin']);

if ($method === 'GET') {
    // Obtener lista completa de usuarios para administrar
    $stmt = $pdo->query('SELECT id, role, nombre, apellido, email, status, created_at FROM users ORDER BY created_at DESC');
    header('Content-Type: application/json');
    echo json_encode($stmt->fetchAll());
    exit;
}

if ($method === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'create_admin') {
        // Crear un nuevo usuario administrador
        $nombre = $_POST['nombre'] ?? '';
        $apellido = $_POST['apellido'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        if (empty($email) || empty($password)) { 
            http_response_code(400); 
            echo 'Faltan datos obligatorios'; 
            exit; 
        }
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare('INSERT INTO users (role, nombre, apellido, email, password, status) VALUES (?,?,?,?,?,?)');
        $stmt->execute(['admin', $nombre, $apellido, $email, $hash, 'active']);
        echo 'Admin creado';
        exit;
    }

    if ($action === 'deactivate') {
        // Desactivar un usuario específico
        $id = (int)($_POST['id'] ?? 0);
        $upd = $pdo->prepare('UPDATE users SET status = ? WHERE id = ?');
        $upd->execute(['inactive', $id]);
        echo 'Usuario desactivado correctamente';
        exit;
    }

    http_response_code(400);
    echo 'Acción no válida';
    exit;
}

http_response_code(405);
echo 'Method not allowed';

?>
