<?php
// Ejemplo de configuración - Renombrar a config.php
// Configuración de la base de datos
define('DB_HOST', 'localhost');
define('DB_NAME', 'licu_rides');
define('DB_USER', 'tu_usuario');
define('DB_PASS', 'tu_contraseña');

// Configuración de la aplicación
define('SITE_URL', 'http://isw.paw.com');
define('EMAILS_DIR', __DIR__ . '/emails/');
define('UPLOADS_DIR', __DIR__ . '/uploads/');

// Zona horaria
date_default_timezone_set('America/Costa_Rica');
?>