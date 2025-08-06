<?php
require_once '../includes/functions.php';
require_login(['admin']);

$admin_info = get_user_info($_SESSION['user_id'], 'admin');

// Get system statistics
$system_stats = [
    'total_patients' => getRowCount("SELECT COUNT(*) FROM patient"),
    'total_doctors' => getRowCount("SELECT COUNT(*) FROM doctor"),
    'total_staff' => getRowCount("SELECT COUNT(*) FROM medical_staff"),
    'total_hospitals' => getRowCount("SELECT COUNT(*) FROM hospital"),
    'total_consultations' => getRowCount("SELECT COUNT(*) FROM consultation"),
    'total_operations' => getRowCount("SELECT COUNT(*) FROM operation"),
    'total_diagnoses' => getRowCount("SELECT COUNT(*) FROM diagnosis"),
    'total_medications' => getRowCount("SELECT COUNT(*) FROM medical_administration"),
    'active_patients' => getRowCount("
        SELECT COUNT(DISTINCT patient_ssn) FROM consultation 
        WHERE consultation_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAYS)
    "),
    'recent_registrations' => getRowCount("
        SELECT COUNT(*) FROM patient 
        WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAYS)
    "),
    'payments_this_month' => getRowCount("
        SELECT COUNT(*) FROM payment 
        WHERE YEAR(payment_date) = YEAR(CURDATE()) 
        AND MONTH(payment_date) = MONTH(CURDATE())
    "),
    'pending_transfers' => getRowCount("
        SELECT COUNT(*) FROM patient_transfer 
        WHERE status = 'Pending'
    ")
];

// Get recent activities
$recent_patients = getRows("
    SELECT p.*, pl.created_at as registration_date
    FROM patient p 
    JOIN patient_login pl ON p.ssn = pl.patient_ssn
    ORDER BY pl.created_at DESC 
    LIMIT 5
") ?: [];

$recent_doctors = getRows("
    SELECT d.*, dl.created_at as registration_date
    FROM doctor d 
    JOIN doctor_login dl ON d.ssn = dl.doctor_ssn
    ORDER BY dl.created_at DESC 
    LIMIT 5
") ?: [];

$recent_staff = getRows("
    SELECT s.*, sl.created_at as registration_date
    FROM medical_staff s 
    JOIN staff_login sl ON s.ssn = sl.staff_ssn
    ORDER BY sl.created_at DESC 
    LIMIT 5
") ?: [];

// Get pending transfers
$pending_transfers = getRows("
    SELECT pt.*, CONCAT(p.first_name, ' ', p.last_name) as patient_name,
           h.name as destination_hospital
    FROM patient_transfer pt
    JOIN patient p ON pt.patient_ssn = p.ssn
    JOIN hospital h ON pt.destination_hospital_id = h.id
    WHERE pt.status = 'Pending'
    ORDER BY pt.transfer_date DESC
    LIMIT 5
") ?: [];

// Get system alerts
$system_alerts = [];

// Check for inactive users
$inactive_doctors = getRowCount("
    SELECT COUNT(*) FROM doctor_login 
    WHERE last_login < DATE_SUB(CURDATE(), INTERVAL 30 DAYS) AND is_active = 1
");
if ($inactive_doctors > 0) {
    $system_alerts[] = [
        'type' => 'warning',
        'message' => "{$inactive_doctors} doctors haven't logged in for 30+ days",
        'action' => 'user_management.php'
    ];
}

// Check for failed login attempts
$failed_logins = getRowCount("
    SELECT COUNT(*) FROM audit_log 
    WHERE action = 'Failed Login' AND created_at >= DATE_SUB(CURDATE(), INTERVAL 24 HOURS)
");
if ($failed_logins > 10) {
    $system_alerts[] = [
        'type' => 'danger',
        'message' => "{$failed_logins} failed login attempts in the last 24 hours",
        'action' => 'security.php'
    ];
}

// Check storage space (placeholder)
$system_alerts[] = [
    'type' => 'info',
    'message' => 'System backup completed successfully',
    'action' => 'system.php'
];

$flash = get_flash_message();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Goba Hospital</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <nav class="dashboard-nav admin-nav">
        <div class="nav-brand">
            <i class="fas fa-hospital"></i>
            <span>Goba Hospital - Admin</span>
        </div>
        <div class="nav-user">
            <span>Admin: <?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
            <div class="dropdown">
                <button class="dropdown-toggle">
                    <i class="fas fa-user-shield"></i>
                    <i class="fas fa-chevron-down"></i>
                </button>
                <div class="dropdown-menu">
                    <a href="profile.php"><i class="fas fa-user"></i> My Profile</a>
                    <a href="settings.php"><i class="fas fa-cog"></i> System Settings</a>
                    <a href="audit.php"><i class="fas fa-history"></i> Audit Log</a>
                    <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar admin-sidebar">
            <ul class="sidebar-menu">
                <li class="active">
                    <a href="dashboard.php">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="user_management.php">
                        <i class="fas fa-users-cog"></i>
                        <span>User Management</span>
                    </a>
                </li>
                <li>
                    <a href="register_users.php">
                        <i class="fas fa-user-plus"></i>
                        <span>Register Users</span>
                    </a>
                </li>
                <li>
                    <a href="hospital_management.php">
                        <i class="fas fa-building"></i>
                        <span>Hospital Management</span>
                    </a>
                </li>
                <li>
                    <a href="patient_referrals.php">
                        <i class="fas fa-exchange-alt"></i>
                        <span>Patient Referrals</span>
                    </a>
                </li>
                <li>
                    <a href="reports.php">
                        <i class="fas fa-chart-bar"></i>
                        <span>Reports & Analytics</span>
                    </a>
                </li>
                <li>
                    <a href="audit.php">
                        <i class="fas fa-history"></i>
                        <span>Audit Log</span>
                    </a>
                </li>
                <li>
                    <a href="system.php">
                        <i class="fas fa-server"></i>
                        <span>System Settings</span>
                    </a>
                </li>
                <li>
                    <a href="security.php">
                        <i class="fas fa-shield-alt"></i>
                        <span>Security</span>
                    </a>
                </li>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <?php if ($flash): ?>
                <div class="alert alert-<?php echo $flash['type']; ?>">
                    <?php echo htmlspecialchars($flash['message']); ?>
                </div>
            <?php endif; ?>

            <!-- Welcome Header -->
            <div class="dashboard-header">
                <div class="header-content">
                    <h1><i class="fas fa-tachometer-alt"></i> System Administration Dashboard</h1>
                    <p>Welcome, <?php echo htmlspecialchars($admin_info['full_name']); ?>! Monitor and manage the entire hospital system.</p>
                </div>
                <div class="header-actions">
                    <button class="btn btn-primary" onclick="location.href='register_users.php'">
                        <i class="fas fa-user-plus"></i> Register User
                    </button>
                    <button class="btn btn-secondary" onclick="location.href='reports.php'">
                        <i class="fas fa-chart-bar"></i> Generate Report
                    </button>
                </div>
            </div>

            <!-- System Alerts -->
            <?php if (!empty($system_alerts)): ?>
                <div class="system-alerts">
                    <h3><i class="fas fa-exclamation-triangle"></i> System Alerts</h3>
                    <div class="alerts-list">
                        <?php foreach ($system_alerts as $alert): ?>
                            <div class="alert-item alert-<?php echo $alert['type']; ?>">
                                <div class="alert-content">
                                    <span><?php echo htmlspecialchars($alert['message']); ?></span>
                                </div>
                                <div class="alert-action">
                                    <a href="<?php echo $alert['action']; ?>" class="btn btn-sm btn-outline">
                                        <i class="fas fa-arrow-right"></i>
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Statistics Overview -->
            <div class="stats-overview">
                <h3><i class="fas fa-chart-line"></i> System Overview</h3>
                <div class="stats-grid">
                    <div class="stat-card users">
                        <div class="stat-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $system_stats['total_patients']; ?></h3>
                            <p>Total Patients</p>
                            <span class="stat-change">
                                <i class="fas fa-user-plus"></i>
                                +<?php echo $system_stats['recent_registrations']; ?> this week
                            </span>
                        </div>
                    </div>

                    <div class="stat-card doctors">
                        <div class="stat-icon">
                            <i class="fas fa-user-md"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $system_stats['total_doctors']; ?></h3>
                            <p>Doctors</p>
                            <span class="stat-change">
                                <i class="fas fa-stethoscope"></i>
                                Active physicians
                            </span>
                        </div>
                    </div>

                    <div class="stat-card staff">
                        <div class="stat-icon">
                            <i class="fas fa-user-nurse"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $system_stats['total_staff']; ?></h3>
                            <p>Medical Staff</p>
                            <span class="stat-change">
                                <i class="fas fa-hospital-user"></i>
                                Nursing staff
                            </span>
                        </div>
                    </div>

                    <div class="stat-card hospitals">
                        <div class="stat-icon">
                            <i class="fas fa-building"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $system_stats['total_hospitals']; ?></h3>
                            <p>Hospitals</p>
                            <span class="stat-change">
                                <i class="fas fa-network-wired"></i>
                                Connected facilities
                            </span>
                        </div>
                    </div>

                    <div class="stat-card activities">
                        <div class="stat-icon">
                            <i class="fas fa-activity"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $system_stats['total_consultations']; ?></h3>
                            <p>Consultations</p>
                            <span class="stat-change">
                                <i class="fas fa-calendar-check"></i>
                                Total recorded
                            </span>
                        </div>
                    </div>

                    <div class="stat-card operations">
                        <div class="stat-icon">
                            <i class="fas fa-procedures"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $system_stats['total_operations']; ?></h3>
                            <p>Operations</p>
                            <span class="stat-change">
                                <i class="fas fa-surgical-tools"></i>
                                Surgical procedures
                            </span>
                        </div>
                    </div>

                    <div class="stat-card diagnoses">
                        <div class="stat-icon">
                            <i class="fas fa-diagnoses"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $system_stats['total_diagnoses']; ?></h3>
                            <p>Diagnoses</p>
                            <span class="stat-change">
                                <i class="fas fa-clipboard-list"></i>
                                Medical diagnoses
                            </span>
                        </div>
                    </div>

                    <div class="stat-card medications">
                        <div class="stat-icon">
                            <i class="fas fa-pills"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $system_stats['total_medications']; ?></h3>
                            <p>Medications</p>
                            <span class="stat-change">
                                <i class="fas fa-prescription"></i>
                                Administered doses
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Dashboard Content -->
            <div class="dashboard-grid">
                <!-- Recent User Registrations -->
                <div class="dashboard-card recent-users-card">
                    <div class="card-header">
                        <h3><i class="fas fa-user-plus"></i> Recent User Registrations</h3>
                        <a href="user_management.php" class="card-action">Manage All Users</a>
                    </div>
                    <div class="card-content">
                        <div class="tabs-container">
                            <div class="tab-buttons">
                                <button class="tab-btn active" data-tab="patients">Patients</button>
                                <button class="tab-btn" data-tab="doctors">Doctors</button>
                                <button class="tab-btn" data-tab="staff">Staff</button>
                            </div>
                            
                            <div class="tab-content active" id="patients">
                                <?php if (!empty($recent_patients)): ?>
                                    <div class="users-list">
                                        <?php foreach ($recent_patients as $patient): ?>
                                            <div class="user-item">
                                                <div class="user-avatar patient">
                                                    <i class="fas fa-user"></i>
                                                </div>
                                                <div class="user-info">
                                                    <h4><?php echo htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']); ?></h4>
                                                    <p>ID: <?php echo htmlspecialchars($patient['ssn']); ?></p>
                                                    <small>Registered: <?php echo format_datetime($patient['registration_date']); ?></small>
                                                </div>
                                                <div class="user-actions">
                                                    <button class="btn btn-sm btn-info" onclick="viewUser('patient', '<?php echo $patient['ssn']; ?>')">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="empty-state">
                                        <i class="fas fa-users"></i>
                                        <p>No recent patient registrations</p>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="tab-content" id="doctors">
                                <?php if (!empty($recent_doctors)): ?>
                                    <div class="users-list">
                                        <?php foreach ($recent_doctors as $doctor): ?>
                                            <div class="user-item">
                                                <div class="user-avatar doctor">
                                                    <i class="fas fa-user-md"></i>
                                                </div>
                                                <div class="user-info">
                                                    <h4>Dr. <?php echo htmlspecialchars($doctor['first_name'] . ' ' . $doctor['last_name']); ?></h4>
                                                    <p><?php echo htmlspecialchars($doctor['specialization'] ?: 'General Medicine'); ?></p>
                                                    <small>Registered: <?php echo format_datetime($doctor['registration_date']); ?></small>
                                                </div>
                                                <div class="user-actions">
                                                    <button class="btn btn-sm btn-info" onclick="viewUser('doctor', '<?php echo $doctor['ssn']; ?>')">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="empty-state">
                                        <i class="fas fa-user-md"></i>
                                        <p>No recent doctor registrations</p>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="tab-content" id="staff">
                                <?php if (!empty($recent_staff)): ?>
                                    <div class="users-list">
                                        <?php foreach ($recent_staff as $staff): ?>
                                            <div class="user-item">
                                                <div class="user-avatar staff">
                                                    <i class="fas fa-user-nurse"></i>
                                                </div>
                                                <div class="user-info">
                                                    <h4><?php echo htmlspecialchars($staff['first_name'] . ' ' . $staff['last_name']); ?></h4>
                                                    <p><?php echo htmlspecialchars($staff['department'] ?: 'Medical Staff'); ?></p>
                                                    <small>Registered: <?php echo format_datetime($staff['registration_date']); ?></small>
                                                </div>
                                                <div class="user-actions">
                                                    <button class="btn btn-sm btn-info" onclick="viewUser('staff', '<?php echo $staff['ssn']; ?>')">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="empty-state">
                                        <i class="fas fa-user-nurse"></i>
                                        <p>No recent staff registrations</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pending Patient Referrals -->
                <div class="dashboard-card referrals-card">
                    <div class="card-header">
                        <h3><i class="fas fa-exchange-alt"></i> Pending Patient Referrals</h3>
                        <span class="card-badge"><?php echo count($pending_transfers); ?> pending</span>
                    </div>
                    <div class="card-content">
                        <?php if (!empty($pending_transfers)): ?>
                            <div class="referrals-list">
                                <?php foreach ($pending_transfers as $transfer): ?>
                                    <div class="referral-item">
                                        <div class="referral-info">
                                            <h4><?php echo htmlspecialchars($transfer['patient_name']); ?></h4>
                                            <p>To: <?php echo htmlspecialchars($transfer['destination_hospital']); ?></p>
                                            <small>Transfer Date: <?php echo format_datetime($transfer['transfer_date']); ?></small>
                                            <?php if ($transfer['reason']): ?>
                                                <div class="referral-reason">
                                                    <strong>Reason:</strong> <?php echo htmlspecialchars(substr($transfer['reason'], 0, 50)); ?>...
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="referral-actions">
                                            <button class="btn btn-sm btn-success" onclick="approveTransfer('<?php echo $transfer['id']; ?>')">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button class="btn btn-sm btn-info" onclick="viewTransfer('<?php echo $transfer['id']; ?>')">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="card-footer">
                                <a href="patient_referrals.php" class="view-all-link">
                                    View all referrals
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="fas fa-check-circle"></i>
                                <h4>No pending referrals</h4>
                                <p>All patient transfers are up to date.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="dashboard-card actions-card">
                    <div class="card-header">
                        <h3><i class="fas fa-bolt"></i> Quick Admin Actions</h3>
                    </div>
                    <div class="card-content">
                        <div class="quick-actions">
                            <button class="action-btn register" onclick="location.href='register_users.php'">
                                <i class="fas fa-user-plus"></i>
                                <span>Register User</span>
                            </button>
                            
                            <button class="action-btn manage" onclick="location.href='user_management.php'">
                                <i class="fas fa-users-cog"></i>
                                <span>Manage Users</span>
                            </button>
                            
                            <button class="action-btn hospital" onclick="location.href='hospital_management.php'">
                                <i class="fas fa-building"></i>
                                <span>Hospital Settings</span>
                            </button>
                            
                            <button class="action-btn reports" onclick="location.href='reports.php'">
                                <i class="fas fa-chart-bar"></i>
                                <span>View Reports</span>
                            </button>
                            
                            <button class="action-btn audit" onclick="location.href='audit.php'">
                                <i class="fas fa-history"></i>
                                <span>Audit Log</span>
                            </button>
                            
                            <button class="action-btn security" onclick="location.href='security.php'">
                                <i class="fas fa-shield-alt"></i>
                                <span>Security</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- System Status -->
                <div class="dashboard-card system-status-card">
                    <div class="card-header">
                        <h3><i class="fas fa-server"></i> System Status</h3>
                        <span class="status-badge online">Online</span>
                    </div>
                    <div class="card-content">
                        <div class="status-items">
                            <div class="status-item">
                                <div class="status-icon online">
                                    <i class="fas fa-database"></i>
                                </div>
                                <div class="status-info">
                                    <h4>Database</h4>
                                    <p>All connections healthy</p>
                                </div>
                            </div>
                            
                            <div class="status-item">
                                <div class="status-icon online">
                                    <i class="fas fa-cloud"></i>
                                </div>
                                <div class="status-info">
                                    <h4>File Storage</h4>
                                    <p>85% capacity available</p>
                                </div>
                            </div>
                            
                            <div class="status-item">
                                <div class="status-icon warning">
                                    <i class="fas fa-shield-alt"></i>
                                </div>
                                <div class="status-info">
                                    <h4>Security</h4>
                                    <p><?php echo $failed_logins; ?> failed logins today</p>
                                </div>
                            </div>
                            
                            <div class="status-item">
                                <div class="status-icon online">
                                    <i class="fas fa-sync"></i>
                                </div>
                                <div class="status-info">
                                    <h4>Last Backup</h4>
                                    <p><?php echo date('Y-m-d H:i'); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <style>
        .admin-nav {
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
        }

        .admin-sidebar {
            border-right: 3px solid #6366f1;
        }

        .admin-sidebar .sidebar-menu li.active a,
        .admin-sidebar .sidebar-menu li:hover a {
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            color: white;
        }

        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding: 1.5rem;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .header-actions {
            display: flex;
            gap: 1rem;
        }

        .system-alerts {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            padding: 1.5rem;
            margin-bottom: 2rem;
        }

        .system-alerts h3 {
            color: #1e293b;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .alerts-list {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .alert-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem;
            border-radius: 8px;
            border-left: 4px solid;
        }

        .alert-item.alert-warning {
            background: #fef3c7;
            border-color: #f59e0b;
            color: #92400e;
        }

        .alert-item.alert-danger {
            background: #fef2f2;
            border-color: #ef4444;
            color: #991b1b;
        }

        .alert-item.alert-info {
            background: #dbeafe;
            border-color: #3b82f6;
            color: #1e40af;
        }

        .stats-overview {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            padding: 2rem;
            margin-bottom: 2rem;
        }

        .stats-overview h3 {
            color: #1e293b;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
        }

        .stat-card {
            background: #f8fafc;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            gap: 1rem;
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
        }

        .stat-card.users::before { background: #3b82f6; }
        .stat-card.doctors::before { background: #10b981; }
        .stat-card.staff::before { background: #f59e0b; }
        .stat-card.hospitals::before { background: #8b5cf6; }
        .stat-card.activities::before { background: #ef4444; }
        .stat-card.operations::before { background: #06b6d4; }
        .stat-card.diagnoses::before { background: #f97316; }
        .stat-card.medications::before { background: #84cc16; }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
        }

        .users .stat-icon { background: #3b82f6; }
        .doctors .stat-icon { background: #10b981; }
        .staff .stat-icon { background: #f59e0b; }
        .hospitals .stat-icon { background: #8b5cf6; }
        .activities .stat-icon { background: #ef4444; }
        .operations .stat-icon { background: #06b6d4; }
        .diagnoses .stat-icon { background: #f97316; }
        .medications .stat-icon { background: #84cc16; }

        .stat-info h3 {
            font-size: 2rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 0.25rem;
        }

        .stat-info p {
            color: #64748b;
            font-weight: 500;
            margin-bottom: 0.25rem;
        }

        .stat-change {
            font-size: 0.75rem;
            display: flex;
            align-items: center;
            gap: 0.25rem;
            color: #059669;
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 1.5rem;
        }

        .dashboard-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .card-header {
            background: #f8fafc;
            padding: 1.5rem;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card-header h3 {
            margin: 0;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .card-badge {
            background: #dbeafe;
            color: #1d4ed8;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .card-action {
            color: #6366f1;
            text-decoration: none;
            font-size: 0.875rem;
            font-weight: 500;
        }

        .card-content {
            padding: 1.5rem;
        }

        .tabs-container {
            margin-bottom: 1rem;
        }

        .tab-buttons {
            display: flex;
            border-bottom: 1px solid #e5e7eb;
            margin-bottom: 1rem;
        }

        .tab-btn {
            background: none;
            border: none;
            padding: 0.75rem 1rem;
            color: #6b7280;
            font-weight: 500;
            cursor: pointer;
            border-bottom: 2px solid transparent;
            transition: all 0.3s ease;
        }

        .tab-btn.active {
            color: #6366f1;
            border-bottom-color: #6366f1;
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        .users-list, .referrals-list {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .user-item, .referral-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            border: 1px solid #f3f4f6;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .user-item:hover, .referral-item:hover {
            border-color: #6366f1;
            box-shadow: 0 2px 8px rgba(99, 102, 241, 0.1);
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1rem;
        }

        .user-avatar.patient { background: #3b82f6; }
        .user-avatar.doctor { background: #10b981; }
        .user-avatar.staff { background: #f59e0b; }

        .user-info, .referral-info {
            flex: 1;
        }

        .user-info h4, .referral-info h4 {
            color: #1e293b;
            margin-bottom: 0.25rem;
        }

        .user-info p, .referral-info p {
            color: #6b7280;
            margin-bottom: 0.25rem;
            font-size: 0.875rem;
        }

        .referral-reason {
            color: #374151;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }

        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 1rem;
        }

        .action-btn {
            background: white;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            padding: 1.5rem 1rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            color: #374151;
        }

        .action-btn:hover {
            border-color: #6366f1;
            color: #6366f1;
            box-shadow: 0 4px 15px rgba(99, 102, 241, 0.1);
        }

        .action-btn i {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
            display: block;
        }

        .action-btn span {
            font-size: 0.875rem;
            font-weight: 500;
        }

        .status-items {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .status-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            background: #f8fafc;
            border-radius: 8px;
        }

        .status-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1rem;
        }

        .status-icon.online { background: #10b981; }
        .status-icon.warning { background: #f59e0b; }
        .status-icon.error { background: #ef4444; }

        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-badge.online { background: #dcfce7; color: #166534; }

        .empty-state {
            text-align: center;
            padding: 2rem 1rem;
            color: #6b7280;
        }

        .empty-state i {
            font-size: 2rem;
            margin-bottom: 1rem;
            color: #d1d5db;
        }

        @media (max-width: 768px) {
            .dashboard-header {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }

            .header-actions {
                width: 100%;
                justify-content: center;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .dashboard-grid {
                grid-template-columns: 1fr;
            }

            .quick-actions {
                grid-template-columns: repeat(2, 1fr);
            }

            .tab-buttons {
                flex-wrap: wrap;
            }
        }
    </style>

    <script>
        // Tab functionality
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const tabName = btn.getAttribute('data-tab');
                const container = btn.closest('.tabs-container');

                // Remove active class from all tabs and contents
                container.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
                container.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));

                // Add active class to clicked tab and corresponding content
                btn.classList.add('active');
                document.getElementById(tabName).classList.add('active');
            });
        });

        // Action functions
        function viewUser(type, id) {
            window.location.href = `user_management.php?type=${type}&view=${id}`;
        }

        function approveTransfer(transferId) {
            if (confirm('Approve this patient transfer?')) {
                window.location.href = `patient_referrals.php?action=approve&id=${transferId}`;
            }
        }

        function viewTransfer(transferId) {
            window.location.href = `patient_referrals.php?view=${transferId}`;
        }

        // Real-time updates (placeholder)
        function updateSystemStatus() {
            // In a real application, this would fetch real-time system status
            console.log('Updating system status...');
        }

        setInterval(updateSystemStatus, 30000); // Update every 30 seconds

        // Auto-refresh dashboard
        function refreshDashboard() {
            // Auto-refresh dashboard data every 5 minutes
            setTimeout(() => {
                window.location.reload();
            }, 300000);
        }

        refreshDashboard();
    </script>

    <script src="../assets/js/script.js"></script>
</body>
</html>