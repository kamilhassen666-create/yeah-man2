<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'goba_hospital');
define('DB_USER', 'root');
define('DB_PASS', '');

// Application configuration
define('SITE_NAME', 'Goba Hospital Patient Record Management System');
define('SITE_URL', 'http://localhost/goba_hospital');

// File upload configuration
define('UPLOAD_DIR', '../uploads/');
define('AUDIO_DIR', UPLOAD_DIR . 'audio/');
define('FILES_DIR', UPLOAD_DIR . 'files/');
define('IMAGES_DIR', UPLOAD_DIR . 'images/');

// Create upload directories if they don't exist
if (!file_exists(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0755, true);
}
if (!file_exists(AUDIO_DIR)) {
    mkdir(AUDIO_DIR, 0755, true);
}
if (!file_exists(FILES_DIR)) {
    mkdir(FILES_DIR, 0755, true);
}
if (!file_exists(IMAGES_DIR)) {
    mkdir(IMAGES_DIR, 0755, true);
}

// Database connection function
function getDBConnection() {
    try {
        $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch(PDOException $e) {
        die("Connection failed: " . $e->getMessage());
    }
}

// Helper functions
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function generateReferenceId($prefix) {
    return $prefix . '_' . date('Ymd') . '_' . uniqid();
}

function formatDate($date) {
    return date('F j, Y', strtotime($date));
}

function formatDateTime($datetime) {
    return date('F j, Y g:i A', strtotime($datetime));
}

// Session management
function startSession() {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
}

function isLoggedIn() {
    startSession();
    return isset($_SESSION['user_id']) && isset($_SESSION['user_type']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ../login.php');
        exit();
    }
}

function logout() {
    startSession();
    session_destroy();
    header('Location: ../login.php');
    exit();
}

// User type validation
function requireUserType($allowedTypes) {
    if (!isLoggedIn()) {
        header('Location: ../login.php');
        exit();
    }
    
    if (!in_array($_SESSION['user_type'], $allowedTypes)) {
        header('Location: ../index.php');
        exit();
    }
}

// Flash messages
function setFlashMessage($type, $message) {
    startSession();
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

function getFlashMessage() {
    startSession();
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

// Validation functions
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function validatePhone($phone) {
    return preg_match('/^\+?[0-9\s\-\(\)]{10,}$/', $phone);
}

function validateNationalId($id) {
    return preg_match('/^[A-Z]{2}[0-9]{9}$/', $id);
}
?>