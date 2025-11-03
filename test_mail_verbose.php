<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/scripts/send_mail.php';

echo "<pre>\n";
echo "=== Prueba de envío de correo ===\n\n";

// Verificar archivos y clases
echo "Verificando requisitos:\n";
echo "- Archivo smtp_config.php existe: " . (file_exists(__DIR__ . '/scripts/smtp_config.php') ? 'SÍ' : 'NO') . "\n";
echo "- Clase PHPMailer existe: " . (class_exists('PHPMailer\PHPMailer\PHPMailer') ? 'SÍ' : 'NO') . "\n";
echo "- Directorio emails existe: " . (is_dir(EMAILS_DIR) ? 'SÍ' : 'NO') . "\n\n";

echo "Configuración SMTP:\n";
echo "- Host: " . SMTP_HOST . "\n";
echo "- Puerto: " . SMTP_PORT . "\n";
echo "- Usuario: " . SMTP_USERNAME . "\n";
echo "- Contraseña: " . substr(SMTP_PASSWORD, 0, 3) . '...' . substr(SMTP_PASSWORD, -3) . "\n";
echo "- From Email: " . SMTP_FROM_EMAIL . "\n\n";

// Intentar envío
$to = "test@example.com";
$subject = "Prueba de correo - " . date('Y-m-d H:i:s');
$body = "
<h1>Correo de prueba</h1>
<p>Este es un correo de prueba enviado el " . date('Y-m-d H:i:s') . "</p>
<hr>
<p><strong>Configuración utilizada:</strong></p>
<ul>
    <li>Host: " . SMTP_HOST . "</li>
    <li>Puerto: " . SMTP_PORT . "</li>
    <li>Usuario: " . SMTP_USERNAME . "</li>
    <li>From: " . SMTP_FROM_EMAIL . "</li>
</ul>
";

echo "Intentando enviar correo...\n";
$result = send_local_mail($to, $subject, $body);
echo "\nResultado: " . ($result ? "ÉXITO" : "FALLÓ") . "\n";
echo "Archivo de registro: " . $result . "\n";
echo "\nRevisa el archivo php_error.log para ver los detalles del debug SMTP\n";
echo "Y tu bandeja de Mailtrap en: https://mailtrap.io/inboxes\n";
echo "</pre>";
?>