<?php
/**
 * Goba Hospital Patient Record Management System - Setup Script
 * This script helps configure the system and check requirements
 */

// Check PHP version
$php_version = phpversion();
$php_required = '7.4.0';
$php_ok = version_compare($php_version, $php_required, '>=');

// Check required extensions
$required_extensions = ['pdo', 'pdo_mysql', 'mbstring', 'json'];
$missing_extensions = [];

foreach ($required_extensions as $ext) {
    if (!extension_loaded($ext)) {
        $missing_extensions[] = $ext;
    }
}

// Check if config file exists
$config_exists = file_exists('includes/config.php');

// Check if database schema exists
$schema_exists = file_exists('database/schema.sql');

// Check upload directories
$upload_dirs = ['uploads', 'uploads/audio', 'uploads/files', 'uploads/images'];
$upload_issues = [];

foreach ($upload_dirs as $dir) {
    if (!is_dir($dir)) {
        $upload_issues[] = "Directory '$dir' does not exist";
    } elseif (!is_writable($dir)) {
        $upload_issues[] = "Directory '$dir' is not writable";
    }
}

// Check if we can connect to database
$db_connection_ok = false;
$db_error = '';

if ($config_exists) {
    require_once 'includes/config.php';
    try {
        $pdo = getDBConnection();
        $db_connection_ok = true;
    } catch (Exception $e) {
        $db_error = $e->getMessage();
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
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h3 class="mb-0">
                            <i class="fas fa-hospital me-2"></i>
                            Goba Hospital - System Setup
                        </h3>
                    </div>
                    <div class="card-body">
                        
                        <!-- System Requirements Check -->
                        <h4 class="mb-3">System Requirements Check</h4>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <h6>PHP Version</h6>
                                        <p class="mb-1">Current: <?php echo $php_version; ?></p>
                                        <p class="mb-1">Required: <?php echo $php_required; ?></p>
                                        <?php if ($php_ok): ?>
                                            <span class="badge bg-success">✓ OK</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">✗ FAILED</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <h6>PHP Extensions</h6>
                                        <?php if (empty($missing_extensions)): ?>
                                            <span class="badge bg-success">✓ All Required Extensions Installed</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">✗ Missing Extensions</span>
                                            <ul class="mt-2 mb-0">
                                                <?php foreach ($missing_extensions as $ext): ?>
                                                    <li><?php echo $ext; ?></li>
                                                <?php endforeach; ?>
                                            </ul>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- File System Check -->
                        <h4 class="mb-3">File System Check</h4>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <h6>Configuration Files</h6>
                                        <ul class="list-unstyled mb-0">
                                            <li>
                                                config.php: 
                                                <?php if ($config_exists): ?>
                                                    <span class="badge bg-success">✓ Found</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger">✗ Missing</span>
                                                <?php endif; ?>
                                            </li>
                                            <li>
                                                schema.sql: 
                                                <?php if ($schema_exists): ?>
                                                    <span class="badge bg-success">✓ Found</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger">✗ Missing</span>
                                                <?php endif; ?>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <h6>Upload Directories</h6>
                                        <?php if (empty($upload_issues)): ?>
                                            <span class="badge bg-success">✓ All Directories OK</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning">⚠ Issues Found</span>
                                            <ul class="mt-2 mb-0">
                                                <?php foreach ($upload_issues as $issue): ?>
                                                    <li><?php echo $issue; ?></li>
                                                <?php endforeach; ?>
                                            </ul>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Database Connection Check -->
                        <h4 class="mb-3">Database Connection</h4>
                        
                        <div class="card mb-3">
                            <div class="card-body">
                                <?php if ($db_connection_ok): ?>
                                    <span class="badge bg-success">✓ Database Connection Successful</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">✗ Database Connection Failed</span>
                                    <?php if ($db_error): ?>
                                        <p class="text-danger mt-2">Error: <?php echo $db_error; ?></p>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Setup Instructions -->
                        <h4 class="mb-3">Setup Instructions</h4>
                        
                        <div class="alert alert-info">
                            <h6>To complete the setup:</h6>
                            <ol>
                                <li>Ensure all requirements above are met</li>
                                <li>Create a MySQL database named 'goba_hospital'</li>
                                <li>Import the database schema: <code>mysql -u root -p goba_hospital < database/schema.sql</code></li>
                                <li>Update database credentials in <code>includes/config.php</code></li>
                                <li>Set proper permissions for upload directories</li>
                                <li>Configure your web server to point to this directory</li>
                            </ol>
                        </div>
                        
                        <!-- Quick Actions -->
                        <div class="row">
                            <div class="col-md-6">
                                <a href="index.php" class="btn btn-primary w-100">
                                    <i class="fas fa-home me-2"></i>Go to Homepage
                                </a>
                            </div>
                            <div class="col-md-6">
                                <a href="login.php" class="btn btn-success w-100">
                                    <i class="fas fa-sign-in-alt me-2"></i>Go to Login
                                </a>
                            </div>
                        </div>
                        
                        <!-- System Status Summary -->
                        <?php
                        $all_ok = $php_ok && empty($missing_extensions) && $config_exists && $schema_exists && empty($upload_issues) && $db_connection_ok;
                        ?>
                        
                        <div class="mt-4">
                            <?php if ($all_ok): ?>
                                <div class="alert alert-success">
                                    <h5><i class="fas fa-check-circle me-2"></i>System Ready!</h5>
                                    <p class="mb-0">All requirements are met. The system is ready to use.</p>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-warning">
                                    <h5><i class="fas fa-exclamation-triangle me-2"></i>Setup Required</h5>
                                    <p class="mb-0">Please address the issues above before using the system.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                    </div>
                </div>
                
                <!-- Footer -->
                <div class="text-center mt-4">
                    <p class="text-muted">
                        Goba Hospital Patient Record Management System<br>
                        Version 1.0
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>