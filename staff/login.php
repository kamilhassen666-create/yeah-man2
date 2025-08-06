<?php
require_once '../includes/functions.php';

// Redirect if already logged in
if (is_logged_in() && $_SESSION['user_type'] == 'staff') {
    header('Location: dashboard.php');
    exit();
}

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = sanitize_input($_POST['username']);
    $password = $_POST['password'];
    
    if (empty($username) || empty($password)) {
        $error_message = 'Please fill in all fields.';
    } else {
        if (login_user($username, $password, 'staff')) {
            header('Location: dashboard.php');
            exit();
        } else {
            $error_message = 'Invalid username or password.';
        }
    }
}

$flash = get_flash_message();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Login - Goba Hospital</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="login-container staff-login">
        <div class="login-header">
            <div class="logo">
                <i class="fas fa-users"></i>
                <h1>Goba Hospital</h1>
            </div>
            <h2>Medical Staff Portal</h2>
        </div>

        <div class="form-container">
            <?php if ($flash): ?>
                <div class="alert alert-<?php echo $flash['type']; ?>">
                    <?php echo htmlspecialchars($flash['message']); ?>
                </div>
            <?php endif; ?>

            <?php if ($error_message): ?>
                <div class="alert alert-danger">
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="username">
                        <i class="fas fa-user"></i> Username
                    </label>
                    <input type="text" id="username" name="username" class="form-control" required 
                           value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                </div>

                <div class="form-group">
                    <label for="password">
                        <i class="fas fa-lock"></i> Password
                    </label>
                    <input type="password" id="password" name="password" class="form-control" required>
                </div>

                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fas fa-sign-in-alt"></i> Login to Staff Portal
                </button>
            </form>

            <div class="login-links">
                <p>For medical staff and nurses only.</p>
                <div class="portal-links">
                    <a href="../patient/login.php">Patient Portal</a>
                    <a href="../doctor/login.php">Doctor Portal</a>
                    <a href="../admin/login.php">Admin Portal</a>
                </div>
                <a href="../index.html" class="back-home">
                    <i class="fas fa-home"></i> Back to Home
                </a>
            </div>
        </div>
    </div>

    <style>
        .staff-login {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        }

        .staff-login .btn-primary {
            background: #d97706;
        }

        .staff-login .btn-primary:hover {
            background: #b45309;
        }

        .staff-login .portal-links a {
            color: #d97706;
        }

        .staff-login .back-home {
            color: #d97706;
        }

        .login-container {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 2rem;
        }

        .login-header {
            text-align: center;
            color: white;
            margin-bottom: 2rem;
        }

        .logo {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .logo i {
            font-size: 3rem;
        }

        .logo h1 {
            font-size: 2.5rem;
            margin: 0;
        }

        .login-header h2 {
            font-size: 1.5rem;
            margin: 0;
            opacity: 0.9;
        }

        .form-container {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }

        .form-group label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #374151;
        }

        .btn-block {
            width: 100%;
            margin-top: 1rem;
        }

        .login-links {
            text-align: center;
            margin-top: 2rem;
            color: #6b7280;
        }

        .portal-links {
            display: flex;
            justify-content: center;
            gap: 1rem;
            margin: 1rem 0;
            flex-wrap: wrap;
        }

        .portal-links a {
            text-decoration: none;
            font-size: 0.875rem;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            transition: background-color 0.3s;
        }

        .portal-links a:hover {
            background-color: #f3f4f6;
        }

        .back-home {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
            margin-top: 1rem;
            font-weight: 500;
        }

        .back-home:hover {
            text-decoration: underline;
        }

        @media (max-width: 480px) {
            .login-container {
                padding: 1rem;
            }
            
            .form-container {
                padding: 1.5rem;
            }
            
            .logo h1 {
                font-size: 2rem;
            }
            
            .portal-links {
                flex-direction: column;
                gap: 0.5rem;
            }
        }
    </style>
</body>
</html>