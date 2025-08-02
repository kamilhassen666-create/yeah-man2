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
                       ORDER BY c.consultation_date DESC 
                       LIMIT 5");
$stmt->execute([$_SESSION['user_id']]);
$consultations = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get patient's surgeries
$stmt = $pdo->prepare("SELECT s.*, d.first_name as doctor_first_name, d.last_name as doctor_last_name 
                       FROM surgeries s 
                       JOIN doctors d ON s.doctor_id = d.id 
                       WHERE s.patient_id = ? 
                       ORDER BY s.surgery_date DESC 
                       LIMIT 5");
$stmt->execute([$_SESSION['user_id']]);
$surgeries = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get patient's diagnoses
$stmt = $pdo->prepare("SELECT d.*, doc.first_name as doctor_first_name, doc.last_name as doctor_last_name 
                       FROM diagnoses d 
                       JOIN doctors doc ON d.doctor_id = doc.id 
                       WHERE d.patient_id = ? 
                       ORDER BY d.diagnosis_date DESC 
                       LIMIT 5");
$stmt->execute([$_SESSION['user_id']]);
$diagnoses = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get recent payments
$stmt = $pdo->prepare("SELECT * FROM payments WHERE patient_id = ? ORDER BY payment_date DESC LIMIT 5");
$stmt->execute([$_SESSION['user_id']]);
$payments = $stmt->fetchAll(PDO::FETCH_ASSOC);

include '../includes/header.php';
?>

<div class="row">
    <!-- Sidebar -->
    <div class="col-md-3">
        <div class="sidebar">
            <div class="text-center mb-4">
                <img src="../assets/images/patient-avatar.png" alt="Patient" class="profile-avatar mb-3">
                <h5 class="text-white"><?php echo $_SESSION['full_name']; ?></h5>
                <p class="text-white-50">Patient ID: <?php echo $patient['patient_id']; ?></p>
            </div>
            
            <nav class="nav flex-column">
                <a class="nav-link active" href="dashboard.php">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
                <a class="nav-link" href="medical_records.php">
                    <i class="fas fa-notes-medical"></i> Medical Records
                </a>
                <a class="nav-link" href="consultations.php">
                    <i class="fas fa-stethoscope"></i> Consultations
                </a>
                <a class="nav-link" href="surgeries.php">
                    <i class="fas fa-procedures"></i> Surgeries
                </a>
                <a class="nav-link" href="diagnoses.php">
                    <i class="fas fa-thermometer-half"></i> Diagnoses
                </a>
                <a class="nav-link" href="payments.php">
                    <i class="fas fa-credit-card"></i> Payments
                </a>
                <a class="nav-link" href="profile.php">
                    <i class="fas fa-user"></i> Profile
                </a>
                <a class="nav-link" href="../logout.php">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </nav>
        </div>
    </div>

    <!-- Main Content -->
    <div class="col-md-9">
        <div class="main-content">
            <!-- Welcome Section -->
            <div class="row mb-4">
                <div class="col-12">
                    <h2 class="mb-3">
                        <i class="fas fa-user-injured me-2"></i>
                        Welcome, <?php echo $patient['first_name']; ?>!
                    </h2>
                    <p class="text-muted">Here's your medical information overview</p>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="dashboard-card">
                        <h3><?php echo count($consultations); ?></h3>
                        <p>Consultations</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="dashboard-card">
                        <h3><?php echo count($surgeries); ?></h3>
                        <p>Surgeries</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="dashboard-card">
                        <h3><?php echo count($diagnoses); ?></h3>
                        <p>Diagnoses</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="dashboard-card">
                        <h3><?php echo count($payments); ?></h3>
                        <p>Payments</p>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="row">
                <!-- Recent Consultations -->
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-stethoscope me-2"></i>
                                Recent Consultations
                            </h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($consultations)): ?>
                                <p class="text-muted">No consultations found.</p>
                            <?php else: ?>
                                <?php foreach ($consultations as $consultation): ?>
                                    <div class="border-bottom pb-2 mb-2">
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <strong>Dr. <?php echo $consultation['doctor_first_name'] . ' ' . $consultation['doctor_last_name']; ?></strong>
                                                <br>
                                                <small class="text-muted">
                                                    <?php echo formatDateTime($consultation['consultation_date']); ?>
                                                </small>
                                            </div>
                                            <span class="badge bg-primary"><?php echo $consultation['reference_id']; ?></span>
                                        </div>
                                        <p class="mb-1"><?php echo substr($consultation['diagnosis'], 0, 100) . '...'; ?></p>
                                    </div>
                                <?php endforeach; ?>
                                <a href="consultations.php" class="btn btn-sm btn-outline-primary">View All</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Recent Surgeries -->
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-procedures me-2"></i>
                                Recent Surgeries
                            </h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($surgeries)): ?>
                                <p class="text-muted">No surgeries found.</p>
                            <?php else: ?>
                                <?php foreach ($surgeries as $surgery): ?>
                                    <div class="border-bottom pb-2 mb-2">
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <strong><?php echo $surgery['surgery_type']; ?></strong>
                                                <br>
                                                <small class="text-muted">
                                                    Dr. <?php echo $surgery['doctor_first_name'] . ' ' . $surgery['doctor_last_name']; ?>
                                                    <br>
                                                    <?php echo formatDateTime($surgery['surgery_date']); ?>
                                                </small>
                                            </div>
                                            <span class="badge bg-success"><?php echo $surgery['reference_id']; ?></span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                                <a href="surgeries.php" class="btn btn-sm btn-outline-success">View All</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Recent Diagnoses -->
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-thermometer-half me-2"></i>
                                Recent Diagnoses
                            </h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($diagnoses)): ?>
                                <p class="text-muted">No diagnoses found.</p>
                            <?php else: ?>
                                <?php foreach ($diagnoses as $diagnosis): ?>
                                    <div class="border-bottom pb-2 mb-2">
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <strong><?php echo $diagnosis['condition_name']; ?></strong>
                                                <br>
                                                <small class="text-muted">
                                                    Dr. <?php echo $diagnosis['doctor_first_name'] . ' ' . $diagnosis['doctor_last_name']; ?>
                                                    <br>
                                                    <?php echo formatDateTime($diagnosis['diagnosis_date']); ?>
                                                </small>
                                            </div>
                                            <span class="badge bg-info"><?php echo $diagnosis['reference_id']; ?></span>
                                        </div>
                                        <p class="mb-1"><?php echo substr($diagnosis['description'], 0, 100) . '...'; ?></p>
                                    </div>
                                <?php endforeach; ?>
                                <a href="diagnoses.php" class="btn btn-sm btn-outline-info">View All</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Recent Payments -->
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-credit-card me-2"></i>
                                Recent Payments
                            </h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($payments)): ?>
                                <p class="text-muted">No payments found.</p>
                            <?php else: ?>
                                <?php foreach ($payments as $payment): ?>
                                    <div class="border-bottom pb-2 mb-2">
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <strong>ETB <?php echo number_format($payment['amount'], 2); ?></strong>
                                                <br>
                                                <small class="text-muted">
                                                    <?php echo $payment['payment_method']; ?>
                                                    <br>
                                                    <?php echo formatDateTime($payment['payment_date']); ?>
                                                </small>
                                            </div>
                                            <span class="badge bg-<?php echo $payment['status'] == 'Completed' ? 'success' : ($payment['status'] == 'Pending' ? 'warning' : 'danger'); ?>">
                                                <?php echo $payment['status']; ?>
                                            </span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                                <a href="payments.php" class="btn btn-sm btn-outline-warning">View All</a>
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
                                <i class="fas fa-bolt me-2"></i>
                                Quick Actions
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3 mb-3">
                                    <a href="medical_records.php" class="btn btn-primary w-100">
                                        <i class="fas fa-notes-medical me-2"></i>
                                        View Medical Records
                                    </a>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <a href="payments.php" class="btn btn-success w-100">
                                        <i class="fas fa-credit-card me-2"></i>
                                        Make Payment
                                    </a>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <a href="profile.php" class="btn btn-info w-100">
                                        <i class="fas fa-user me-2"></i>
                                        Update Profile
                                    </a>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <a href="search_records.php" class="btn btn-warning w-100">
                                        <i class="fas fa-search me-2"></i>
                                        Search Records
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