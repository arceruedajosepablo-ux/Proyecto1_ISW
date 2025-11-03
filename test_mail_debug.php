<?php
require_once __DIR__ . '/scripts/send_mail.php';

echo "Iniciando prueba de envío de correo...\n";
echo "Configuración SMTP:\n";
echo "Host: " . SMTP_HOST . "\n";
echo "Puerto: " . SMTP_PORT . "\n";
echo "Usuario: " . SMTP_USERNAME . "\n";
echo "From Email: " . SMTP_FROM_EMAIL . "\n";

// Test sending an email
$to = "test@example.com";
$subject = "Test Email from Licu Rides - " . date('Y-m-d H:i:s');
$body = "
<h1>Test Email</h1>
<p>Este es un correo de prueba enviado el " . date('Y-m-d H:i:s') . "</p>
<p>Configuración utilizada:</p>
<ul>
    <li>Host: " . SMTP_HOST . "</li>
    <li>Puerto: " . SMTP_PORT . "</li>
    <li>Usuario: " . SMTP_USERNAME . "</li>
</ul>
";

error_log("Intentando enviar correo de prueba...");
$result = send_local_mail($to, $subject, $body);
error_log("Resultado del envío: " . $result);

echo "\nPrueba completada. Verifica el archivo de log en php_error.log para ver los detalles del debug SMTP.\n";
echo "El archivo del correo se guardó en: " . $result . "\n";
?>