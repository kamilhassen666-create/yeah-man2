<?php
require_once '../includes/auth.php';
require_once '../config/database.php';

$auth = new Auth();
$auth->requireLogin(['admin']);

$database = new Database();
$db = $database->getConnection();

// Get system statistics
try {
    // Total users
    $stmt = $db->query("SELECT COUNT(*) as total_patients FROM patient");
    $totalPatients = $stmt->fetch(PDO::FETCH_ASSOC)['total_patients'];
    
    $stmt = $db->query("SELECT COUNT(*) as total_doctors FROM doctor");
    $totalDoctors = $stmt->fetch(PDO::FETCH_ASSOC)['total_doctors'];
    
    $stmt = $db->query("SELECT COUNT(*) as total_staff FROM medical_staff");
    $totalStaff = $stmt->fetch(PDO::FETCH_ASSOC)['total_staff'];
    
    // Recent activities
    $stmt = $db->query("SELECT COUNT(*) as total_consultations FROM consultation WHERE DATE(created_at) = CURDATE()");
    $todayConsultations = $stmt->fetch(PDO::FETCH_ASSOC)['total_consultations'];
    
    $stmt = $db->query("SELECT COUNT(*) as total_operations FROM operation WHERE DATE(created_at) = CURDATE()");
    $todayOperations = $stmt->fetch(PDO::FETCH_ASSOC)['total_operations'];
    
    $stmt = $db->query("SELECT COUNT(*) as total_payments FROM payments WHERE DATE(payment_date) = CURDATE()");
    $todayPayments = $stmt->fetch(PDO::FETCH_ASSOC)['total_payments'];
    
} catch (Exception $e) {
    $totalPatients = $totalDoctors = $totalStaff = 0;
    $todayConsultations = $todayOperations = $todayPayments = 0;
}

$userInfo = $_SESSION['user_info'] ?? [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Goba Hospital</title>
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
                    <span>Goba Hospital - Admin</span>
                </div>
                <ul class="nav-menu">
                    <li class="nav-item">
                        <a href="dashboard.php" class="nav-link">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a href="register.php" class="nav-link">Register Users</a>
                    </li>
                    <li class="nav-item">
                        <a href="manage-users.php" class="nav-link">Manage Users</a>
                    </li>
                    <li class="nav-item">
                        <a href="reports.php" class="nav-link">Reports</a>
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
            <!-- Dashboard Header -->
            <div class="dashboard-header">
                <div class="user-info">
                    <div class="user-details">
                        <h2>Welcome, <?php echo htmlspecialchars($userInfo['name'] ?? 'Administrator'); ?></h2>
                        <p>System Administrator Dashboard</p>
                    </div>
                    <div class="user-actions">
                        <a href="register.php" class="btn btn-primary">
                            <i class="fas fa-user-plus"></i> Register New User
                        </a>
                        <a href="reports.php" class="btn btn-secondary">
                            <i class="fas fa-chart-bar"></i> View Reports
                        </a>
                    </div>
                </div>
            </div>

            <!-- Statistics Grid -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number"><?php echo $totalPatients; ?></div>
                    <div class="stat-label">Total Patients</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $totalDoctors; ?></div>
                    <div class="stat-label">Total Doctors</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $totalStaff; ?></div>
                    <div class="stat-label">Medical Staff</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $todayConsultations; ?></div>
                    <div class="stat-label">Today's Consultations</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $todayOperations; ?></div>
                    <div class="stat-label">Today's Operations</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $todayPayments; ?></div>
                    <div class="stat-label">Today's Payments</div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="table-container">
                <div class="table-header">
                    <h3>Quick Actions</h3>
                </div>
                <div style="padding: 2rem;">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem;">
                        <a href="register.php?type=patient" class="btn btn-primary" style="padding: 1rem; text-align: center;">
                            <i class="fas fa-user-injured" style="font-size: 2rem; display: block; margin-bottom: 0.5rem;"></i>
                            Register New Patient
                        </a>
                        <a href="register.php?type=doctor" class="btn btn-primary" style="padding: 1rem; text-align: center;">
                            <i class="fas fa-user-md" style="font-size: 2rem; display: block; margin-bottom: 0.5rem;"></i>
                            Register New Doctor
                        </a>
                        <a href="register.php?type=staff" class="btn btn-primary" style="padding: 1rem; text-align: center;">
                            <i class="fas fa-user-nurse" style="font-size: 2rem; display: block; margin-bottom: 0.5rem;"></i>
                            Register Medical Staff
                        </a>
                        <a href="manage-users.php" class="btn btn-secondary" style="padding: 1rem; text-align: center;">
                            <i class="fas fa-users-cog" style="font-size: 2rem; display: block; margin-bottom: 0.5rem;"></i>
                            Manage All Users
                        </a>
                        <a href="system-settings.php" class="btn btn-secondary" style="padding: 1rem; text-align: center;">
                            <i class="fas fa-cogs" style="font-size: 2rem; display: block; margin-bottom: 0.5rem;"></i>
                            System Settings
                        </a>
                        <a href="backup.php" class="btn btn-secondary" style="padding: 1rem; text-align: center;">
                            <i class="fas fa-download" style="font-size: 2rem; display: block; margin-bottom: 0.5rem;"></i>
                            Backup System
                        </a>
                    </div>
                </div>
            </div>

            <!-- Recent Activities -->
            <div class="table-container">
                <div class="table-header">
                    <h3>Recent System Activities</h3>
                </div>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Time</th>
                            <th>Activity</th>
                            <th>User</th>
                            <th>Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        try {
                            // Get recent login activities
                            $stmt = $db->prepare("
                                SELECT 'Patient Login' as activity, p.first_name, p.last_name, pl.last_login as activity_time
                                FROM patient_login pl 
                                JOIN patient p ON pl.patient_ssn = p.ssn 
                                WHERE pl.last_login IS NOT NULL
                                UNION ALL
                                SELECT 'Doctor Login' as activity, d.first_name, d.last_name, dl.last_login as activity_time
                                FROM doctor_login dl 
                                JOIN doctor d ON dl.doctor_ssn = d.ssn 
                                WHERE dl.last_login IS NOT NULL
                                UNION ALL
                                SELECT 'Staff Login' as activity, ms.first_name, ms.last_name, sl.last_login as activity_time
                                FROM staff_login sl 
                                JOIN medical_staff ms ON sl.staff_ssn = ms.ssn 
                                WHERE sl.last_login IS NOT NULL
                                ORDER BY activity_time DESC 
                                LIMIT 10
                            ");
                            $stmt->execute();
                            $activities = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            
                            if (empty($activities)) {
                                echo '<tr><td colspan="4" style="text-align: center; color: var(--dark-gray);">No recent activities found</td></tr>';
                            } else {
                                foreach ($activities as $activity) {
                                    echo '<tr>';
                                    echo '<td>' . date('M j, Y H:i', strtotime($activity['activity_time'])) . '</td>';
                                    echo '<td>' . htmlspecialchars($activity['activity']) . '</td>';
                                    echo '<td>' . htmlspecialchars($activity['first_name'] . ' ' . $activity['last_name']) . '</td>';
                                    echo '<td>Successful login to system</td>';
                                    echo '</tr>';
                                }
                            }
                        } catch (Exception $e) {
                            echo '<tr><td colspan="4" style="text-align: center; color: var(--danger);">Error loading activities</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>

            <!-- System Information -->
            <div class="table-container">
                <div class="table-header">
                    <h3>System Information</h3>
                </div>
                <div style="padding: 2rem;">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem;">
                        <div>
                            <h4 style="color: var(--primary-green); margin-bottom: 1rem;">Database Status</h4>
                            <p><strong>Status:</strong> <span class="badge badge-success">Connected</span></p>
                            <p><strong>Total Records:</strong> <?php echo $totalPatients + $totalDoctors + $totalStaff; ?></p>
                            <p><strong>Last Backup:</strong> Not configured</p>
                        </div>
                        <div>
                            <h4 style="color: var(--primary-green); margin-bottom: 1rem;">Server Information</h4>
                            <p><strong>PHP Version:</strong> <?php echo phpversion(); ?></p>
                            <p><strong>Server Time:</strong> <?php echo date('Y-m-d H:i:s'); ?></p>
                            <p><strong>Uptime:</strong> <?php echo date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME']); ?></p>
                        </div>
                        <div>
                            <h4 style="color: var(--primary-green); margin-bottom: 1rem;">Quick Links</h4>
                            <p><a href="../index.php">Main Website</a></p>
                            <p><a href="system-logs.php">System Logs</a></p>
                            <p><a href="user-audit.php">User Audit Trail</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="../assets/js/main.js"></script>
    <script>
        // Auto-refresh stats every 30 seconds
        setInterval(function() {
            // You can implement AJAX refresh here if needed
        }, 30000);
        
        // Show success message if redirected from registration
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('success') === 'registered') {
            showAlert('User registered successfully!', 'success');
        }
    </script>
</body>
</html>