<?php
/**
 * Goba Hospital Management System - Setup Script
 * This script helps initialize the database and check system requirements
 */

// Check if setup has already been run
$setupCompleteFile = __DIR__ . '/setup_complete.flag';
if (file_exists($setupCompleteFile)) {
    die('<h1>Setup Already Complete</h1><p>The system has already been set up. If you need to re-run setup, delete the file: ' . $setupCompleteFile . '</p>');
}

$errors = [];
$success = [];

// Check PHP version
if (version_compare(PHP_VERSION, '7.4.0', '<')) {
    $errors[] = 'PHP 7.4 or higher is required. Current version: ' . PHP_VERSION;
} else {
    $success[] = 'PHP version is compatible: ' . PHP_VERSION;
}

// Check required extensions
$requiredExtensions = ['pdo', 'pdo_mysql', 'mysqli'];
foreach ($requiredExtensions as $ext) {
    if (!extension_loaded($ext)) {
        $errors[] = "Required PHP extension '$ext' is not loaded";
    } else {
        $success[] = "PHP extension '$ext' is available";
    }
}

// Check directory permissions
$directories = [
    __DIR__ . '/uploads',
    __DIR__ . '/uploads/medical_documents',
    __DIR__ . '/uploads/audio_files',
    __DIR__ . '/uploads/profile_images',
    __DIR__ . '/logs'
];

foreach ($directories as $dir) {
    if (!is_dir($dir)) {
        if (mkdir($dir, 0755, true)) {
            $success[] = "Created directory: $dir";
        } else {
            $errors[] = "Could not create directory: $dir";
        }
    } else {
        $success[] = "Directory exists: $dir";
    }
    
    if (is_dir($dir) && !is_writable($dir)) {
        $errors[] = "Directory is not writable: $dir";
    }
}

// Database setup
$dbSetup = false;
$dbError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['setup_db'])) {
    $host = $_POST['db_host'] ?? 'localhost';
    $username = $_POST['db_user'] ?? 'root';
    $password = $_POST['db_pass'] ?? '';
    $database = $_POST['db_name'] ?? 'goba_hospital';
    
    try {
        // Test connection
        $dsn = "mysql:host=$host";
        $pdo = new PDO($dsn, $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Create database if it doesn't exist
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `$database` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $pdo->exec("USE `$database`");
        
        // Read and execute schema
        $schemaFile = __DIR__ . '/database/schema.sql';
        if (file_exists($schemaFile)) {
            $schema = file_get_contents($schemaFile);
            // Remove the USE database line from schema since we already selected it
            $schema = preg_replace('/USE\s+[^;]+;/', '', $schema);
            $pdo->exec($schema);
            $success[] = 'Database schema created successfully';
            
            // Read and execute sample data
            $sampleDataFile = __DIR__ . '/database/sample_data.sql';
            if (file_exists($sampleDataFile) && isset($_POST['include_sample_data'])) {
                $sampleData = file_get_contents($sampleDataFile);
                $sampleData = preg_replace('/USE\s+[^;]+;/', '', $sampleData);
                $pdo->exec($sampleData);
                $success[] = 'Sample data inserted successfully';
            }
            
            // Update config file
            $configFile = __DIR__ . '/config/database.php';
            $configContent = file_get_contents($configFile);
            $configContent = str_replace("define('DB_HOST', 'localhost');", "define('DB_HOST', '$host');", $configContent);
            $configContent = str_replace("define('DB_USER', 'root');", "define('DB_USER', '$username');", $configContent);
            $configContent = str_replace("define('DB_PASS', '');", "define('DB_PASS', '$password');", $configContent);
            $configContent = str_replace("define('DB_NAME', 'goba_hospital');", "define('DB_NAME', '$database');", $configContent);
            file_put_contents($configFile, $configContent);
            
            $dbSetup = true;
            $success[] = 'Database configuration updated';
            
        } else {
            $errors[] = 'Database schema file not found: ' . $schemaFile;
        }
        
    } catch (PDOException $e) {
        $dbError = 'Database connection failed: ' . $e->getMessage();
        $errors[] = $dbError;
    }
}

// Mark setup as complete if everything is successful
if ($dbSetup && empty($errors)) {
    file_put_contents($setupCompleteFile, date('Y-m-d H:i:s'));
    $success[] = 'Setup completed successfully!';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Goba Hospital Setup</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header class="header">
        <div class="container">
            <div class="logo">
                <div class="logo-icon">🏥</div>
                <span>Goba Hospital Setup</span>
            </div>
        </div>
    </header>

    <main class="main-content">
        <div class="container">
            <div class="form-container" style="max-width: 800px;">
                <div class="card-header">
                    <h1 class="card-title">System Setup</h1>
                    <p>Initialize the Goba Hospital Management System</p>
                </div>

                <!-- Display Errors -->
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <h4>Setup Errors:</h4>
                        <ul>
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <!-- Display Success Messages -->
                <?php if (!empty($success)): ?>
                    <div class="alert alert-success">
                        <h4>Setup Progress:</h4>
                        <ul>
                            <?php foreach ($success as $msg): ?>
                                <li><?php echo htmlspecialchars($msg); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <?php if ($dbSetup && empty($errors)): ?>
                    <!-- Setup Complete -->
                    <div class="card" style="background: #e8f5e8; border: 2px solid #4CAF50;">
                        <h2>🎉 Setup Complete!</h2>
                        <p>Your Goba Hospital Management System has been successfully set up.</p>
                        <div style="margin: 2rem 0;">
                            <h3>What's Next?</h3>
                            <ol>
                                <li>Visit the <a href="index.php">homepage</a> to access the system</li>
                                <li>Use the demo accounts provided in the README.md file</li>
                                <li>Explore the different portal functionalities</li>
                                <li>Review the documentation for advanced features</li>
                            </ol>
                        </div>
                        <div style="text-align: center;">
                            <a href="index.php" class="btn" style="margin-right: 1rem;">Go to Homepage</a>
                            <a href="README.md" class="btn btn-secondary">View Documentation</a>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Database Setup Form -->
                    <form method="POST" action="">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Database Configuration</h3>
                            </div>
                            
                            <div class="form-group">
                                <label for="db_host" class="form-label">Database Host</label>
                                <input 
                                    type="text" 
                                    id="db_host" 
                                    name="db_host" 
                                    class="form-input" 
                                    value="<?php echo htmlspecialchars($_POST['db_host'] ?? 'localhost'); ?>"
                                    required
                                >
                            </div>

                            <div class="form-group">
                                <label for="db_user" class="form-label">Database Username</label>
                                <input 
                                    type="text" 
                                    id="db_user" 
                                    name="db_user" 
                                    class="form-input" 
                                    value="<?php echo htmlspecialchars($_POST['db_user'] ?? 'root'); ?>"
                                    required
                                >
                            </div>

                            <div class="form-group">
                                <label for="db_pass" class="form-label">Database Password</label>
                                <input 
                                    type="password" 
                                    id="db_pass" 
                                    name="db_pass" 
                                    class="form-input" 
                                    placeholder="Leave blank if no password"
                                >
                            </div>

                            <div class="form-group">
                                <label for="db_name" class="form-label">Database Name</label>
                                <input 
                                    type="text" 
                                    id="db_name" 
                                    name="db_name" 
                                    class="form-input" 
                                    value="<?php echo htmlspecialchars($_POST['db_name'] ?? 'goba_hospital'); ?>"
                                    required
                                >
                            </div>

                            <div class="form-group">
                                <label>
                                    <input type="checkbox" name="include_sample_data" value="1" checked>
                                    Include sample data for testing
                                </label>
                            </div>

                            <div class="form-group">
                                <button type="submit" name="setup_db" class="btn" style="width: 100%;" <?php echo !empty($errors) ? 'disabled' : ''; ?>>
                                    Initialize Database
                                </button>
                            </div>
                        </div>
                    </form>

                    <!-- System Requirements -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">System Requirements</h3>
                        </div>
                        <p>Please ensure all requirements are met before proceeding with database setup.</p>
                        <ul>
                            <li>PHP 7.4 or higher ✓</li>
                            <li>MySQL 5.7 or higher</li>
                            <li>PDO and PDO_MySQL extensions ✓</li>
                            <li>Write permissions for uploads and logs directories ✓</li>
                        </ul>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <footer class="footer">
        <div class="container">
            <p>&copy; 2024 Goba Hospital Management System Setup</p>
        </div>
    </footer>

    <script src="assets/js/main.js"></script>
</body>
</html>