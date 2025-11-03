<?php
// Configuración básica (editar según XAMPP)
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'licu_rides');
define('DB_USER', 'root');
define('DB_PASS', '');

// Ruta base del proyecto (usar para generar enlaces en correos)
// Por ejemplo: http://isw.paw.com
define('BASE_URL', 'http://isw.paw.com');

// Carpeta donde se guardan los correos en desarrollo
define('EMAILS_DIR', __DIR__ . '/emails');

// Crear carpetas si no existen
if (!is_dir(__DIR__ . '/uploads')) {
    mkdir(__DIR__ . '/uploads', 0755, true);
}
if (!is_dir(EMAILS_DIR)) {
    mkdir(EMAILS_DIR, 0755, true);
}

function db_connect() {
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        return $pdo;
    } catch (PDOException $e) {
        die('DB Connection failed: ' . $e->getMessage());
    }
}

?>
