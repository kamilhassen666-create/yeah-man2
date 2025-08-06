<?php
require_once __DIR__ . '/../includes/auth.php';

$auth = new Auth();
$error = '';
$success = '';

// Redirect if already logged in
if ($auth->isLoggedIn() && $auth->isPatient()) {
    header('Location: dashboard.php');
    exit();
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password.';
    } else {
        if ($auth->loginPatient($username, $password)) {
            header('Location: dashboard.php');
            exit();
        } else {
            $error = 'Invalid username or password.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Login - Goba Hospital</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <nav class="nav-container">
                <div class="logo">
                    <div class="logo-icon">🏥</div>
                    <span>Goba Hospital</span>
                </div>
                <ul class="nav-menu">
                    <li><a href="../index.php" class="nav-link">Home</a></li>
                    <li><a href="../index.php#about" class="nav-link">About</a></li>
                    <li><a href="../index.php#contact" class="nav-link">Contact</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        <div class="container">
            <div class="form-container">
                <div class="card-header">
                    <h2 class="card-title">Patient Login</h2>
                    <p>Access your medical records and health information</p>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="form-group">
                        <label for="username" class="form-label">Username</label>
                        <input 
                            type="text" 
                            id="username" 
                            name="username" 
                            class="form-input" 
                            required
                            value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                            placeholder="Enter your username"
                        >
                    </div>

                    <div class="form-group">
                        <label for="password" class="form-label">Password</label>
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            class="form-input" 
                            required
                            placeholder="Enter your password"
                        >
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn" style="width: 100%;">Login</button>
                    </div>
                </form>

                <div style="text-align: center; margin-top: 2rem; padding-top: 2rem; border-top: 1px solid var(--border-color);">
                    <p>Don't have an account? <a href="register.php" style="color: var(--primary-color);">Register here</a></p>
                    <p><a href="forgot-password.php" style="color: var(--text-light);">Forgot your password?</a></p>
                    <hr style="margin: 1rem 0;">
                    <p><a href="../index.php" style="color: var(--text-light);">← Back to Home</a></p>
                </div>
            </div>

            <!-- Demo Login Information -->
            <div class="card" style="max-width: 500px; margin: 2rem auto; background: #f0f8ff;">
                <div class="card-header">
                    <h3 class="card-title">Demo Login Information</h3>
                </div>
                <p><strong>For testing purposes, you can use these demo credentials:</strong></p>
                <div style="background: white; padding: 1rem; border-radius: 5px; margin: 1rem 0;">
                    <strong>Patient Demo Account:</strong><br>
                    Username: <code>patient_demo</code><br>
                    Password: <code>password123</code>
                </div>
                <p style="font-size: 0.875rem; color: var(--text-light);">
                    <em>Note: In a production environment, these demo accounts would not be available.</em>
                </p>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p>&copy; 2024 Goba Hospital. All rights reserved.</p>
        </div>
    </footer>

    <script src="../assets/js/main.js"></script>
</body>
</html>