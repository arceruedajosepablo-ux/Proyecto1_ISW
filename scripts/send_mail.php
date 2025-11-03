<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/smtp_config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// First, let's check for PHPMailer
if (!file_exists(__DIR__ . '/../vendor/phpmailer/phpmailer')) {
    // Create composer.json if it doesn't exist
    if (!file_exists(__DIR__ . '/../composer.json')) {
        file_put_contents(__DIR__ . '/../composer.json', json_encode([
            'require' => [
                'phpmailer/phpmailer' => '^6.8'
            ]
        ], JSON_PRETTY_PRINT));
    }
    
    // Check if composer is installed and install dependencies
    if (!file_exists(__DIR__ . '/../vendor/autoload.php')) {
        echo "Please run 'composer install' in the project root directory to install dependencies.\n";
    }
}

require_once __DIR__ . '/../vendor/autoload.php';

function send_mail($to, $subject, $body, $isHTML = true) {
    try {
        error_log("Iniciando envío de correo a: " . $to);
        error_log("Configuración SMTP - Host: " . SMTP_HOST . ", Puerto: " . SMTP_PORT . ", Usuario: " . SMTP_USERNAME);
        
        $mail = new PHPMailer(true);
        
        // debugging
        $mail->SMTPDebug = 3; 
        $mail->Debugoutput = function($str, $level) {
            error_log("DEBUG SMTP [$level]: $str");
        };
        
        // Server settings
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USERNAME;
        $mail->Password = SMTP_PASSWORD;
        $mail->SMTPSecure = 'tls';
        $mail->Port = SMTP_PORT;
        
        // Configuración adicional de seguridad
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );
        
        // Recipients
        $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        $mail->addAddress($to);
        
        // Content
        $mail->isHTML($isHTML);
        $mail->Subject = $subject;
        $mail->Body = $body;
        
        // Plain text version for non-HTML mail clients
        if ($isHTML) {
            $mail->AltBody = strip_tags($body);
        }
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Error sending email: {$mail->ErrorInfo}");
        return false;
    }
}

// For backwards compatibility and development purposes
function send_local_mail($to, $subject, $body) {
    // Try to send via SMTP first
    if (send_mail($to, $subject, $body)) {
        // Log successful email for development
        $timestamp = date('Ymd_His');
        $safeTo = preg_replace('/[^a-z0-9@._-]/i', '_', $to);
        $filename = EMAILS_DIR . "/sent_{$timestamp}_{$safeTo}.html";
        $content = "<h3>✓ Email sent successfully via SMTP</h3>";
        $content .= "<h3>To: " . htmlspecialchars($to) . "</h3>";
        $content .= "<h4>Subject: " . htmlspecialchars($subject) . "</h4>";
        $content .= "<div>" . $body . "</div>";
        file_put_contents($filename, $content);
        return $filename;
    }
    
    // Fallback to local file if SMTP fails
    $timestamp = date('Ymd_His');
    $safeTo = preg_replace('/[^a-z0-9@._-]/i', '_', $to);
    $filename = EMAILS_DIR . "/failed_{$timestamp}_{$safeTo}.html";
    $content = "<h3>⚠ SMTP Failed - Saved locally</h3>";
    $content .= "<h3>To: " . htmlspecialchars($to) . "</h3>";
    $content .= "<h4>Subject: " . htmlspecialchars($subject) . "</h4>";
    $content .= "<div>" . $body . "</div>";
    file_put_contents($filename, $content);
    return $filename;
}
?>
