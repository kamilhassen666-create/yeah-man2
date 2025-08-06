<?php
require_once '../includes/functions.php';
require_login(['doctor']);

$doctor_info = get_user_info($_SESSION['user_id'], 'doctor');
$doctor_stats = get_doctor_statistics($_SESSION['user_id']);

// Get today's appointments/consultations
$today = date('Y-m-d');
$todays_consultations = getRows("
    SELECT c.*, CONCAT(p.first_name, ' ', p.last_name) as patient_name, p.phone, p.date_of_birth
    FROM consultation c 
    JOIN patient p ON c.patient_ssn = p.ssn 
    WHERE c.doctor_ssn = ? AND DATE(c.consultation_date) = ?
    ORDER BY c.consultation_date ASC
", [$_SESSION['user_id'], $today]) ?: [];

// Get recent patients
$recent_patients = getRows("
    SELECT DISTINCT p.ssn, CONCAT(p.first_name, ' ', p.last_name) as patient_name, 
           p.phone, p.date_of_birth, p.gender, p.blood_type,
           MAX(c.consultation_date) as last_visit
    FROM patient p 
    JOIN consultation c ON p.ssn = c.patient_ssn 
    WHERE c.doctor_ssn = ?
    GROUP BY p.ssn
    ORDER BY last_visit DESC
    LIMIT 8
", [$_SESSION['user_id']]) ?: [];

// Get pending operations
$pending_operations = getRows("
    SELECT o.*, CONCAT(p.first_name, ' ', p.last_name) as patient_name
    FROM operation o 
    JOIN patient p ON o.patient_ssn = p.ssn 
    WHERE o.doctor_ssn = ? AND o.status = 'Scheduled'
    ORDER BY o.operation_date ASC
    LIMIT 5
", [$_SESSION['user_id']]) ?: [];

// Get urgent diagnoses
$urgent_diagnoses = getRows("
    SELECT d.*, CONCAT(p.first_name, ' ', p.last_name) as patient_name
    FROM diagnosis d 
    JOIN patient p ON d.patient_ssn = p.ssn 
    WHERE d.doctor_ssn = ? AND d.severity IN ('Severe', 'Critical')
    ORDER BY d.diagnosis_date DESC
    LIMIT 5
", [$_SESSION['user_id']]) ?: [];

$flash = get_flash_message();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Dashboard - Goba Hospital</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <nav class="dashboard-nav doctor-nav">
        <div class="nav-brand">
            <i class="fas fa-hospital"></i>
            <span>Goba Hospital</span>
        </div>
        <div class="nav-user">
            <span>Dr. <?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
            <div class="dropdown">
                <button class="dropdown-toggle">
                    <i class="fas fa-user-md"></i>
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
        <aside class="sidebar doctor-sidebar">
            <ul class="sidebar-menu">
                <li class="active">
                    <a href="dashboard.php">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="patients.php">
                        <i class="fas fa-users"></i>
                        <span>My Patients</span>
                    </a>
                </li>
                <li>
                    <a href="consultations.php">
                        <i class="fas fa-stethoscope"></i>
                        <span>Consultations</span>
                    </a>
                </li>
                <li>
                    <a href="operations.php">
                        <i class="fas fa-procedures"></i>
                        <span>Operations</span>
                    </a>
                </li>
                <li>
                    <a href="diagnoses.php">
                        <i class="fas fa-diagnoses"></i>
                        <span>Diagnoses</span>
                    </a>
                </li>
                <li>
                    <a href="records.php">
                        <i class="fas fa-notes-medical"></i>
                        <span>Medical Records</span>
                    </a>
                </li>
                <li>
                    <a href="search.php">
                        <i class="fas fa-search"></i>
                        <span>Search Patients</span>
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
                    <p>Welcome back, Dr. <?php echo htmlspecialchars($doctor_info['first_name']); ?>! Here's what's happening today.</p>
                </div>
                <div class="header-actions">
                    <button class="btn btn-primary" onclick="location.href='consultations.php?action=new'">
                        <i class="fas fa-plus"></i> New Consultation
                    </button>
                    <button class="btn btn-secondary" onclick="location.href='search.php'">
                        <i class="fas fa-search"></i> Find Patient
                    </button>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="stats-grid">
                <div class="stat-card patients">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $doctor_stats['total_patients'] ?? 0; ?></h3>
                        <p>Total Patients</p>
                        <span class="stat-change">
                            <i class="fas fa-arrow-up"></i>
                            +<?php echo $doctor_stats['new_patients_this_month'] ?? 0; ?> this month
                        </span>
                    </div>
                </div>

                <div class="stat-card consultations">
                    <div class="stat-icon">
                        <i class="fas fa-stethoscope"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $doctor_stats['total_consultations'] ?? 0; ?></h3>
                        <p>Consultations</p>
                        <span class="stat-change">
                            <i class="fas fa-calendar"></i>
                            <?php echo count($todays_consultations); ?> today
                        </span>
                    </div>
                </div>

                <div class="stat-card operations">
                    <div class="stat-icon">
                        <i class="fas fa-procedures"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $doctor_stats['total_operations'] ?? 0; ?></h3>
                        <p>Operations</p>
                        <span class="stat-change">
                            <i class="fas fa-clock"></i>
                            <?php echo count($pending_operations); ?> pending
                        </span>
                    </div>
                </div>

                <div class="stat-card diagnoses">
                    <div class="stat-icon">
                        <i class="fas fa-diagnoses"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $doctor_stats['total_diagnoses'] ?? 0; ?></h3>
                        <p>Diagnoses</p>
                        <span class="stat-change urgent">
                            <i class="fas fa-exclamation-triangle"></i>
                            <?php echo count($urgent_diagnoses); ?> urgent
                        </span>
                    </div>
                </div>
            </div>

            <!-- Main Dashboard Content -->
            <div class="dashboard-grid">
                <!-- Today's Schedule -->
                <div class="dashboard-card schedule-card">
                    <div class="card-header">
                        <h3><i class="fas fa-calendar-day"></i> Today's Schedule</h3>
                        <span class="card-badge"><?php echo count($todays_consultations); ?> appointments</span>
                    </div>
                    <div class="card-content">
                        <?php if (!empty($todays_consultations)): ?>
                            <div class="schedule-list">
                                <?php foreach (array_slice($todays_consultations, 0, 5) as $consultation): ?>
                                    <div class="schedule-item">
                                        <div class="schedule-time">
                                            <?php echo date('H:i', strtotime($consultation['consultation_date'])); ?>
                                        </div>
                                        <div class="schedule-info">
                                            <h4><?php echo htmlspecialchars($consultation['patient_name']); ?></h4>
                                            <p><?php echo htmlspecialchars($consultation['symptoms'] ?: 'Regular checkup'); ?></p>
                                            <small>
                                                <i class="fas fa-phone"></i> <?php echo htmlspecialchars($consultation['phone'] ?: 'No phone'); ?>
                                            </small>
                                        </div>
                                        <div class="schedule-actions">
                                            <button class="btn btn-sm btn-primary" onclick="viewConsultation('<?php echo $consultation['id']; ?>')">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <?php if (count($todays_consultations) > 5): ?>
                                <div class="card-footer">
                                    <a href="consultations.php?date=today" class="view-all-link">
                                        View all <?php echo count($todays_consultations); ?> appointments
                                    </a>
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="fas fa-calendar-check"></i>
                                <h4>No appointments today</h4>
                                <p>Your schedule is clear for today.</p>
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
                                            <small>Last visit: <?php echo format_date($patient['last_visit']); ?></small>
                                        </div>
                                        <div class="patient-actions">
                                            <button class="btn btn-sm btn-info" onclick="viewPatient('<?php echo $patient['ssn']; ?>')">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-sm btn-primary" onclick="newConsultation('<?php echo $patient['ssn']; ?>')">
                                                <i class="fas fa-stethoscope"></i>
                                            </button>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="fas fa-user-friends"></i>
                                <h4>No recent patients</h4>
                                <p>Start by adding your first consultation.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Pending Operations -->
                <div class="dashboard-card operations-card">
                    <div class="card-header">
                        <h3><i class="fas fa-procedures"></i> Pending Operations</h3>
                        <a href="operations.php?status=scheduled" class="card-action">View All</a>
                    </div>
                    <div class="card-content">
                        <?php if (!empty($pending_operations)): ?>
                            <div class="operations-list">
                                <?php foreach ($pending_operations as $operation): ?>
                                    <div class="operation-item">
                                        <div class="operation-date">
                                            <div class="date-day"><?php echo date('d', strtotime($operation['operation_date'])); ?></div>
                                            <div class="date-month"><?php echo date('M', strtotime($operation['operation_date'])); ?></div>
                                        </div>
                                        <div class="operation-info">
                                            <h4><?php echo htmlspecialchars($operation['operation_type']); ?></h4>
                                            <p><?php echo htmlspecialchars($operation['patient_name']); ?></p>
                                            <small><?php echo date('H:i', strtotime($operation['operation_date'])); ?></small>
                                        </div>
                                        <div class="operation-status">
                                            <span class="status scheduled">Scheduled</span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="fas fa-procedures"></i>
                                <h4>No pending operations</h4>
                                <p>All operations are up to date.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Urgent Cases -->
                <div class="dashboard-card urgent-card">
                    <div class="card-header">
                        <h3><i class="fas fa-exclamation-triangle"></i> Urgent Cases</h3>
                        <a href="diagnoses.php?severity=urgent" class="card-action">View All</a>
                    </div>
                    <div class="card-content">
                        <?php if (!empty($urgent_diagnoses)): ?>
                            <div class="urgent-list">
                                <?php foreach ($urgent_diagnoses as $diagnosis): ?>
                                    <div class="urgent-item">
                                        <div class="urgent-severity">
                                            <span class="severity <?php echo strtolower($diagnosis['severity']); ?>">
                                                <?php echo $diagnosis['severity']; ?>
                                            </span>
                                        </div>
                                        <div class="urgent-info">
                                            <h4><?php echo htmlspecialchars($diagnosis['diagnosis_name']); ?></h4>
                                            <p><?php echo htmlspecialchars($diagnosis['patient_name']); ?></p>
                                            <small><?php echo format_date($diagnosis['diagnosis_date']); ?></small>
                                        </div>
                                        <div class="urgent-actions">
                                            <button class="btn btn-sm btn-warning" onclick="followUpDiagnosis('<?php echo $diagnosis['id']; ?>')">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="fas fa-check-circle"></i>
                                <h4>No urgent cases</h4>
                                <p>All patients are stable.</p>
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
                            <button class="action-btn consultation" onclick="location.href='consultations.php?action=new'">
                                <i class="fas fa-stethoscope"></i>
                                <span>New Consultation</span>
                            </button>
                            
                            <button class="action-btn operation" onclick="location.href='operations.php?action=new'">
                                <i class="fas fa-procedures"></i>
                                <span>Schedule Operation</span>
                            </button>
                            
                            <button class="action-btn diagnosis" onclick="location.href='diagnoses.php?action=new'">
                                <i class="fas fa-diagnoses"></i>
                                <span>Add Diagnosis</span>
                            </button>
                            
                            <button class="action-btn search" onclick="location.href='search.php'">
                                <i class="fas fa-search"></i>
                                <span>Find Patient</span>
                            </button>
                            
                            <button class="action-btn reports" onclick="location.href='reports.php'">
                                <i class="fas fa-chart-line"></i>
                                <span>Generate Report</span>
                            </button>
                            
                            <button class="action-btn upload" onclick="openUploadModal()">
                                <i class="fas fa-upload"></i>
                                <span>Upload Files</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Doctor Information -->
                <div class="dashboard-card doctor-info-card">
                    <div class="card-header">
                        <h3><i class="fas fa-user-md"></i> Doctor Information</h3>
                        <a href="profile.php" class="card-action">Edit Profile</a>
                    </div>
                    <div class="card-content">
                        <div class="doctor-profile">
                            <div class="doctor-avatar">
                                <i class="fas fa-user-md"></i>
                            </div>
                            <div class="doctor-details">
                                <h4>Dr. <?php echo htmlspecialchars($doctor_info['first_name'] . ' ' . $doctor_info['last_name']); ?></h4>
                                <p class="specialization"><?php echo htmlspecialchars($doctor_info['specialization'] ?? 'General Practice'); ?></p>
                                <p class="license">License: <?php echo htmlspecialchars($doctor_info['license_number'] ?? 'Not specified'); ?></p>
                                <div class="contact-info">
                                    <p><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($doctor_info['email'] ?? 'No email'); ?></p>
                                    <p><i class="fas fa-phone"></i> <?php echo htmlspecialchars($doctor_info['phone'] ?? 'No phone'); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <style>
        .doctor-nav {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }

        .doctor-sidebar {
            border-right: 3px solid #10b981;
        }

        .doctor-sidebar .sidebar-menu li.active a,
        .doctor-sidebar .sidebar-menu li:hover a {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
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

        .stat-card.patients::before { background: #3b82f6; }
        .stat-card.consultations::before { background: #10b981; }
        .stat-card.operations::before { background: #ef4444; }
        .stat-card.diagnoses::before { background: #f59e0b; }

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

        .patients .stat-icon { background: #3b82f6; }
        .consultations .stat-icon { background: #10b981; }
        .operations .stat-icon { background: #ef4444; }
        .diagnoses .stat-icon { background: #f59e0b; }

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

        .stat-change.urgent {
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
            background: #dbeafe;
            color: #1d4ed8;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .card-action {
            color: #2563eb;
            text-decoration: none;
            font-size: 0.875rem;
            font-weight: 500;
        }

        .card-content {
            padding: 2rem;
        }

        .schedule-list, .patients-list, .operations-list, .urgent-list {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .schedule-item, .patient-item, .operation-item, .urgent-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            border: 1px solid #f3f4f6;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .schedule-item:hover, .patient-item:hover, .operation-item:hover, .urgent-item:hover {
            border-color: #2563eb;
            box-shadow: 0 2px 8px rgba(37, 99, 235, 0.1);
        }

        .schedule-time {
            background: #dbeafe;
            color: #1d4ed8;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-weight: 600;
            min-width: 60px;
            text-align: center;
        }

        .patient-avatar, .doctor-avatar {
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

        .operation-date {
            background: #fef3c7;
            color: #d97706;
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

        .urgent-severity .severity {
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .severity.severe { background: #fee2e2; color: #dc2626; }
        .severity.critical { background: #fef2f2; color: #991b1b; }

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
            border-color: #2563eb;
            color: #2563eb;
            box-shadow: 0 4px 15px rgba(37, 99, 235, 0.1);
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

        .doctor-profile {
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
            color: #2563eb;
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

            .doctor-profile {
                flex-direction: column;
                text-align: center;
            }
        }
    </style>

    <script>
        function viewConsultation(consultationId) {
            window.location.href = `consultations.php?view=${consultationId}`;
        }

        function viewPatient(patientId) {
            window.location.href = `patients.php?view=${patientId}`;
        }

        function newConsultation(patientId) {
            window.location.href = `consultations.php?action=new&patient=${patientId}`;
        }

        function followUpDiagnosis(diagnosisId) {
            window.location.href = `diagnoses.php?view=${diagnosisId}`;
        }

        function openUploadModal() {
            // This would open a file upload modal
            alert('File upload functionality would be implemented here.');
        }

        // Real-time clock
        function updateClock() {
            const now = new Date();
            const timeString = now.toLocaleTimeString();
            const dateString = now.toLocaleDateString();
            
            document.title = `Dashboard - ${timeString} - Goba Hospital`;
        }

        setInterval(updateClock, 1000);
        updateClock();
    </script>

    <script src="../assets/js/script.js"></script>
</body>
</html>