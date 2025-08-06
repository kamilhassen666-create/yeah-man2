<?php
require_once '../includes/auth.php';

$auth = new Auth();

// If already logged in, redirect to appropriate dashboard
if ($auth->isLoggedIn()) {
    $userType = $auth->getUserType();
    header('Location: ' . $auth->getRedirectUrl($userType));
    exit();
}

$userType = isset($_GET['type']) ? $_GET['type'] : 'patient';
$validTypes = ['patient', 'doctor', 'staff', 'admin', 'external_office'];

if (!in_array($userType, $validTypes)) {
    $userType = 'patient';
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = trim($_POST['user_id']);
    $password = $_POST['password'];
    $loginType = $_POST['user_type'];
    
    if (empty($userId) || empty($password)) {
        $error = 'Please fill in all fields';
    } else {
        $result = $auth->login($userId, $password, $loginType);
        
        if ($result['success']) {
            header('Location: ' . $result['redirect']);
            exit();
        } else {
            $error = $result['message'];
        }
    }
}

// Get user type display name
function getUserTypeDisplay($type) {
    switch ($type) {
        case 'patient': return 'Patient';
        case 'doctor': return 'Doctor';
        case 'staff': return 'Medical Staff';
        case 'admin': return 'Administrator';
        case 'external_office': return 'External Health Office';
        default: return 'User';
    }
}

// Get user type icon
function getUserTypeIcon($type) {
    switch ($type) {
        case 'patient': return 'fas fa-user-injured';
        case 'doctor': return 'fas fa-user-md';
        case 'staff': return 'fas fa-user-nurse';
        case 'admin': return 'fas fa-user-shield';
        case 'external_office': return 'fas fa-building';
        default: return 'fas fa-user';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo getUserTypeDisplay($userType); ?> Login - Goba Hospital</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <div style="text-align: center; margin-bottom: 1rem;">
                    <i class="<?php echo getUserTypeIcon($userType); ?>" style="font-size: 3rem; color: var(--primary-green);"></i>
                </div>
                <h2><?php echo getUserTypeDisplay($userType); ?> Login</h2>
                <p>Goba Hospital Patient Record Management System</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="" class="login-form">
                <input type="hidden" name="user_type" value="<?php echo htmlspecialchars($userType); ?>">
                
                <div class="form-group">
                    <label for="user_id" class="form-label">
                        <i class="fas fa-user"></i> User ID
                    </label>
                    <input 
                        type="text" 
                        id="user_id" 
                        name="user_id" 
                        class="form-control" 
                        placeholder="Enter your user ID"
                        value="<?php echo isset($_POST['user_id']) ? htmlspecialchars($_POST['user_id']) : ''; ?>"
                        required
                        autocomplete="username"
                    >
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">
                        <i class="fas fa-lock"></i> Password
                    </label>
                    <div style="position: relative;">
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            class="form-control" 
                            placeholder="Enter your password"
                            required
                            autocomplete="current-password"
                        >
                        <button 
                            type="button" 
                            class="password-toggle" 
                            onclick="togglePassword()"
                            style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; color: var(--dark-gray); cursor: pointer;"
                        >
                            <i class="fas fa-eye" id="toggleIcon"></i>
                        </button>
                    </div>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary" style="width: 100%;">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </button>
                </div>
            </form>

            <div style="margin-top: 2rem; padding-top: 1rem; border-top: 1px solid var(--border-color); text-align: center;">
                <p style="margin-bottom: 1rem; color: var(--dark-gray);">Switch Login Type:</p>
                <div style="display: flex; flex-wrap: wrap; gap: 0.5rem; justify-content: center;">
                    <?php foreach ($validTypes as $type): ?>
                        <?php if ($type !== $userType): ?>
                            <a href="?type=<?php echo $type; ?>" class="btn btn-secondary" style="font-size: 0.9rem; padding: 8px 16px;">
                                <i class="<?php echo getUserTypeIcon($type); ?>"></i> 
                                <?php echo getUserTypeDisplay($type); ?>
                            </a>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>

            <div style="margin-top: 2rem; text-align: center;">
                <a href="../index.php" class="btn btn-secondary">
                    <i class="fas fa-home"></i> Back to Home
                </a>
            </div>

            <?php if ($userType === 'admin'): ?>
                <div style="margin-top: 2rem; padding: 1rem; background: var(--light-green); border-radius: var(--border-radius);">
                    <h4 style="margin-bottom: 0.5rem; color: var(--primary-green);">Demo Admin Credentials:</h4>
                    <p style="margin: 0; font-size: 0.9rem;">
                        <strong>User ID:</strong> admin<br>
                        <strong>Password:</strong> password
                    </p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordField = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');
            
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordField.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }

        // Auto-focus on user ID field
        document.getElementById('user_id').focus();

        // Add form submission loading state
        document.querySelector('.login-form').addEventListener('submit', function() {
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Logging in...';
            submitBtn.disabled = true;
        });
    </script>
</body>
</html>