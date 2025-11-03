<?php
// API para gestionar vehículos - solo conductores y admins pueden usarla
require_once __DIR__ . '/auth.php';

$method = $_SERVER['REQUEST_METHOD'];
$pdo = db_connect();

// GET: obtener lista de vehículos del usuario
if ($method === 'GET') {
    if (isset($_GET['user_id'])) {
        // Para admins que quieren ver vehículos de otro usuario
        $user_id = (int)$_GET['user_id'];
    } else {
        // Usuario normal viendo sus propios vehículos
        require_login();
        $user_id = current_user_id();
    }
    $stmt = $pdo->prepare('SELECT * FROM vehicles WHERE user_id = ?');
    $stmt->execute([$user_id]);
    $rows = $stmt->fetchAll();
    header('Content-Type: application/json');
    echo json_encode($rows);
    exit;
}

// POST: crear, editar o eliminar vehículos
if ($method === 'POST') {
    require_login();
    $action = $_POST['action'] ?? 'create';
    $user_id = current_user_id();

    if ($action === 'create') {
        // Solo conductores y administradores pueden crear vehículos
        if ($_SESSION['role'] !== 'driver' && $_SESSION['role'] !== 'admin') { 
            http_response_code(403); 
            echo 'Solo choferes y administradores pueden crear vehículos'; 
            exit; 
        }
        $placa = $_POST['placa'] ?? '';
        $color = $_POST['color'] ?? '';
        $marca = $_POST['marca'] ?? '';
        $modelo = $_POST['modelo'] ?? '';
        $anio = $_POST['anio'] ?? null;
        $capacidad = $_POST['capacidad'] ?? 4;
        $foto_path = null;
        if (!empty($_FILES['foto']['tmp_name'])) {
            $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
            $target = __DIR__ . '/../uploads/' . uniqid('veh_') . '.' . $ext;
            move_uploaded_file($_FILES['foto']['tmp_name'], $target);
            $foto_path = 'uploads/' . basename($target);
        }
        $stmt = $pdo->prepare('INSERT INTO vehicles (user_id, placa, color, marca, modelo, anio, capacidad, foto) VALUES (?,?,?,?,?,?,?,?)');
        $stmt->execute([$user_id, $placa, $color, $marca, $modelo, $anio, $capacidad, $foto_path]);
        echo 'Vehículo creado';
        exit;
    }

    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        // verificar que el vehículo pertenece al usuario o admin
        $stmt = $pdo->prepare('SELECT user_id FROM vehicles WHERE id = ?');
        $stmt->execute([$id]);
        $v = $stmt->fetch();
        if (!$v) { http_response_code(404); echo 'No encontrado'; exit; }
        if ($v['user_id'] != $user_id && $_SESSION['role'] !== 'admin') { http_response_code(403); echo 'No autorizado'; exit; }
        $del = $pdo->prepare('DELETE FROM vehicles WHERE id = ?');
        $del->execute([$id]);
        echo 'Vehículo eliminado';
        exit;
    }

    if ($action === 'edit') {
        $id = (int)($_POST['id'] ?? 0);
        $stmt = $pdo->prepare('SELECT user_id FROM vehicles WHERE id = ?');
        $stmt->execute([$id]);
        $v = $stmt->fetch();
        if (!$v) { http_response_code(404); echo 'No encontrado'; exit; }
        if ($v['user_id'] != $user_id && $_SESSION['role'] !== 'admin') { http_response_code(403); echo 'No autorizado'; exit; }
        $placa = $_POST['placa'] ?? '';
        $color = $_POST['color'] ?? '';
        $marca = $_POST['marca'] ?? '';
        $modelo = $_POST['modelo'] ?? '';
        $anio = $_POST['anio'] ?? null;
        $capacidad = $_POST['capacidad'] ?? 4;
        $foto_path = null;
        if (!empty($_FILES['foto']['tmp_name'])) {
            $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
            $target = __DIR__ . '/../uploads/' . uniqid('veh_') . '.' . $ext;
            move_uploaded_file($_FILES['foto']['tmp_name'], $target);
            $foto_path = 'uploads/' . basename($target);
        }
        $sql = 'UPDATE vehicles SET placa=?, color=?, marca=?, modelo=?, anio=?, capacidad=?';
        $params = [$placa, $color, $marca, $modelo, $anio, $capacidad];
        if ($foto_path) { $sql .= ', foto=?'; $params[] = $foto_path; }
        $sql .= ' WHERE id=?';
        $params[] = $id;
        $upd = $pdo->prepare($sql);
        $upd->execute($params);
        echo 'Vehículo actualizado';
        exit;
    }

    http_response_code(400);
    echo 'Acción inválida';
    exit;
}

http_response_code(405);
echo 'Method not allowed';

?>
