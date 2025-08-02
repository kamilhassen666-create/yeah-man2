<?php
require_once 'includes/config.php';

// Log the logout action if user is logged in
if (isset($_SESSION['user_id']) && isset($_SESSION['user_type'])) {
    log_system_action($_SESSION['user_id'], $_SESSION['user_type'], 'User logged out');
}

// Destroy all session data
session_unset();
session_destroy();

// Clear any session cookies
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Redirect to login page with success message
header('Location: login.php?logout=success');
exit();
?>