<?php
require_once '../includes/functions.php';
require_login(['patient']);

$patient_info = get_user_info($_SESSION['user_id'], 'patient');
$patient_stats = get_patient_statistics($_SESSION['user_id']);

// Get recent records
$recent_consultations = search_records($_SESSION['user_id'], 'consultation');
$recent_consultations = array_slice($recent_consultations ?: [], 0, 5);

$recent_operations = search_records($_SESSION['user_id'], 'operation');
$recent_operations = array_slice($recent_operations ?: [], 0, 5);

$recent_diagnoses = search_records($_SESSION['user_id'], 'diagnosis');
$recent_diagnoses = array_slice($recent_diagnoses ?: [], 0, 5);

$flash = get_flash_message();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Dashboard - Goba Hospital</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <nav class="dashboard-nav">
        <div class="nav-brand">
            <i class="fas fa-hospital"></i>
            <span>Goba Hospital</span>
        </div>
        <div class="nav-user">
            <span>Welcome, <?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
            <div class="dropdown">
                <button class="dropdown-toggle">
                    <i class="fas fa-user-circle"></i>
                    <i class="fas fa-chevron-down"></i>
                </button>
                <div class="dropdown-menu">
                    <a href="profile.php"><i class="fas fa-user"></i> Profile</a>
                    <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <ul class="sidebar-menu">
                <li class="active">
                    <a href="dashboard.php">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="profile.php">
                        <i class="fas fa-user"></i>
                        <span>My Profile</span>
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
                        <span>Search Records</span>
                    </a>
                </li>
                <li>
                    <a href="payments.php">
                        <i class="fas fa-credit-card"></i>
                        <span>Payments</span>
                    </a>
                </li>
                <li>
                    <a href="appointments.php">
                        <i class="fas fa-calendar-check"></i>
                        <span>Appointments</span>
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

            <div class="dashboard-header">
                <h1>Dashboard</h1>
                <p>Welcome back, <?php echo htmlspecialchars($patient_info['first_name']); ?>! Here's your medical information overview.</p>
            </div>

            <!-- Statistics Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon consultation">
                        <i class="fas fa-stethoscope"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $patient_stats['consultations']; ?></h3>
                        <p>Total Consultations</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon operation">
                        <i class="fas fa-procedures"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $patient_stats['operations']; ?></h3>
                        <p>Surgical Procedures</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon diagnosis">
                        <i class="fas fa-diagnoses"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $patient_stats['diagnoses']; ?></h3>
                        <p>Diagnoses</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon medication">
                        <i class="fas fa-pills"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $patient_stats['medications']; ?></h3>
                        <p>Medications</p>
                    </div>
                </div>
            </div>

            <!-- Patient Info Summary -->
            <div class="info-summary">
                <h2>Personal Information</h2>
                <div class="info-grid">
                    <div class="info-item">
                        <label>Patient ID:</label>
                        <span><?php echo htmlspecialchars($patient_info['ssn']); ?></span>
                    </div>
                    <div class="info-item">
                        <label>Age:</label>
                        <span><?php echo calculate_age($patient_info['date_of_birth']); ?> years</span>
                    </div>
                    <div class="info-item">
                        <label>Gender:</label>
                        <span><?php echo htmlspecialchars($patient_info['gender']); ?></span>
                    </div>
                    <div class="info-item">
                        <label>Blood Type:</label>
                        <span><?php echo htmlspecialchars($patient_info['blood_type'] ?: 'Not specified'); ?></span>
                    </div>
                    <div class="info-item">
                        <label>Email:</label>
                        <span><?php echo htmlspecialchars($patient_info['email'] ?: 'Not specified'); ?></span>
                    </div>
                    <div class="info-item">
                        <label>Phone:</label>
                        <span><?php echo htmlspecialchars($patient_info['phone'] ?: 'Not specified'); ?></span>
                    </div>
                </div>
            </div>

            <!-- Recent Activities -->
            <div class="recent-activities">
                <div class="activity-section">
                    <h3>Recent Consultations</h3>
                    <?php if (!empty($recent_consultations)): ?>
                        <div class="records-list">
                            <?php foreach ($recent_consultations as $consultation): ?>
                                <div class="record-item">
                                    <div class="record-info">
                                        <h4>Dr. <?php echo htmlspecialchars($consultation['doctor_name']); ?></h4>
                                        <p><?php echo htmlspecialchars($consultation['diagnosis'] ?: 'Consultation'); ?></p>
                                        <span class="record-date"><?php echo format_datetime($consultation['consultation_date']); ?></span>
                                    </div>
                                    <div class="record-status">
                                        <span class="status <?php echo strtolower($consultation['status']); ?>">
                                            <?php echo $consultation['status']; ?>
                                        </span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <a href="records.php?type=consultation" class="view-more">View All Consultations</a>
                    <?php else: ?>
                        <p class="no-records">No consultations found.</p>
                    <?php endif; ?>
                </div>

                <div class="activity-section">
                    <h3>Recent Diagnoses</h3>
                    <?php if (!empty($recent_diagnoses)): ?>
                        <div class="records-list">
                            <?php foreach ($recent_diagnoses as $diagnosis): ?>
                                <div class="record-item">
                                    <div class="record-info">
                                        <h4><?php echo htmlspecialchars($diagnosis['diagnosis_name']); ?></h4>
                                        <p>Dr. <?php echo htmlspecialchars($diagnosis['doctor_name']); ?></p>
                                        <span class="record-date"><?php echo format_datetime($diagnosis['diagnosis_date']); ?></span>
                                    </div>
                                    <div class="record-status">
                                        <span class="severity <?php echo strtolower($diagnosis['severity']); ?>">
                                            <?php echo $diagnosis['severity']; ?>
                                        </span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <a href="records.php?type=diagnosis" class="view-more">View All Diagnoses</a>
                    <?php else: ?>
                        <p class="no-records">No diagnoses found.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="quick-actions">
                <h3>Quick Actions</h3>
                <div class="actions-grid">
                    <a href="search.php" class="action-button">
                        <i class="fas fa-search"></i>
                        <span>Search Records</span>
                    </a>
                    <a href="payments.php" class="action-button">
                        <i class="fas fa-credit-card"></i>
                        <span>Make Payment</span>
                    </a>
                    <a href="profile.php" class="action-button">
                        <i class="fas fa-edit"></i>
                        <span>Update Profile</span>
                    </a>
                    <a href="records.php" class="action-button">
                        <i class="fas fa-download"></i>
                        <span>Download Records</span>
                    </a>
                </div>
            </div>
        </main>
    </div>

    <style>
        .dashboard-nav {
            background: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .nav-brand {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 1.5rem;
            font-weight: 700;
            color: #2563eb;
        }

        .nav-user {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .dropdown {
            position: relative;
        }

        .dropdown-toggle {
            background: none;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem;
            border-radius: 8px;
            transition: background-color 0.3s;
        }

        .dropdown-toggle:hover {
            background-color: #f3f4f6;
        }

        .dropdown-menu {
            position: absolute;
            top: 100%;
            right: 0;
            background: white;
            border-radius: 8px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            padding: 0.5rem 0;
            min-width: 150px;
            display: none;
        }

        .dropdown:hover .dropdown-menu {
            display: block;
        }

        .dropdown-menu a {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            text-decoration: none;
            color: #374151;
            transition: background-color 0.3s;
        }

        .dropdown-menu a:hover {
            background-color: #f3f4f6;
        }

        .dashboard-container {
            display: flex;
            min-height: calc(100vh - 80px);
        }

        .sidebar {
            width: 250px;
            background: white;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
            padding: 2rem 0;
        }

        .sidebar-menu {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .sidebar-menu li a {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem 2rem;
            text-decoration: none;
            color: #6b7280;
            transition: all 0.3s;
        }

        .sidebar-menu li.active a,
        .sidebar-menu li a:hover {
            background-color: #f3f4f6;
            color: #2563eb;
            border-right: 3px solid #2563eb;
        }

        .main-content {
            flex: 1;
            padding: 2rem;
            background-color: #f8fafc;
        }

        .dashboard-header h1 {
            font-size: 2rem;
            color: #1e293b;
            margin-bottom: 0.5rem;
        }

        .dashboard-header p {
            color: #64748b;
            margin-bottom: 2rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            gap: 1rem;
        }

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

        .stat-icon.consultation { background: #3b82f6; }
        .stat-icon.operation { background: #ef4444; }
        .stat-icon.diagnosis { background: #f59e0b; }
        .stat-icon.medication { background: #10b981; }

        .stat-info h3 {
            font-size: 2rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 0.25rem;
        }

        .stat-info p {
            color: #64748b;
            margin: 0;
        }

        .info-summary {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }

        .info-summary h2 {
            color: #1e293b;
            margin-bottom: 1.5rem;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
        }

        .info-item {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .info-item label {
            font-weight: 600;
            color: #374151;
            font-size: 0.875rem;
        }

        .info-item span {
            color: #1e293b;
        }

        .recent-activities {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .activity-section {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .activity-section h3 {
            color: #1e293b;
            margin-bottom: 1.5rem;
        }

        .records-list {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .record-item {
            padding: 1rem;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .record-info h4 {
            color: #1e293b;
            margin-bottom: 0.25rem;
        }

        .record-info p {
            color: #6b7280;
            margin-bottom: 0.25rem;
        }

        .record-date {
            font-size: 0.875rem;
            color: #9ca3af;
        }

        .status, .severity {
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status.completed { background: #dcfce7; color: #166534; }
        .status.scheduled { background: #dbeafe; color: #1d4ed8; }
        .status.cancelled { background: #fef2f2; color: #dc2626; }

        .severity.mild { background: #ecfdf5; color: #059669; }
        .severity.moderate { background: #fef3c7; color: #d97706; }
        .severity.severe { background: #fee2e2; color: #dc2626; }
        .severity.critical { background: #fef2f2; color: #991b1b; }

        .view-more {
            color: #2563eb;
            text-decoration: none;
            font-weight: 500;
        }

        .view-more:hover {
            text-decoration: underline;
        }

        .no-records {
            color: #9ca3af;
            text-align: center;
            padding: 2rem;
            font-style: italic;
        }

        .quick-actions {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .quick-actions h3 {
            color: #1e293b;
            margin-bottom: 1.5rem;
        }

        .actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }

        .action-button {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.5rem;
            padding: 1.5rem;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            text-decoration: none;
            color: #374151;
            transition: all 0.3s;
        }

        .action-button:hover {
            border-color: #2563eb;
            color: #2563eb;
            transform: translateY(-2px);
        }

        .action-button i {
            font-size: 2rem;
        }

        @media (max-width: 768px) {
            .dashboard-container {
                flex-direction: column;
            }
            
            .sidebar {
                width: 100%;
                order: 2;
            }
            
            .main-content {
                order: 1;
                padding: 1rem;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .recent-activities {
                grid-template-columns: 1fr;
            }
            
            .actions-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>
</body>
</html>