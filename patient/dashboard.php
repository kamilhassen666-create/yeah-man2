<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

$auth = new Auth();
$auth->requireLogin(['patient']);

$userData = $auth->getUserData();
$database = new Database();
$db = $database->getConnection();

// Get patient SSN from login data
$patientSSN = $userData['patient_ssn'];

// Fetch patient details
try {
    $stmt = $db->prepare("SELECT * FROM patient WHERE ssn = ?");
    $stmt->execute([$patientSSN]);
    $patient = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Get consultation records count
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM consultation WHERE patient_ssn = ?");
    $stmt->execute([$patientSSN]);
    $consultationCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Get operation records count
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM operation WHERE patient_ssn = ?");
    $stmt->execute([$patientSSN]);
    $operationCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Get diagnosis records count
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM diagnosis WHERE patient_ssn = ?");
    $stmt->execute([$patientSSN]);
    $diagnosisCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Get recent records
    $stmt = $db->prepare("
        SELECT 'consultation' as type, consultation_date as date, reference_number, 
               CONCAT(d.first_name, ' ', d.last_name) as doctor_name,
               diagnosis_summary as summary
        FROM consultation c
        JOIN doctor d ON c.doctor_ssn = d.ssn
        WHERE c.patient_ssn = ?
        UNION ALL
        SELECT 'operation' as type, operation_date as date, reference_number,
               CONCAT(d.first_name, ' ', d.last_name) as doctor_name,
               operation_type as summary
        FROM operation o
        JOIN doctor d ON o.doctor_ssn = d.ssn
        WHERE o.patient_ssn = ?
        UNION ALL
        SELECT 'diagnosis' as type, diagnosis_date as date, reference_number,
               CONCAT(d.first_name, ' ', d.last_name) as doctor_name,
               diagnosis_name as summary
        FROM diagnosis di
        JOIN doctor d ON di.doctor_ssn = d.ssn
        WHERE di.patient_ssn = ?
        ORDER BY date DESC
        LIMIT 5
    ");
    $stmt->execute([$patientSSN, $patientSSN, $patientSSN]);
    $recentRecords = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    error_log("Dashboard data fetch error: " . $e->getMessage());
    $patient = null;
    $consultationCount = $operationCount = $diagnosisCount = 0;
    $recentRecords = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Dashboard - Goba Hospital</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="logged-in">
    <!-- Header -->
    <header class="header">
        <div class="container">
            <nav class="nav-container">
                <div class="logo">
                    <div class="logo-icon">🏥</div>
                    <span>Goba Hospital</span>
                </div>
                <ul class="nav-menu">
                    <li><a href="dashboard.php" class="nav-link active">Dashboard</a></li>
                    <li><a href="profile.php" class="nav-link">Profile</a></li>
                    <li><a href="records.php" class="nav-link">Medical Records</a></li>
                    <li><a href="payments.php" class="nav-link">Payments</a></li>
                    <li><a href="logout.php" class="nav-link">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        <div class="dashboard">
            <!-- Sidebar -->
            <aside class="sidebar">
                <ul class="sidebar-menu">
                    <li class="sidebar-item">
                        <a href="dashboard.php" class="sidebar-link active">
                            📊 Dashboard
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a href="profile.php" class="sidebar-link">
                            👤 My Profile
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a href="records.php" class="sidebar-link">
                            📋 Medical Records
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a href="search.php" class="sidebar-link">
                            🔍 Search Records
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a href="consultations.php" class="sidebar-link">
                            🩺 Consultations
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a href="operations.php" class="sidebar-link">
                            🏥 Operations
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a href="diagnoses.php" class="sidebar-link">
                            📋 Diagnoses
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a href="payments.php" class="sidebar-link">
                            💳 Payments
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a href="logout.php" class="sidebar-link">
                            🚪 Logout
                        </a>
                    </li>
                </ul>
            </aside>

            <!-- Dashboard Content -->
            <div class="dashboard-content">
                <!-- Welcome Section -->
                <div class="card">
                    <h1>Welcome, <?php echo htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']); ?>!</h1>
                    <p>Patient ID: <?php echo htmlspecialchars($patient['ssn']); ?></p>
                    <p>Here's an overview of your medical records and recent activity.</p>
                </div>

                <!-- Statistics Cards -->
                <div class="portals-grid">
                    <div class="portal-card">
                        <div class="portal-icon">🩺</div>
                        <h3><?php echo $consultationCount; ?></h3>
                        <p>Total Consultations</p>
                        <a href="consultations.php" class="btn">View Details</a>
                    </div>

                    <div class="portal-card">
                        <div class="portal-icon">🏥</div>
                        <h3><?php echo $operationCount; ?></h3>
                        <p>Total Operations</p>
                        <a href="operations.php" class="btn">View Details</a>
                    </div>

                    <div class="portal-card">
                        <div class="portal-icon">📋</div>
                        <h3><?php echo $diagnosisCount; ?></h3>
                        <p>Total Diagnoses</p>
                        <a href="diagnoses.php" class="btn">View Details</a>
                    </div>

                    <div class="portal-card">
                        <div class="portal-icon">👤</div>
                        <h3>Profile</h3>
                        <p>Personal Information</p>
                        <a href="profile.php" class="btn btn-secondary">Update Profile</a>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Recent Medical Records</h2>
                    </div>
                    
                    <?php if (empty($recentRecords)): ?>
                        <p>No medical records found. When you have consultations, operations, or diagnoses, they will appear here.</p>
                    <?php else: ?>
                        <div class="table-container">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Type</th>
                                        <th>Date</th>
                                        <th>Doctor</th>
                                        <th>Summary</th>
                                        <th>Reference</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentRecords as $record): ?>
                                        <tr>
                                            <td>
                                                <span class="badge" style="background: 
                                                    <?php echo $record['type'] === 'consultation' ? 'var(--primary-color)' : 
                                                            ($record['type'] === 'operation' ? 'var(--danger-color)' : 'var(--accent-color)'); ?>; 
                                                    color: white; padding: 0.25rem 0.5rem; border-radius: 3px; font-size: 0.75rem;">
                                                    <?php echo ucfirst($record['type']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('M d, Y', strtotime($record['date'])); ?></td>
                                            <td><?php echo htmlspecialchars($record['doctor_name']); ?></td>
                                            <td><?php echo htmlspecialchars(substr($record['summary'], 0, 50)) . (strlen($record['summary']) > 50 ? '...' : ''); ?></td>
                                            <td><code><?php echo htmlspecialchars($record['reference_number']); ?></code></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div style="text-align: center; margin-top: 1rem;">
                            <a href="records.php" class="btn">View All Records</a>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Quick Actions -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Quick Actions</h2>
                    </div>
                    <div class="portals-grid">
                        <a href="search.php" class="portal-card" style="text-decoration: none;">
                            <div class="portal-icon">🔍</div>
                            <h4>Search Records</h4>
                            <p>Find specific medical records by date, doctor, or reference number</p>
                        </a>

                        <a href="profile.php" class="portal-card" style="text-decoration: none;">
                            <div class="portal-icon">✏️</div>
                            <h4>Update Profile</h4>
                            <p>Edit your personal information and emergency contacts</p>
                        </a>

                        <a href="payments.php" class="portal-card" style="text-decoration: none;">
                            <div class="portal-icon">💳</div>
                            <h4>Payment History</h4>
                            <p>View and manage your medical service payments</p>
                        </a>
                    </div>
                </div>

                <!-- Health Summary -->
                <?php if ($patient): ?>
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Health Information Summary</h2>
                    </div>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                        <div>
                            <strong>Date of Birth:</strong><br>
                            <?php echo date('F d, Y', strtotime($patient['date_of_birth'])); ?>
                        </div>
                        <div>
                            <strong>Gender:</strong><br>
                            <?php echo htmlspecialchars($patient['gender']); ?>
                        </div>
                        <div>
                            <strong>Email:</strong><br>
                            <?php echo htmlspecialchars($patient['email']); ?>
                        </div>
                        <div>
                            <strong>Phone:</strong><br>
                            <?php echo htmlspecialchars($patient['phone']); ?>
                        </div>
                        <div>
                            <strong>Emergency Contact:</strong><br>
                            <?php echo htmlspecialchars($patient['emergency_contact']); ?><br>
                            <small><?php echo htmlspecialchars($patient['emergency_phone']); ?></small>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <script src="../assets/js/main.js"></script>
</body>
</html>