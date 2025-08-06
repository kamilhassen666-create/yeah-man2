<?php
require_once '../includes/functions.php';
require_login(['staff']);

$staff_info = get_user_info($_SESSION['user_id'], 'staff');

// Get today's medication schedule
$today = date('Y-m-d');
$todays_medications = getRows("
    SELECT m.*, CONCAT(p.first_name, ' ', p.last_name) as patient_name, 
           p.phone, p.date_of_birth, p.allergies, p.blood_type
    FROM medical_administration m 
    JOIN patient p ON m.patient_ssn = p.ssn 
    WHERE m.staff_ssn = ? AND DATE(m.administration_date) = ?
    ORDER BY m.administration_date ASC
", [$_SESSION['user_id'], $today]) ?: [];

// Get pending medications
$pending_medications = getRows("
    SELECT m.*, CONCAT(p.first_name, ' ', p.last_name) as patient_name, 
           p.phone, p.allergies, p.blood_type
    FROM medical_administration m 
    JOIN patient p ON m.patient_ssn = p.ssn 
    WHERE m.staff_ssn = ? AND m.status = 'Scheduled'
    ORDER BY m.administration_date ASC
    LIMIT 10
", [$_SESSION['user_id']]) ?: [];

// Get recent patients with medications
$recent_patients = getRows("
    SELECT DISTINCT p.ssn, CONCAT(p.first_name, ' ', p.last_name) as patient_name, 
           p.phone, p.date_of_birth, p.gender, p.blood_type, p.allergies,
           MAX(m.administration_date) as last_medication,
           COUNT(m.id) as total_medications
    FROM patient p 
    JOIN medical_administration m ON p.ssn = m.patient_ssn 
    WHERE m.staff_ssn = ?
    GROUP BY p.ssn
    ORDER BY last_medication DESC
    LIMIT 8
", [$_SESSION['user_id']]) ?: [];

// Get staff statistics
$staff_stats = [
    'total_medications' => getRowCount("SELECT COUNT(*) FROM medical_administration WHERE staff_ssn = ?", [$_SESSION['user_id']]),
    'today_medications' => count($todays_medications),
    'pending_medications' => count($pending_medications),
    'total_patients' => getRowCount("SELECT COUNT(DISTINCT patient_ssn) FROM medical_administration WHERE staff_ssn = ?", [$_SESSION['user_id']]),
    'medications_this_month' => getRowCount("
        SELECT COUNT(*) FROM medical_administration 
        WHERE staff_ssn = ? AND YEAR(administration_date) = YEAR(CURDATE()) 
        AND MONTH(administration_date) = MONTH(CURDATE())
    ", [$_SESSION['user_id']])
];

// Get allergy alerts
$allergy_alerts = getRows("
    SELECT DISTINCT p.ssn, CONCAT(p.first_name, ' ', p.last_name) as patient_name, 
           p.allergies, COUNT(m.id) as medication_count
    FROM patient p 
    JOIN medical_administration m ON p.ssn = m.patient_ssn 
    WHERE m.staff_ssn = ? AND p.allergies IS NOT NULL AND p.allergies != ''
    AND m.administration_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAYS)
    GROUP BY p.ssn
    LIMIT 5
", [$_SESSION['user_id']]) ?: [];

$flash = get_flash_message();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Dashboard - Goba Hospital</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <nav class="dashboard-nav staff-nav">
        <div class="nav-brand">
            <i class="fas fa-hospital"></i>
            <span>Goba Hospital</span>
        </div>
        <div class="nav-user">
            <span><?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
            <div class="dropdown">
                <button class="dropdown-toggle">
                    <i class="fas fa-user-nurse"></i>
                    <i class="fas fa-chevron-down"></i>
                </button>
                <div class="dropdown-menu">
                    <a href="profile.php"><i class="fas fa-user"></i> My Profile</a>
                    <a href="schedule.php"><i class="fas fa-calendar"></i> My Schedule</a>
                    <a href="settings.php"><i class="fas fa-cog"></i> Settings</a>
                    <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar staff-sidebar">
            <ul class="sidebar-menu">
                <li class="active">
                    <a href="dashboard.php">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="medications.php">
                        <i class="fas fa-pills"></i>
                        <span>Medications</span>
                    </a>
                </li>
                <li>
                    <a href="patients.php">
                        <i class="fas fa-users"></i>
                        <span>My Patients</span>
                    </a>
                </li>
                <li>
                    <a href="vitals.php">
                        <i class="fas fa-heartbeat"></i>
                        <span>Vital Signs</span>
                    </a>
                </li>
                <li>
                    <a href="records.php">
                        <i class="fas fa-notes-medical"></i>
                        <span>Medical Records</span>
                    </a>
                </li>
                <li>
                    <a href="schedule.php">
                        <i class="fas fa-calendar-alt"></i>
                        <span>Schedule</span>
                    </a>
                </li>
                <li>
                    <a href="reports.php">
                        <i class="fas fa-chart-line"></i>
                        <span>Reports</span>
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
                    <h1><i class="fas fa-tachometer-alt"></i> Dashboard</h1>
                    <p>Welcome back, <?php echo htmlspecialchars($staff_info['first_name']); ?>! Here's your medication schedule for today.</p>
                </div>
                <div class="header-actions">
                    <button class="btn btn-primary" onclick="location.href='medications.php?action=new'">
                        <i class="fas fa-plus"></i> Record Medication
                    </button>
                    <button class="btn btn-secondary" onclick="location.href='vitals.php?action=new'">
                        <i class="fas fa-heartbeat"></i> Record Vitals
                    </button>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="stats-grid">
                <div class="stat-card medications">
                    <div class="stat-icon">
                        <i class="fas fa-pills"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $staff_stats['total_medications']; ?></h3>
                        <p>Total Medications</p>
                        <span class="stat-change">
                            <i class="fas fa-calendar"></i>
                            <?php echo $staff_stats['medications_this_month']; ?> this month
                        </span>
                    </div>
                </div>

                <div class="stat-card today">
                    <div class="stat-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $staff_stats['today_medications']; ?></h3>
                        <p>Today's Schedule</p>
                        <span class="stat-change">
                            <i class="fas fa-hourglass-half"></i>
                            <?php echo $staff_stats['pending_medications']; ?> pending
                        </span>
                    </div>
                </div>

                <div class="stat-card patients">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $staff_stats['total_patients']; ?></h3>
                        <p>My Patients</p>
                        <span class="stat-change">
                            <i class="fas fa-user-friends"></i>
                            Active cases
                        </span>
                    </div>
                </div>

                <div class="stat-card alerts">
                    <div class="stat-icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo count($allergy_alerts); ?></h3>
                        <p>Allergy Alerts</p>
                        <span class="stat-change alert">
                            <i class="fas fa-shield-alt"></i>
                            Require attention
                        </span>
                    </div>
                </div>
            </div>

            <!-- Main Dashboard Content -->
            <div class="dashboard-grid">
                <!-- Today's Medication Schedule -->
                <div class="dashboard-card schedule-card">
                    <div class="card-header">
                        <h3><i class="fas fa-calendar-day"></i> Today's Medication Schedule</h3>
                        <span class="card-badge"><?php echo count($todays_medications); ?> medications</span>
                    </div>
                    <div class="card-content">
                        <?php if (!empty($todays_medications)): ?>
                            <div class="medication-schedule">
                                <?php foreach (array_slice($todays_medications, 0, 6) as $medication): ?>
                                    <div class="medication-item">
                                        <div class="medication-time">
                                            <?php echo date('H:i', strtotime($medication['administration_date'])); ?>
                                        </div>
                                        <div class="medication-info">
                                            <h4><?php echo htmlspecialchars($medication['medication_name']); ?></h4>
                                            <p class="patient-name"><?php echo htmlspecialchars($medication['patient_name']); ?></p>
                                            <p class="dosage"><?php echo htmlspecialchars($medication['dosage']); ?></p>
                                            <?php if ($medication['allergies']): ?>
                                                <div class="allergy-warning">
                                                    <i class="fas fa-exclamation-triangle"></i>
                                                    <span>Allergies: <?php echo htmlspecialchars(substr($medication['allergies'], 0, 30)); ?>...</span>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="medication-actions">
                                            <button class="btn btn-sm btn-success" onclick="administerMedication('<?php echo $medication['id']; ?>')">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button class="btn btn-sm btn-info" onclick="viewMedication('<?php echo $medication['id']; ?>')">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <?php if (count($todays_medications) > 6): ?>
                                <div class="card-footer">
                                    <a href="medications.php?date=today" class="view-all-link">
                                        View all <?php echo count($todays_medications); ?> medications
                                    </a>
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="fas fa-pills"></i>
                                <h4>No medications scheduled today</h4>
                                <p>Your medication schedule is clear for today.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Recent Patients -->
                <div class="dashboard-card patients-card">
                    <div class="card-header">
                        <h3><i class="fas fa-users"></i> Recent Patients</h3>
                        <a href="patients.php" class="card-action">View All</a>
                    </div>
                    <div class="card-content">
                        <?php if (!empty($recent_patients)): ?>
                            <div class="patients-list">
                                <?php foreach ($recent_patients as $patient): ?>
                                    <div class="patient-item">
                                        <div class="patient-avatar">
                                            <i class="fas fa-user"></i>
                                        </div>
                                        <div class="patient-info">
                                            <h4><?php echo htmlspecialchars($patient['patient_name']); ?></h4>
                                            <p>
                                                <?php echo calculate_age($patient['date_of_birth']); ?> years, 
                                                <?php echo htmlspecialchars($patient['gender']); ?>
                                                <?php if ($patient['blood_type']): ?>
                                                    • <?php echo htmlspecialchars($patient['blood_type']); ?>
                                                <?php endif; ?>
                                            </p>
                                            <small>Last medication: <?php echo format_date($patient['last_medication']); ?></small>
                                            <div class="medication-count">
                                                <i class="fas fa-pills"></i>
                                                <?php echo $patient['total_medications']; ?> medications
                                            </div>
                                        </div>
                                        <div class="patient-actions">
                                            <button class="btn btn-sm btn-info" onclick="viewPatient('<?php echo $patient['ssn']; ?>')">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-sm btn-primary" onclick="newMedication('<?php echo $patient['ssn']; ?>')">
                                                <i class="fas fa-pills"></i>
                                            </button>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="fas fa-user-friends"></i>
                                <h4>No recent patients</h4>
                                <p>Start by recording your first medication administration.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Pending Medications -->
                <div class="dashboard-card pending-card">
                    <div class="card-header">
                        <h3><i class="fas fa-hourglass-half"></i> Pending Medications</h3>
                        <a href="medications.php?status=scheduled" class="card-action">View All</a>
                    </div>
                    <div class="card-content">
                        <?php if (!empty($pending_medications)): ?>
                            <div class="pending-list">
                                <?php foreach (array_slice($pending_medications, 0, 5) as $medication): ?>
                                    <div class="pending-item">
                                        <div class="pending-date">
                                            <div class="date-day"><?php echo date('d', strtotime($medication['administration_date'])); ?></div>
                                            <div class="date-month"><?php echo date('M', strtotime($medication['administration_date'])); ?></div>
                                        </div>
                                        <div class="pending-info">
                                            <h4><?php echo htmlspecialchars($medication['medication_name']); ?></h4>
                                            <p><?php echo htmlspecialchars($medication['patient_name']); ?></p>
                                            <small><?php echo date('H:i', strtotime($medication['administration_date'])); ?> • <?php echo htmlspecialchars($medication['dosage']); ?></small>
                                        </div>
                                        <div class="pending-priority">
                                            <span class="priority-badge normal">Normal</span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="fas fa-check-circle"></i>
                                <h4>No pending medications</h4>
                                <p>All medications are up to date.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Allergy Alerts -->
                <div class="dashboard-card alerts-card">
                    <div class="card-header">
                        <h3><i class="fas fa-exclamation-triangle"></i> Allergy Alerts</h3>
                        <a href="patients.php?allergies=true" class="card-action">View All</a>
                    </div>
                    <div class="card-content">
                        <?php if (!empty($allergy_alerts)): ?>
                            <div class="alerts-list">
                                <?php foreach ($allergy_alerts as $alert): ?>
                                    <div class="alert-item">
                                        <div class="alert-icon">
                                            <i class="fas fa-exclamation-triangle"></i>
                                        </div>
                                        <div class="alert-info">
                                            <h4><?php echo htmlspecialchars($alert['patient_name']); ?></h4>
                                            <p class="allergies"><?php echo htmlspecialchars($alert['allergies']); ?></p>
                                            <small><?php echo $alert['medication_count']; ?> recent medications</small>
                                        </div>
                                        <div class="alert-actions">
                                            <button class="btn btn-sm btn-warning" onclick="viewPatient('<?php echo $alert['ssn']; ?>')">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="fas fa-shield-alt"></i>
                                <h4>No active alerts</h4>
                                <p>All patients are safe from known allergies.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="dashboard-card actions-card">
                    <div class="card-header">
                        <h3><i class="fas fa-bolt"></i> Quick Actions</h3>
                    </div>
                    <div class="card-content">
                        <div class="quick-actions">
                            <button class="action-btn medication" onclick="location.href='medications.php?action=new'">
                                <i class="fas fa-pills"></i>
                                <span>Record Medication</span>
                            </button>
                            
                            <button class="action-btn vitals" onclick="location.href='vitals.php?action=new'">
                                <i class="fas fa-heartbeat"></i>
                                <span>Record Vitals</span>
                            </button>
                            
                            <button class="action-btn patient" onclick="openPatientSearch()">
                                <i class="fas fa-search"></i>
                                <span>Find Patient</span>
                            </button>
                            
                            <button class="action-btn schedule" onclick="location.href='schedule.php'">
                                <i class="fas fa-calendar"></i>
                                <span>View Schedule</span>
                            </button>
                            
                            <button class="action-btn reports" onclick="location.href='reports.php'">
                                <i class="fas fa-chart-line"></i>
                                <span>Generate Report</span>
                            </button>
                            
                            <button class="action-btn emergency" onclick="openEmergencyProtocol()">
                                <i class="fas fa-phone-alt"></i>
                                <span>Emergency</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Staff Information -->
                <div class="dashboard-card staff-info-card">
                    <div class="card-header">
                        <h3><i class="fas fa-user-nurse"></i> Staff Information</h3>
                        <a href="profile.php" class="card-action">Edit Profile</a>
                    </div>
                    <div class="card-content">
                        <div class="staff-profile">
                            <div class="staff-avatar">
                                <i class="fas fa-user-nurse"></i>
                            </div>
                            <div class="staff-details">
                                <h4><?php echo htmlspecialchars($staff_info['first_name'] . ' ' . $staff_info['last_name']); ?></h4>
                                <p class="department"><?php echo htmlspecialchars($staff_info['department'] ?? 'General Nursing'); ?></p>
                                <p class="shift">Shift: <?php echo htmlspecialchars($staff_info['shift'] ?? 'Day Shift'); ?></p>
                                <div class="contact-info">
                                    <p><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($staff_info['email'] ?? 'No email'); ?></p>
                                    <p><i class="fas fa-phone"></i> <?php echo htmlspecialchars($staff_info['phone'] ?? 'No phone'); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <style>
        .staff-nav {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        }

        .staff-sidebar {
            border-right: 3px solid #f59e0b;
        }

        .staff-sidebar .sidebar-menu li.active a,
        .staff-sidebar .sidebar-menu li:hover a {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
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

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            gap: 1.5rem;
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

        .stat-card.medications::before { background: #f59e0b; }
        .stat-card.today::before { background: #10b981; }
        .stat-card.patients::before { background: #3b82f6; }
        .stat-card.alerts::before { background: #ef4444; }

        .stat-icon {
            width: 80px;
            height: 80px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: white;
        }

        .medications .stat-icon { background: #f59e0b; }
        .today .stat-icon { background: #10b981; }
        .patients .stat-icon { background: #3b82f6; }
        .alerts .stat-icon { background: #ef4444; }

        .stat-info h3 {
            font-size: 2.5rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 0.5rem;
        }

        .stat-info p {
            color: #64748b;
            font-weight: 500;
            margin-bottom: 0.5rem;
        }

        .stat-change {
            font-size: 0.875rem;
            display: flex;
            align-items: center;
            gap: 0.25rem;
            color: #059669;
        }

        .stat-change.alert {
            color: #dc2626;
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
            padding: 1.5rem 2rem;
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
            background: #fef3c7;
            color: #92400e;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .card-action {
            color: #f59e0b;
            text-decoration: none;
            font-size: 0.875rem;
            font-weight: 500;
        }

        .card-content {
            padding: 2rem;
        }

        .medication-schedule, .patients-list, .pending-list, .alerts-list {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .medication-item, .patient-item, .pending-item, .alert-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            border: 1px solid #f3f4f6;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .medication-item:hover, .patient-item:hover, .pending-item:hover, .alert-item:hover {
            border-color: #f59e0b;
            box-shadow: 0 2px 8px rgba(245, 158, 11, 0.1);
        }

        .medication-time {
            background: #fef3c7;
            color: #92400e;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-weight: 600;
            min-width: 60px;
            text-align: center;
        }

        .patient-avatar, .staff-avatar {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.25rem;
        }

        .pending-date {
            background: #dbeafe;
            color: #1d4ed8;
            padding: 0.5rem;
            border-radius: 8px;
            text-align: center;
            min-width: 60px;
        }

        .date-day {
            font-size: 1.25rem;
            font-weight: 700;
        }

        .date-month {
            font-size: 0.75rem;
            text-transform: uppercase;
        }

        .alert-icon {
            width: 40px;
            height: 40px;
            background: #fef2f2;
            color: #dc2626;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
        }

        .allergy-warning {
            background: #fef3c7;
            color: #92400e;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.75rem;
            display: flex;
            align-items: center;
            gap: 0.25rem;
            margin-top: 0.25rem;
        }

        .priority-badge {
            padding: 0.25rem 0.5rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .priority-badge.normal { background: #ecfdf5; color: #059669; }
        .priority-badge.urgent { background: #fef2f2; color: #dc2626; }

        .medication-count {
            color: #f59e0b;
            font-size: 0.875rem;
            display: flex;
            align-items: center;
            gap: 0.25rem;
            margin-top: 0.25rem;
        }

        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
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
            border-color: #f59e0b;
            color: #f59e0b;
            box-shadow: 0 4px 15px rgba(245, 158, 11, 0.1);
        }

        .action-btn.emergency:hover {
            border-color: #ef4444;
            color: #ef4444;
            box-shadow: 0 4px 15px rgba(239, 68, 68, 0.1);
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

        .staff-profile {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .contact-info p {
            margin: 0.25rem 0;
            font-size: 0.875rem;
            color: #6b7280;
        }

        .contact-info i {
            width: 16px;
            color: #f59e0b;
        }

        .empty-state {
            text-align: center;
            padding: 2rem 1rem;
            color: #6b7280;
        }

        .empty-state i {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            color: #d1d5db;
        }

        .empty-state h4 {
            margin-bottom: 0.5rem;
            color: #374151;
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

            .staff-profile {
                flex-direction: column;
                text-align: center;
            }
        }
    </style>

    <script>
        function administerMedication(medicationId) {
            if (confirm('Mark this medication as administered?')) {
                // In a real application, this would make an AJAX call
                window.location.href = `medications.php?action=administer&id=${medicationId}`;
            }
        }

        function viewMedication(medicationId) {
            window.location.href = `medications.php?view=${medicationId}`;
        }

        function viewPatient(patientId) {
            window.location.href = `patients.php?view=${patientId}`;
        }

        function newMedication(patientId) {
            window.location.href = `medications.php?action=new&patient=${patientId}`;
        }

        function openPatientSearch() {
            window.location.href = `patients.php`;
        }

        function openEmergencyProtocol() {
            alert('Emergency protocols:\n\n• Call 911 for life-threatening emergencies\n• Contact attending physician\n• Notify nursing supervisor\n• Document all actions taken');
        }

        // Real-time clock and medication reminders
        function updateClock() {
            const now = new Date();
            const timeString = now.toLocaleTimeString();
            document.title = `Staff Dashboard - ${timeString} - Goba Hospital`;
        }

        setInterval(updateClock, 1000);
        updateClock();

        // Medication reminder notifications
        function checkMedicationReminders() {
            const now = new Date();
            const currentTime = now.getHours() * 60 + now.getMinutes();
            
            // This would check for medications due soon and show notifications
            // Implementation would depend on the specific requirements
        }

        setInterval(checkMedicationReminders, 60000); // Check every minute
    </script>

    <script src="../assets/js/script.js"></script>
</body>
</html>