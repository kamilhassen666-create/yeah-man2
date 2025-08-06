<?php
require_once '../includes/auth.php';

$auth = new Auth();
$auth->requireLogin(['patient']);

$userInfo = $_SESSION['user_info'] ?? [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Dashboard - Goba Hospital</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <nav class="navbar">
            <div class="nav-container">
                <div class="nav-logo">
                    <i class="fas fa-hospital"></i>
                    <span>Goba Hospital - Patient Portal</span>
                </div>
                <ul class="nav-menu">
                    <li class="nav-item">
                        <a href="dashboard.php" class="nav-link">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a href="profile.php" class="nav-link">Profile</a>
                    </li>
                    <li class="nav-item">
                        <a href="records.php" class="nav-link">Medical Records</a>
                    </li>
                    <li class="nav-item">
                        <a href="appointments.php" class="nav-link">Appointments</a>
                    </li>
                    <li class="nav-item">
                        <a href="../auth/logout.php" class="nav-link">Logout</a>
                    </li>
                </ul>
                <div class="hamburger">
                    <span class="bar"></span>
                    <span class="bar"></span>
                    <span class="bar"></span>
                </div>
            </div>
        </nav>
    </header>

    <!-- Main Content -->
    <main class="dashboard">
        <div class="container">
            <div class="dashboard-header">
                <div class="user-info">
                    <div class="user-details">
                        <h2>Welcome, <?php echo htmlspecialchars(($userInfo['first_name'] ?? '') . ' ' . ($userInfo['last_name'] ?? '')); ?></h2>
                        <p>Patient Portal - Manage your health records</p>
                    </div>
                    <div class="user-actions">
                        <a href="profile.php" class="btn btn-primary">
                            <i class="fas fa-user"></i> View Profile
                        </a>
                    </div>
                </div>
            </div>

            <div class="table-container">
                <div class="table-header">
                    <h3>Patient Dashboard</h3>
                </div>
                <div style="padding: 2rem; text-align: center;">
                    <p>Welcome to your patient portal. From here you can:</p>
                    <ul style="text-align: left; max-width: 500px; margin: 2rem auto;">
                        <li>View your medical records and history</li>
                        <li>Update your personal information</li>
                        <li>Search for specific medical information</li>
                        <li>View consultation and diagnosis records</li>
                        <li>Manage payment information</li>
                    </ul>
                </div>
            </div>
        </div>
    </main>

    <script src="../assets/js/main.js"></script>
</body>
</html>