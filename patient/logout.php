<?php
require_once __DIR__ . '/../includes/auth.php';

$auth = new Auth();

// If user is logged in, log them out
if ($auth->isLoggedIn()) {
    $auth->logout();
}

// Redirect to patient login with logout message
header('Location: login.php?logged_out=1');
exit();
?>