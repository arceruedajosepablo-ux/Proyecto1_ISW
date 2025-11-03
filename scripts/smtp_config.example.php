<?php
// Configuración de ejemplo para el servidor de correo
// Copiá este archivo como smtp_config.php y poné tus datos reales del servidor SMTP

define('SMTP_HOST', 'smtp.example.com');        // Servidor SMTP (Gmail, Outlook, etc.)
define('SMTP_PORT', 587);                       // Puerto del servidor (587 es típico para TLS)
define('SMTP_USERNAME', 'tu_usuario@example.com'); // Email del que manda los correos
define('SMTP_PASSWORD', 'tu_contraseña');       // Contraseña del email
define('SMTP_FROM_EMAIL', 'noreply@example.com'); // Email que aparece como remitente
define('SMTP_FROM_NAME', 'Licu Rides');         // Nombre que aparece como remitente
?>