<?php
/**
 * Goba Hospital Patient Record Management System - Setup Script
 * This script helps configure the system and check requirements
 */

// Check PHP version
$php_version = phpversion();
$min_php_version = '7.4.0';

// Check required extensions
$required_extensions = [
    'pdo',
    'pdo_mysql',
    'session',
    'fileinfo',
    'json'
];

$missing_extensions = [];
foreach ($required_extensions as $ext) {
    if (!extension_loaded($ext)) {
        $missing_extensions[] = $ext;
    }
}

// Check directory permissions
$directories = [
    'uploads',
    'uploads/audio',
    'uploads/files',
    'uploads/images'
];

$permission_issues = [];
foreach ($directories as $dir) {
    if (!is_dir($dir)) {
        if (!mkdir($dir, 0755, true)) {
            $permission_issues[] = "Cannot create directory: $dir";
        }
    } elseif (!is_writable($dir)) {
        $permission_issues[] = "Directory not writable: $dir";
    }
}

// Test database connection
$db_connection = false;
$db_error = '';
if (file_exists('includes/config.php')) {
    require_once 'includes/config.php';
    try {
        $pdo = getDBConnection();
        $db_connection = true;
    } catch (Exception $e) {
        $db_error = $e->getMessage();
    }
}

// Handle form submission
$setup_complete = false;
$setup_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $db_host = $_POST['db_host'] ?? 'localhost';
    $db_name = $_POST['db_name'] ?? 'goba_hospital';
    $db_user = $_POST['db_user'] ?? 'root';
    $db_pass = $_POST['db_pass'] ?? '';
    
    // Test connection
    try {
        $test_pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
        $test_pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Update config file
        $config_content = "<?php
// Database configuration
define('DB_HOST', '$db_host');
define('DB_NAME', '$db_name');
define('DB_USER', '$db_user');
define('DB_PASS', '$db_pass');

// Application configuration
define('SITE_NAME', 'Goba Hospital Patient Record Management System');
define('SITE_URL', 'http://' . \$_SERVER['HTTP_HOST'] . dirname(\$_SERVER['REQUEST_URI']));

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
        \$pdo = new PDO(\"mysql:host=\" . DB_HOST . \";dbname=\" . DB_NAME, DB_USER, DB_PASS);
        \$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return \$pdo;
    } catch(PDOException \$e) {
        die(\"Connection failed: \" . \$e->getMessage());
    }
}

// Helper functions
function sanitizeInput(\$data) {
    \$data = trim(\$data);
    \$data = stripslashes(\$data);
    \$data = htmlspecialchars(\$data);
    return \$data;
}

function generateReferenceId(\$prefix) {
    return \$prefix . '_' . date('Ymd') . '_' . uniqid();
}

function formatDate(\$date) {
    return date('F j, Y', strtotime(\$date));
}

function formatDateTime(\$datetime) {
    return date('F j, Y g:i A', strtotime(\$datetime));
}

// Session management
function startSession() {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
}

function isLoggedIn() {
    startSession();
    return isset(\$_SESSION['user_id']) && isset(\$_SESSION['user_type']);
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
function requireUserType(\$allowedTypes) {
    if (!isLoggedIn()) {
        header('Location: ../login.php');
        exit();
    }
    
    if (!in_array(\$_SESSION['user_type'], \$allowedTypes)) {
        header('Location: ../index.php');
        exit();
    }
}

// Flash messages
function setFlashMessage(\$type, \$message) {
    startSession();
    \$_SESSION['flash'] = [
        'type' => \$type,
        'message' => \$message
    ];
}

function getFlashMessage() {
    startSession();
    if (isset(\$_SESSION['flash'])) {
        \$flash = \$_SESSION['flash'];
        unset(\$_SESSION['flash']);
        return \$flash;
    }
    return null;
}

// Validation functions
function validateEmail(\$email) {
    return filter_var(\$email, FILTER_VALIDATE_EMAIL);
}

function validatePhone(\$phone) {
    return preg_match('/^\+?[0-9\s\-\(\)]{10,}$/', \$phone);
}

function validateNationalId(\$id) {
    return preg_match('/^[A-Z]{2}[0-9]{9}$/', \$id);
}
?>";
        
        if (file_put_contents('includes/config.php', $config_content)) {
            // Import database schema
            if (file_exists('database/schema.sql')) {
                $schema = file_get_contents('database/schema.sql');
                $statements = explode(';', $schema);
                
                foreach ($statements as $statement) {
                    $statement = trim($statement);
                    if (!empty($statement)) {
                        try {
                            $test_pdo->exec($statement);
                        } catch (PDOException $e) {
                            // Ignore errors for existing tables
                        }
                    }
                }
            }
            
            $setup_complete = true;
            $setup_message = 'Setup completed successfully! You can now access the system.';
        } else {
            $setup_message = 'Error: Cannot write to includes/config.php. Please check file permissions.';
        }
        
    } catch (PDOException $e) {
        $setup_message = 'Database connection failed: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup - Goba Hospital Patient Record Management System</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow-lg">
                    <div class="card-header text-center">
                        <h2 class="mb-0">
                            <i class="fas fa-hospital me-2"></i>
                            Goba Hospital Setup
                        </h2>
                        <p class="text-muted mb-0">System Configuration and Requirements Check</p>
                    </div>
                    <div class="card-body">
                        
                        <?php if ($setup_complete): ?>
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle me-2"></i>
                                <?php echo $setup_message; ?>
                            </div>
                            <div class="text-center">
                                <a href="index.php" class="btn btn-primary btn-lg">
                                    <i class="fas fa-home me-2"></i>Go to Homepage
                                </a>
                            </div>
                        <?php else: ?>
                            
                            <!-- System Requirements Check -->
                            <h4 class="mb-3">
                                <i class="fas fa-check-circle me-2"></i>System Requirements
                            </h4>
                            
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <div class="card <?php echo version_compare($php_version, $min_php_version, '>=') ? 'border-success' : 'border-danger'; ?>">
                                        <div class="card-body">
                                            <h6>PHP Version</h6>
                                            <p class="mb-0">
                                                Current: <strong><?php echo $php_version; ?></strong><br>
                                                Required: <strong><?php echo $min_php_version; ?>+</strong>
                                                <?php if (version_compare($php_version, $min_php_version, '>=')): ?>
                                                    <i class="fas fa-check text-success ms-2"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-times text-danger ms-2"></i>
                                                <?php endif; ?>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="card <?php echo empty($missing_extensions) ? 'border-success' : 'border-danger'; ?>">
                                        <div class="card-body">
                                            <h6>Required Extensions</h6>
                                            <?php if (empty($missing_extensions)): ?>
                                                <p class="mb-0 text-success">All required extensions are installed</p>
                                            <?php else: ?>
                                                <p class="mb-0 text-danger">Missing: <?php echo implode(', ', $missing_extensions); ?></p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <div class="card <?php echo empty($permission_issues) ? 'border-success' : 'border-danger'; ?>">
                                        <div class="card-body">
                                            <h6>Directory Permissions</h6>
                                            <?php if (empty($permission_issues)): ?>
                                                <p class="mb-0 text-success">All directories are writable</p>
                                            <?php else: ?>
                                                <p class="mb-0 text-danger">
                                                    <?php foreach ($permission_issues as $issue): ?>
                                                        <?php echo $issue; ?><br>
                                                    <?php endforeach; ?>
                                                </p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="card <?php echo $db_connection ? 'border-success' : 'border-danger'; ?>">
                                        <div class="card-body">
                                            <h6>Database Connection</h6>
                                            <?php if ($db_connection): ?>
                                                <p class="mb-0 text-success">Database connection successful</p>
                                            <?php else: ?>
                                                <p class="mb-0 text-danger"><?php echo $db_error ?: 'Database connection failed'; ?></p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Setup Form -->
                            <?php if (version_compare($php_version, $min_php_version, '>=') && empty($missing_extensions) && empty($permission_issues)): ?>
                                <h4 class="mb-3">
                                    <i class="fas fa-cog me-2"></i>Database Configuration
                                </h4>
                                
                                <?php if ($setup_message): ?>
                                    <div class="alert alert-danger">
                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                        <?php echo $setup_message; ?>
                                    </div>
                                <?php endif; ?>
                                
                                <form method="POST" action="">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="db_host" class="form-label">Database Host</label>
                                                <input type="text" class="form-control" id="db_host" name="db_host" value="localhost" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="db_name" class="form-label">Database Name</label>
                                                <input type="text" class="form-control" id="db_name" name="db_name" value="goba_hospital" required>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="db_user" class="form-label">Database Username</label>
                                                <input type="text" class="form-control" id="db_user" name="db_user" value="root" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="db_pass" class="form-label">Database Password</label>
                                                <input type="password" class="form-control" id="db_pass" name="db_pass">
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="d-grid">
                                        <button type="submit" class="btn btn-primary btn-lg">
                                            <i class="fas fa-download me-2"></i>Install System
                                        </button>
                                    </div>
                                </form>
                                
                                <div class="mt-4">
                                    <h6>Installation Notes:</h6>
                                    <ul class="text-muted">
                                        <li>This will create the database and all required tables</li>
                                        <li>Default admin account: admin / admin123</li>
                                        <li>Demo accounts will be created for testing</li>
                                        <li>Make sure your MySQL server is running</li>
                                    </ul>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    Please fix the system requirements before proceeding with installation.
                                </div>
                            <?php endif; ?>
                            
                        <?php endif; ?>
                        
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>