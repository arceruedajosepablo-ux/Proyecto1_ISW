<?php
require_once __DIR__ . '/scripts/send_mail.php';

// Test sending an email
$to = "test@example.com";
$subject = "Test Email from Licu Rides";
$body = "<h1>Test Email</h1><p>This is a test email from Licu Rides using SMTP.</p>";

$result = send_local_mail($to, $subject, $body);
echo "Email result: " . $result . "\n";
?>