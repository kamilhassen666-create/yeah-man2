<?php
require_once '../../config/database.php';
require_once '../../includes/auth.php';

// Require patient login
$auth->requireLogin('patient');

$db = getDB();
$currentUser = getCurrentUser();
$patientSSN = $currentUser['ssn'];

// Get patient information
$sql = "SELECT p.*, pl.last_login FROM patient p 
        JOIN patient_login pl ON p.ssn = pl.patient_ssn 
        WHERE p.ssn = ?";
$stmt = $db->prepare($sql);
$stmt->execute([$patientSSN]);
$patient = $stmt->fetch();

// Get recent consultations
$sql = "SELECT c.*, d.first_name as doctor_first_name, d.last_name as doctor_last_name, d.specialization 
        FROM consultation c 
        JOIN doctor d ON c.doctor_ssn = d.ssn 
        WHERE c.patient_ssn = ? 
        ORDER BY c.consultation_date DESC 
        LIMIT 5";
$stmt = $db->prepare($sql);
$stmt->execute([$patientSSN]);
$recentConsultations = $stmt->fetchAll();

// Get recent operations
$sql = "SELECT o.*, d.first_name as doctor_first_name, d.last_name as doctor_last_name 
        FROM operation o 
        JOIN doctor d ON o.doctor_ssn = d.ssn 
        WHERE o.patient_ssn = ? 
        ORDER BY o.operation_date DESC 
        LIMIT 3";
$stmt = $db->prepare($sql);
$stmt->execute([$patientSSN]);
$recentOperations = $stmt->fetchAll();

// Get recent diagnoses
$sql = "SELECT dia.*, d.first_name as doctor_first_name, d.last_name as doctor_last_name 
        FROM diagnosis dia 
        JOIN doctor d ON dia.doctor_ssn = d.ssn 
        WHERE dia.patient_ssn = ? 
        ORDER BY dia.diagnosis_date DESC 
        LIMIT 3";
$stmt = $db->prepare($sql);
$stmt->execute([$patientSSN]);
$recentDiagnoses = $stmt->fetchAll();

// Get statistics
$sql = "SELECT COUNT(*) as total_consultations FROM consultation WHERE patient_ssn = ?";
$stmt = $db->prepare($sql);
$stmt->execute([$patientSSN]);
$totalConsultations = $stmt->fetchColumn();

$sql = "SELECT COUNT(*) as total_operations FROM operation WHERE patient_ssn = ?";
$stmt = $db->prepare($sql);
$stmt->execute([$patientSSN]);
$totalOperations = $stmt->fetchColumn();

$sql = "SELECT COUNT(*) as total_diagnoses FROM diagnosis WHERE patient_ssn = ?";
$stmt = $db->prepare($sql);
$stmt->execute([$patientSSN]);
$totalDiagnoses = $stmt->fetchColumn();

$sql = "SELECT COUNT(*) as total_payments FROM payment WHERE patient_ssn = ?";
$stmt = $db->prepare($sql);
$stmt->execute([$patientSSN]);
$totalPayments = $stmt->fetchColumn();

// Handle logout
if (isset($_POST['action']) && $_POST['action'] === 'logout') {
    $auth->logout();
    redirect('../../index.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Dashboard - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="logo">
                <div class="logo-icon">
                    <i class="fas fa-hospital"></i>
                </div>
                <h1><?php echo SITE_NAME; ?></h1>
            </div>
            
            <nav class="nav">
                <a href="dashboard.php" class="nav-link">
                    <i class="fas fa-tachometer-alt"></i>
                    Dashboard
                </a>
                <a href="profile.php" class="nav-link">
                    <i class="fas fa-user"></i>
                    Profile
                </a>
                <a href="records.php" class="nav-link">
                    <i class="fas fa-file-medical"></i>
                    Medical Records
                </a>
                <a href="search.php" class="nav-link">
                    <i class="fas fa-search"></i>
                    Search
                </a>
                <a href="payments.php" class="nav-link">
                    <i class="fas fa-credit-card"></i>
                    Payments
                </a>
                <div class="dropdown">
                    <a href="#" class="nav-link">
                        <i class="fas fa-user-circle"></i>
                        <?php echo $currentUser['full_name']; ?>
                        <i class="fas fa-chevron-down"></i>
                    </a>
                    <div class="dropdown-menu">
                        <a href="profile.php" class="dropdown-item">
                            <i class="fas fa-user"></i>
                            My Profile
                        </a>
                        <a href="settings.php" class="dropdown-item">
                            <i class="fas fa-cog"></i>
                            Settings
                        </a>
                        <form method="POST" style="margin: 0;">
                            <input type="hidden" name="action" value="logout">
                            <button type="submit" class="dropdown-item" style="background: none; border: none; width: 100%; text-align: left; padding: 0.75rem 1.5rem; cursor: pointer;">
                                <i class="fas fa-sign-out-alt"></i>
                                Logout
                            </button>
                        </form>
                    </div>
                </div>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <main class="dashboard">
        <div class="container">
            <!-- Dashboard Header -->
            <div class="dashboard-header">
                <div>
                    <h1>Welcome, <?php echo $patient['first_name']; ?>!</h1>
                    <p>Patient ID: <?php echo $patient['ssn']; ?> | Last Login: <?php echo formatDate($patient['last_login'], 'M j, Y g:i A'); ?></p>
                </div>
                <div>
                    <button class="btn btn-primary" onclick="window.location.href='appointments.php'">
                        <i class="fas fa-calendar-plus"></i>
                        Book Appointment
                    </button>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number"><?php echo $totalConsultations; ?></div>
                    <div class="stat-label">Total Consultations</div>
                    <a href="records.php?type=consultations" class="btn btn-sm btn-outline">View All</a>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $totalOperations; ?></div>
                    <div class="stat-label">Operations/Surgeries</div>
                    <a href="records.php?type=operations" class="btn btn-sm btn-outline">View All</a>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $totalDiagnoses; ?></div>
                    <div class="stat-label">Diagnoses</div>
                    <a href="records.php?type=diagnoses" class="btn btn-sm btn-outline">View All</a>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $totalPayments; ?></div>
                    <div class="stat-label">Payment Records</div>
                    <a href="payments.php" class="btn btn-sm btn-outline">View All</a>
                </div>
            </div>

            <!-- Main Dashboard Content -->
            <div class="row">
                <!-- Recent Consultations -->
                <div class="col-8">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-stethoscope"></i>
                                Recent Consultations
                            </h3>
                            <a href="records.php?type=consultations" class="btn btn-sm btn-primary">View All</a>
                        </div>
                        <div class="card-body">
                            <?php if (empty($recentConsultations)): ?>
                                <p class="text-center">No consultation records found.</p>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Doctor</th>
                                                <th>Complaint</th>
                                                <th>Reference</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($recentConsultations as $consultation): ?>
                                                <tr>
                                                    <td><?php echo formatDate($consultation['consultation_date'], 'M j, Y'); ?></td>
                                                    <td>
                                                        Dr. <?php echo $consultation['doctor_first_name'] . ' ' . $consultation['doctor_last_name']; ?>
                                                        <br><small><?php echo $consultation['specialization']; ?></small>
                                                    </td>
                                                    <td><?php echo substr($consultation['complaint'], 0, 50) . (strlen($consultation['complaint']) > 50 ? '...' : ''); ?></td>
                                                    <td><?php echo $consultation['reference_number']; ?></td>
                                                    <td>
                                                        <button class="btn btn-sm btn-primary" onclick="viewConsultation(<?php echo $consultation['id']; ?>)">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Recent Operations -->
                    <?php if (!empty($recentOperations)): ?>
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-cut"></i>
                                    Recent Operations/Surgeries
                                </h3>
                                <a href="records.php?type=operations" class="btn btn-sm btn-primary">View All</a>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Type</th>
                                                <th>Doctor</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($recentOperations as $operation): ?>
                                                <tr>
                                                    <td><?php echo formatDate($operation['operation_date'], 'M j, Y'); ?></td>
                                                    <td><?php echo $operation['operation_type']; ?></td>
                                                    <td>Dr. <?php echo $operation['doctor_first_name'] . ' ' . $operation['doctor_last_name']; ?></td>
                                                    <td>
                                                        <span class="badge badge-<?php echo $operation['status'] === 'Completed' ? 'success' : 'warning'; ?>">
                                                            <?php echo $operation['status']; ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <button class="btn btn-sm btn-primary" onclick="viewOperation(<?php echo $operation['id']; ?>)">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Sidebar -->
                <div class="col-4">
                    <!-- Patient Quick Info -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-id-card"></i>
                                Quick Info
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="d-flex align-center gap-3 mb-3">
                                <div class="portal-icon" style="width: 60px; height: 60px; font-size: 1.5rem;">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div>
                                    <h4 class="mb-1"><?php echo $patient['first_name'] . ' ' . $patient['last_name']; ?></h4>
                                    <p class="mb-0">Patient ID: <?php echo $patient['ssn']; ?></p>
                                </div>
                            </div>
                            <div class="mb-2">
                                <strong>Date of Birth:</strong> <?php echo formatDate($patient['date_of_birth'], 'M j, Y'); ?>
                            </div>
                            <div class="mb-2">
                                <strong>Blood Type:</strong> <?php echo $patient['blood_type'] ?: 'Not specified'; ?>
                            </div>
                            <div class="mb-2">
                                <strong>Phone:</strong> <?php echo $patient['phone'] ?: 'Not provided'; ?>
                            </div>
                            <div class="mb-3">
                                <strong>Email:</strong> <?php echo $patient['email'] ?: 'Not provided'; ?>
                            </div>
                            <a href="profile.php" class="btn btn-primary" style="width: 100%;">
                                <i class="fas fa-edit"></i>
                                Update Profile
                            </a>
                        </div>
                    </div>

                    <!-- Recent Diagnoses -->
                    <?php if (!empty($recentDiagnoses)): ?>
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-diagnoses"></i>
                                    Recent Diagnoses
                                </h3>
                            </div>
                            <div class="card-body">
                                <?php foreach ($recentDiagnoses as $diagnosis): ?>
                                    <div class="mb-3 p-3" style="border-left: 4px solid var(--primary-color); background: var(--light-color);">
                                        <h5 class="mb-1"><?php echo $diagnosis['diagnosis_name']; ?></h5>
                                        <p class="mb-1"><?php echo formatDate($diagnosis['diagnosis_date'], 'M j, Y'); ?></p>
                                        <small>Dr. <?php echo $diagnosis['doctor_first_name'] . ' ' . $diagnosis['doctor_last_name']; ?></small>
                                        <div class="mt-2">
                                            <span class="badge badge-<?php echo strtolower($diagnosis['severity']); ?>">
                                                <?php echo $diagnosis['severity']; ?>
                                            </span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                                <a href="records.php?type=diagnoses" class="btn btn-outline" style="width: 100%;">View All Diagnoses</a>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Allergies Alert -->
                    <?php if (!empty($patient['allergies'])): ?>
                        <div class="card" style="border-left: 4px solid var(--danger-color);">
                            <div class="card-header" style="background: rgba(220, 53, 69, 0.1);">
                                <h3 class="card-title" style="color: var(--danger-color);">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    Allergies Alert
                                </h3>
                            </div>
                            <div class="card-body">
                                <p style="color: var(--danger-color); font-weight: 500;"><?php echo $patient['allergies']; ?></p>
                                <a href="profile.php" class="btn btn-danger btn-sm">Update Allergies</a>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Quick Actions -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-bolt"></i>
                                Quick Actions
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <a href="search.php" class="btn btn-outline">
                                    <i class="fas fa-search"></i>
                                    Search Records
                                </a>
                                <a href="payments.php" class="btn btn-outline">
                                    <i class="fas fa-credit-card"></i>
                                    View Payments
                                </a>
                                <a href="documents.php" class="btn btn-outline">
                                    <i class="fas fa-file-download"></i>
                                    Download Documents
                                </a>
                                <a href="emergency.php" class="btn btn-danger">
                                    <i class="fas fa-ambulance"></i>
                                    Emergency Info
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Scripts -->
    <script src="../../assets/js/main.js"></script>
    <script>
        function viewConsultation(id) {
            window.location.href = `consultation-details.php?id=${id}`;
        }
        
        function viewOperation(id) {
            window.location.href = `operation-details.php?id=${id}`;
        }
        
        // Add some dashboard-specific functionality
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-refresh dashboard every 5 minutes
            setInterval(() => {
                location.reload();
            }, 300000);
            
            // Show welcome message for new patients
            const totalRecords = <?php echo $totalConsultations + $totalOperations + $totalDiagnoses; ?>;
            if (totalRecords === 0) {
                GobaHospital.showNotification(
                    'Welcome to Goba Hospital!', 
                    'Your medical records will appear here as you receive treatment. Contact our staff if you need assistance.',
                    'info'
                );
            }
        });
    </script>
</body>
</html>