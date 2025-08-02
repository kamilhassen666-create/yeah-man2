<?php
require_once 'includes/config.php';
startSession();

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: index.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = sanitizeInput($_POST['username']);
    $password = $_POST['password'];
    $user_type = sanitizeInput($_POST['user_type']);
    
    if (empty($username) || empty($password) || empty($user_type)) {
        $error = 'Please fill in all fields.';
    } else {
        $pdo = getDBConnection();
        
        try {
            switch ($user_type) {
                case 'admin':
                    $stmt = $pdo->prepare("SELECT * FROM admin WHERE username = ?");
                    break;
                case 'patient':
                    $stmt = $pdo->prepare("SELECT pl.*, p.* FROM patient_login pl 
                                         JOIN patients p ON pl.patient_id = p.id 
                                         WHERE pl.username = ?");
                    break;
                case 'doctor':
                    $stmt = $pdo->prepare("SELECT dl.*, d.* FROM doctor_login dl 
                                         JOIN doctors d ON dl.doctor_id = d.id 
                                         WHERE dl.username = ?");
                    break;
                case 'staff':
                    $stmt = $pdo->prepare("SELECT sl.*, s.* FROM staff_login sl 
                                         JOIN staff s ON sl.staff_id = s.id 
                                         WHERE sl.username = ?");
                    break;
                case 'external':
                    $stmt = $pdo->prepare("SELECT el.*, e.* FROM external_login el 
                                         JOIN external_health_office e ON el.external_id = e.id 
                                         WHERE el.username = ?");
                    break;
                default:
                    $error = 'Invalid user type.';
                    break;
            }
            
            if (isset($stmt)) {
                $stmt->execute([$username]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($user && password_verify($password, $user['password'])) {
                    // Set session variables
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['user_type'] = $user_type;
                    
                    // Set additional user info based on type
                    switch ($user_type) {
                        case 'patient':
                            $_SESSION['patient_id'] = $user['patient_id'];
                            $_SESSION['full_name'] = $user['first_name'] . ' ' . $user['last_name'];
                            header('Location: patient/dashboard.php');
                            break;
                        case 'doctor':
                            $_SESSION['doctor_id'] = $user['doctor_id'];
                            $_SESSION['full_name'] = $user['first_name'] . ' ' . $user['last_name'];
                            header('Location: doctor/dashboard.php');
                            break;
                        case 'staff':
                            $_SESSION['staff_id'] = $user['staff_id'];
                            $_SESSION['full_name'] = $user['first_name'] . ' ' . $user['last_name'];
                            header('Location: staff/dashboard.php');
                            break;
                        case 'admin':
                            $_SESSION['full_name'] = 'Administrator';
                            header('Location: admin/dashboard.php');
                            break;
                        case 'external':
                            $_SESSION['external_id'] = $user['office_id'];
                            $_SESSION['full_name'] = $user['name'];
                            header('Location: external/dashboard.php');
                            break;
                    }
                    exit();
                } else {
                    $error = 'Invalid username or password.';
                }
            }
        } catch (PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo SITE_NAME; ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-md-6 col-lg-4">
                <div class="card shadow-lg">
                    <div class="card-header text-center">
                        <h4 class="mb-0">
                            <i class="fas fa-hospital me-2"></i>
                            Login
                        </h4>
                    </div>
                    <div class="card-body p-4">
                        <?php if ($error): ?>
                            <div class="alert alert-danger" role="alert">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <?php echo $error; ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="user_type" class="form-label">User Type</label>
                                <select class="form-select" id="user_type" name="user_type" required>
                                    <option value="">Select User Type</option>
                                    <option value="patient">Patient</option>
                                    <option value="doctor">Doctor</option>
                                    <option value="staff">Medical Staff</option>
                                    <option value="admin">Administrator</option>
                                    <option value="external">External Health Office</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-user"></i>
                                    </span>
                                    <input type="text" class="form-control" id="username" name="username" required>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-lock"></i>
                                    </span>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                </div>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-sign-in-alt me-2"></i>Login
                                </button>
                            </div>
                        </form>
                        
                        <div class="text-center mt-3">
                            <a href="index.php" class="text-decoration-none">
                                <i class="fas fa-arrow-left me-1"></i>Back to Home
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Demo Accounts -->
                <div class="card mt-3">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Demo Accounts</h6>
                    </div>
                    <div class="card-body">
                        <small class="text-muted">
                            <strong>Admin:</strong> admin / admin123<br>
                            <strong>Patient:</strong> abebe / abebe123<br>
                            <strong>Doctor:</strong> dr.yohannes / doctor123<br>
                            <strong>Staff:</strong> nurse.bethel / staff123
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>