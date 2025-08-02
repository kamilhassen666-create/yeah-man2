<?php
require_once '../includes/config.php';
requireUserType(['patient']);

$pdo = getDBConnection();

// Get patient information
$stmt = $pdo->prepare("SELECT * FROM patients WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$patient = $stmt->fetch(PDO::FETCH_ASSOC);

// Get patient's consultations
$stmt = $pdo->prepare("SELECT c.*, d.first_name as doctor_first_name, d.last_name as doctor_last_name 
                       FROM consultations c 
                       JOIN doctors d ON c.doctor_id = d.id 
                       WHERE c.patient_id = ? 
                       ORDER BY c.consultation_date DESC");
$stmt->execute([$_SESSION['user_id']]);
$consultations = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get patient's surgeries
$stmt = $pdo->prepare("SELECT s.*, d.first_name as doctor_first_name, d.last_name as doctor_last_name 
                       FROM surgeries s 
                       JOIN doctors d ON s.doctor_id = d.id 
                       WHERE s.patient_id = ? 
                       ORDER BY s.surgery_date DESC");
$stmt->execute([$_SESSION['user_id']]);
$surgeries = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get patient's diagnoses
$stmt = $pdo->prepare("SELECT d.*, doc.first_name as doctor_first_name, doc.last_name as doctor_last_name 
                       FROM diagnoses d 
                       JOIN doctors doc ON d.doctor_id = doc.id 
                       WHERE d.patient_id = ? 
                       ORDER BY d.diagnosis_date DESC");
$stmt->execute([$_SESSION['user_id']]);
$diagnoses = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get patient's payments
$stmt = $pdo->prepare("SELECT * FROM payments WHERE patient_id = ? ORDER BY payment_date DESC");
$stmt->execute([$_SESSION['user_id']]);
$payments = $stmt->fetchAll(PDO::FETCH_ASSOC);

include '../includes/header.php';
?>

<div class="row">
    <div class="col-lg-3">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="text-center mb-4">
                <img src="../assets/images/patient-avatar.png" alt="Patient" class="profile-avatar mb-3">
                <h5 class="text-white"><?php echo $_SESSION['full_name']; ?></h5>
                <p class="text-white-50">Patient ID: <?php echo $patient['patient_id']; ?></p>
            </div>
            
            <nav class="nav flex-column">
                <a class="nav-link active" href="dashboard.php">
                    <i class="fas fa-tachometer-alt"></i>Dashboard
                </a>
                <a class="nav-link" href="medical_records.php">
                    <i class="fas fa-file-medical"></i>Medical Records
                </a>
                <a class="nav-link" href="consultations.php">
                    <i class="fas fa-stethoscope"></i>Consultations
                </a>
                <a class="nav-link" href="surgeries.php">
                    <i class="fas fa-procedures"></i>Surgeries
                </a>
                <a class="nav-link" href="diagnoses.php">
                    <i class="fas fa-notes-medical"></i>Diagnoses
                </a>
                <a class="nav-link" href="payments.php">
                    <i class="fas fa-credit-card"></i>Payments
                </a>
                <a class="nav-link" href="profile.php">
                    <i class="fas fa-user"></i>Profile
                </a>
            </nav>
        </div>
    </div>
    
    <div class="col-lg-9">
        <div class="main-content">
            <!-- Welcome Section -->
            <div class="row mb-4">
                <div class="col-12">
                    <h2>Welcome, <?php echo $patient['first_name']; ?>!</h2>
                    <p class="text-muted">Here's an overview of your medical information</p>
                </div>
            </div>
            
            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="dashboard-card">
                        <h3><?php echo count($consultations); ?></h3>
                        <p>Consultations</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="dashboard-card">
                        <h3><?php echo count($surgeries); ?></h3>
                        <p>Surgeries</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="dashboard-card">
                        <h3><?php echo count($diagnoses); ?></h3>
                        <p>Diagnoses</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="dashboard-card">
                        <h3><?php echo count($payments); ?></h3>
                        <p>Payments</p>
                    </div>
                </div>
            </div>
            
            <!-- Recent Consultations -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="fas fa-stethoscope me-2"></i>Recent Consultations
                            </h5>
                            <a href="consultations.php" class="btn btn-primary btn-sm">View All</a>
                        </div>
                        <div class="card-body">
                            <?php if (empty($consultations)): ?>
                                <p class="text-muted text-center">No consultations found.</p>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Doctor</th>
                                                <th>Diagnosis</th>
                                                <th>Reference ID</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach (array_slice($consultations, 0, 5) as $consultation): ?>
                                                <tr>
                                                    <td><?php echo formatDateTime($consultation['consultation_date']); ?></td>
                                                    <td>Dr. <?php echo $consultation['doctor_first_name'] . ' ' . $consultation['doctor_last_name']; ?></td>
                                                    <td><?php echo substr($consultation['diagnosis'], 0, 50) . (strlen($consultation['diagnosis']) > 50 ? '...' : ''); ?></td>
                                                    <td><span class="badge bg-primary"><?php echo $consultation['reference_id']; ?></span></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Recent Payments -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="fas fa-credit-card me-2"></i>Recent Payments
                            </h5>
                            <a href="payments.php" class="btn btn-success btn-sm">Make Payment</a>
                        </div>
                        <div class="card-body">
                            <?php if (empty($payments)): ?>
                                <p class="text-muted text-center">No payments found.</p>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Amount</th>
                                                <th>Method</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach (array_slice($payments, 0, 5) as $payment): ?>
                                                <tr>
                                                    <td><?php echo formatDateTime($payment['payment_date']); ?></td>
                                                    <td>ETB <?php echo number_format($payment['amount'], 2); ?></td>
                                                    <td><?php echo $payment['payment_method']; ?></td>
                                                    <td>
                                                        <span class="badge bg-<?php echo $payment['status'] == 'Completed' ? 'success' : ($payment['status'] == 'Pending' ? 'warning' : 'danger'); ?>">
                                                            <?php echo $payment['status']; ?>
                                                        </span>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-bolt me-2"></i>Quick Actions
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3 mb-3">
                                    <a href="payments.php" class="btn btn-success w-100">
                                        <i class="fas fa-credit-card me-2"></i>Make Payment
                                    </a>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <a href="medical_records.php" class="btn btn-primary w-100">
                                        <i class="fas fa-file-medical me-2"></i>View Records
                                    </a>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <a href="profile.php" class="btn btn-info w-100">
                                        <i class="fas fa-user me-2"></i>Update Profile
                                    </a>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <a href="consultations.php" class="btn btn-warning w-100">
                                        <i class="fas fa-search me-2"></i>Search Records
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>