<?php
require_once __DIR__ . '/includes/auth.php';

$auth = new Auth();

// If user is logged in, log them out
if ($auth->isLoggedIn()) {
    $auth->logout();
}

// Redirect to homepage with logout message
header('Location: index.php?logged_out=1');
exit();
?>