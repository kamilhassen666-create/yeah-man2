<?php
require_once 'includes/config.php';

$page_title = 'Login';
$error_message = '';
$success_message = '';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    $redirect_url = $_SESSION['user_type'] . '/dashboard.php';
    header("Location: $redirect_url");
    exit();
}

// Handle login form submission
if ($_POST) {
    $username = sanitize_input($_POST['username']);
    $password = $_POST['password'];
    $user_type = sanitize_input($_POST['user_type']);
    
    if (empty($username) || empty($password) || empty($user_type)) {
        $error_message = 'Please fill in all fields.';
    } else {
        // Determine the correct login table based on user type
        $login_tables = [
            'admin' => 'admin_login',
            'doctor' => 'doctor_login',
            'patient' => 'patient_login',
            'staff' => 'staff_login',
            'external' => 'external_login'
        ];
        
        if (!array_key_exists($user_type, $login_tables)) {
            $error_message = 'Invalid user type selected.';
        } else {
            $table = $login_tables[$user_type];
            
            try {
                // Fetch user from appropriate table
                $stmt = $db->prepare("SELECT * FROM $table WHERE username = ? AND is_active = 1");
                $stmt->execute([$username]);
                $user = $stmt->fetch();
                
                if ($user && password_verify($password, $user['password'])) {
                    // Get additional user information based on type
                    $user_info = null;
                    $user_id_field = '';
                    
                    switch ($user_type) {
                        case 'admin':
                            $user_info = $user;
                            $user_id_field = 'username';
                            break;
                        case 'doctor':
                            $stmt = $db->prepare("SELECT * FROM doctor_info WHERE doctor_id = ?");
                            $stmt->execute([$user['doctor_id']]);
                            $user_info = $stmt->fetch();
                            $user_id_field = 'doctor_id';
                            break;
                        case 'patient':
                            $stmt = $db->prepare("SELECT * FROM patient_info WHERE patient_id = ?");
                            $stmt->execute([$user['patient_id']]);
                            $user_info = $stmt->fetch();
                            $user_id_field = 'patient_id';
                            break;
                        case 'staff':
                            $stmt = $db->prepare("SELECT * FROM staff_info WHERE staff_id = ?");
                            $stmt->execute([$user['staff_id']]);
                            $user_info = $stmt->fetch();
                            $user_id_field = 'staff_id';
                            break;
                        case 'external':
                            $stmt = $db->prepare("SELECT * FROM external_office WHERE office_id = ?");
                            $stmt->execute([$user['office_id']]);
                            $user_info = $stmt->fetch();
                            $user_id_field = 'office_id';
                            break;
                    }
                    
                    if ($user_info) {
                        // Set session variables
                        $_SESSION['user_id'] = $user_type === 'admin' ? $user['username'] : $user_info[$user_id_field];
                        $_SESSION['username'] = $username;
                        $_SESSION['user_type'] = $user_type;
                        $_SESSION['full_name'] = $user_type === 'admin' ? $user['full_name'] : 
                            ($user_info['first_name'] ?? '') . ' ' . ($user_info['last_name'] ?? '');
                        
                        // Update last login
                        $stmt = $db->prepare("UPDATE $table SET last_login = NOW() WHERE username = ?");
                        $stmt->execute([$username]);
                        
                        // Log the login action
                        log_system_action($_SESSION['user_id'], $user_type, 'User logged in');
                        
                        // Redirect to appropriate dashboard
                        $redirect_url = $user_type . '/dashboard.php';
                        header("Location: $redirect_url");
                        exit();
                    } else {
                        $error_message = 'User information not found.';
                    }
                } else {
                    $error_message = 'Invalid username or password.';
                }
            } catch (PDOException $e) {
                $error_message = 'Database error occurred. Please try again.';
                error_log("Login error: " . $e->getMessage());
            }
        }
    }
}

include 'includes/header.php';
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-lg">
                <div class="card-header text-center bg-primary text-white">
                    <h3><i class="fas fa-hospital"></i> Goba Hospital</h3>
                    <p class="mb-0">Patient Record Management System</p>
                </div>
                <div class="card-body p-4">
                    <h4 class="text-center mb-4">Sign In</h4>
                    
                    <div class="alert-container">
                        <?php if ($error_message): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-triangle"></i> <?php echo $error_message; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($success_message): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <form method="POST" action="" class="needs-validation" novalidate>
                        <div class="mb-3">
                            <label for="user_type" class="form-label">
                                <i class="fas fa-users"></i> User Type
                            </label>
                            <select class="form-select" id="user_type" name="user_type" required>
                                <option value="">Select User Type</option>
                                <option value="admin" <?php echo (isset($_POST['user_type']) && $_POST['user_type'] === 'admin') ? 'selected' : ''; ?>>
                                    <i class="fas fa-user-shield"></i> Admin
                                </option>
                                <option value="doctor" <?php echo (isset($_POST['user_type']) && $_POST['user_type'] === 'doctor') ? 'selected' : ''; ?>>
                                    <i class="fas fa-user-md"></i> Doctor
                                </option>
                                <option value="patient" <?php echo (isset($_POST['user_type']) && $_POST['user_type'] === 'patient') ? 'selected' : ''; ?>>
                                    <i class="fas fa-user-injured"></i> Patient
                                </option>
                                <option value="staff" <?php echo (isset($_POST['user_type']) && $_POST['user_type'] === 'staff') ? 'selected' : ''; ?>>
                                    <i class="fas fa-user-nurse"></i> Staff
                                </option>
                                <option value="external" <?php echo (isset($_POST['user_type']) && $_POST['user_type'] === 'external') ? 'selected' : ''; ?>>
                                    <i class="fas fa-building"></i> External Office
                                </option>
                            </select>
                            <div class="invalid-feedback">
                                Please select a user type.
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="username" class="form-label">
                                <i class="fas fa-user"></i> Username
                            </label>
                            <input type="text" class="form-control" id="username" name="username" 
                                   value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" 
                                   placeholder="Enter your username" required>
                            <div class="invalid-feedback">
                                Please enter your username.
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label for="password" class="form-label">
                                <i class="fas fa-lock"></i> Password
                            </label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="password" name="password" 
                                       placeholder="Enter your password" required>
                                <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div class="invalid-feedback">
                                Please enter your password.
                            </div>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-sign-in-alt"></i> Sign In
                            </button>
                        </div>
                    </form>
                </div>
                <div class="card-footer text-center text-muted">
                    <small>
                        <i class="fas fa-shield-alt"></i> 
                        Secure Login - All sessions are encrypted
                    </small>
                </div>
            </div>
            
            <!-- Demo Credentials -->
            <div class="card mt-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-info-circle"></i> Demo Credentials
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-12">
                            <small class="text-muted">
                                <strong>Admin:</strong> Username: <code>admin</code>, Password: <code>admin123</code><br>
                                <strong>Note:</strong> Other user accounts can be created through the admin panel.
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Toggle password visibility
document.getElementById('togglePassword').addEventListener('click', function() {
    const password = document.getElementById('password');
    const icon = this.querySelector('i');
    
    if (password.type === 'password') {
        password.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        password.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
});

// Auto-select user type based on URL parameter
const urlParams = new URLSearchParams(window.location.search);
const userType = urlParams.get('type');
if (userType) {
    document.getElementById('user_type').value = userType;
}
</script>

<?php include 'includes/footer.php'; ?>