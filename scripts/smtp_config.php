<?php
// SMTP Configuration
define('SMTP_HOST', 'sandbox.smtp.mailtrap.io');
define('SMTP_PORT', 2525); // Using port 2525 which is more reliable for Mailtrap
define('SMTP_USERNAME', '4be499df0d78c1');
define('SMTP_PASSWORD', '6d4e58644698bc'); // Your Mailtrap password
define('SMTP_FROM_EMAIL', 'noreply@licurides.com');
define('SMTP_FROM_NAME', 'Licu Rides');

// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);