<?php
require_once __DIR__ . '/../config.php';
session_start();
session_unset();
session_destroy();
// Redirect to public index
header('Location: ' . BASE_URL . '/index.html');
exit;
?>
