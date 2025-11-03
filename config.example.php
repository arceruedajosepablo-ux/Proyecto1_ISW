<?php
// Archivo de ejemplo para la configuración del sistema
// Copiá este archivo como config.php y cambiá los valores según tu servidor

// Configuración de la base de datos - cambiar según tu setup
define('DB_HOST', 'localhost');        // Servidor de base de datos
define('DB_NAME', 'licu_rides');       // Nombre de la base de datos
define('DB_USER', 'tu_usuario');       // Usuario de MySQL
define('DB_PASS', 'tu_contraseña');    // Contraseña de MySQL

// Configuración general de la aplicación
define('SITE_URL', 'http://isw.paw.com');    // URL donde está corriendo el sitio
define('EMAILS_DIR', __DIR__ . '/emails/');  // Carpeta para guardar correos
define('UPLOADS_DIR', __DIR__ . '/uploads/'); // Carpeta para fotos

// Configurar zona horaria de Costa Rica
date_default_timezone_set('America/Costa_Rica');
?>