<?php
require_once '../includes/config.php';
check_session('patient');

$page_title = 'Patient Dashboard';

// Get patient information
$stmt = $db->prepare("SELECT * FROM patient_info WHERE patient_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$patient_info = $stmt->fetch();

// Get dashboard statistics
try {
    // Count total consultations
    $stmt = $db->prepare("SELECT COUNT(*) FROM consultation_records WHERE patient_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $total_consultations = $stmt->fetchColumn();
    
    // Count total surgeries
    $stmt = $db->prepare("SELECT COUNT(*) FROM surgery_records WHERE patient_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $total_surgeries = $stmt->fetchColumn();
    
    // Count total diagnoses
    $stmt = $db->prepare("SELECT COUNT(*) FROM diagnosis_records WHERE patient_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $total_diagnoses = $stmt->fetchColumn();
    
    // Get pending payments
    $stmt = $db->prepare("SELECT COUNT(*) FROM payment_info WHERE patient_id = ? AND status = 'Pending'");
    $stmt->execute([$_SESSION['user_id']]);
    $pending_payments = $stmt->fetchColumn();
    
    // Get recent consultations
    $stmt = $db->prepare("
        SELECT c.*, d.first_name as doctor_fname, d.last_name as doctor_lname, d.specialization
        FROM consultation_records c
        LEFT JOIN doctor_info d ON c.doctor_id = d.doctor_id
        WHERE c.patient_id = ?
        ORDER BY c.consultation_date DESC
        LIMIT 5
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $recent_consultations = $stmt->fetchAll();
    
    // Get last consultation date
    $stmt = $db->prepare("SELECT MAX(consultation_date) FROM consultation_records WHERE patient_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $last_consultation = $stmt->fetchColumn();
    
} catch (PDOException $e) {
    $error_message = "Error fetching dashboard data: " . $e->getMessage();
}

include '../includes/header.php';
?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0 text-primary">
                        <i class="fas fa-tachometer-alt"></i> My Health Dashboard
                    </h1>
                    <p class="text-muted">
                        Welcome, <?php echo htmlspecialchars($patient_info['first_name'] . ' ' . $patient_info['last_name']); ?>
                        <span class="badge bg-danger ms-2"><?php echo $patient_info['blood_group'] ?: 'Blood Group Not Set'; ?></span>
                    </p>
                </div>
                <div>
                    <span class="badge bg-primary px-3 py-2">
                        Patient ID: <?php echo htmlspecialchars($patient_info['patient_id']); ?>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card stats-card stats-primary border-0 shadow">
                <div class="card-body position-relative">
                    <div class="stats-number"><?php echo $total_consultations; ?></div>
                    <div class="stats-label">Total Consultations</div>
                    <i class="fas fa-stethoscope position-absolute"></i>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="card stats-card stats-warning border-0 shadow">
                <div class="card-body position-relative">
                    <div class="stats-number"><?php echo $total_surgeries; ?></div>
                    <div class="stats-label">Surgeries</div>
                    <i class="fas fa-procedures position-absolute"></i>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="card stats-card stats-info border-0 shadow">
                <div class="card-body position-relative">
                    <div class="stats-number"><?php echo $total_diagnoses; ?></div>
                    <div class="stats-label">Medical Diagnoses</div>
                    <i class="fas fa-diagnoses position-absolute"></i>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="card stats-card stats-danger border-0 shadow">
                <div class="card-body position-relative">
                    <div class="stats-number"><?php echo $pending_payments; ?></div>
                    <div class="stats-label">Pending Payments</div>
                    <i class="fas fa-credit-card position-absolute"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-bolt"></i> Quick Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-lg-3 col-md-6">
                            <a href="medical_history.php" class="btn btn-primary w-100 btn-lg">
                                <i class="fas fa-history"></i><br>
                                Medical History
                            </a>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <a href="consultations.php" class="btn btn-success w-100 btn-lg">
                                <i class="fas fa-stethoscope"></i><br>
                                View Consultations
                            </a>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <a href="payments.php" class="btn btn-warning w-100 btn-lg">
                                <i class="fas fa-credit-card"></i><br>
                                Make Payment
                            </a>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <a href="profile.php" class="btn btn-info w-100 btn-lg">
                                <i class="fas fa-user-edit"></i><br>
                                Update Profile
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Recent Medical Records -->
        <div class="col-lg-8">
            <div class="card border-0 shadow">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-history"></i> Recent Medical Records
                    </h5>
                    <a href="medical_history.php" class="btn btn-outline-primary btn-sm">
                        View All
                    </a>
                </div>
                <div class="card-body">
                    <?php if (!empty($recent_consultations)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Doctor</th>
                                        <th>Diagnosis</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_consultations as $consultation): ?>
                                        <tr>
                                            <td>
                                                <small><?php echo date('Y-m-d H:i', strtotime($consultation['consultation_date'])); ?></small>
                                            </td>
                                            <td>
                                                <strong>Dr. <?php echo htmlspecialchars($consultation['doctor_fname'] . ' ' . $consultation['doctor_lname']); ?></strong>
                                                <br><small class="text-muted"><?php echo htmlspecialchars($consultation['specialization']); ?></small>
                                            </td>
                                            <td>
                                                <small><?php echo htmlspecialchars(substr($consultation['diagnosis'], 0, 50)) . (strlen($consultation['diagnosis']) > 50 ? '...' : ''); ?></small>
                                            </td>
                                            <td>
                                                <span class="badge bg-success"><?php echo htmlspecialchars($consultation['status']); ?></span>
                                            </td>
                                            <td>
                                                <a href="view_consultation.php?id=<?php echo $consultation['id']; ?>" 
                                                   class="btn btn-outline-primary btn-sm">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-file-medical fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No medical records found.</p>
                            <p class="text-muted">Your medical records will appear here after your first consultation.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Patient Profile & Info -->
        <div class="col-lg-4">
            <!-- Patient Profile -->
            <div class="card border-0 shadow mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-user"></i> My Profile
                    </h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <i class="fas fa-user-circle fa-4x text-primary"></i>
                        <h6 class="mt-2 mb-0"><?php echo htmlspecialchars($patient_info['first_name'] . ' ' . $patient_info['last_name']); ?></h6>
                        <small class="text-muted">Patient ID: <?php echo htmlspecialchars($patient_info['patient_id']); ?></small>
                    </div>
                    
                    <div class="mb-2">
                        <strong>Blood Group:</strong> 
                        <span class="badge bg-danger"><?php echo $patient_info['blood_group'] ?: 'Not Set'; ?></span>
                    </div>
                    <div class="mb-2">
                        <strong>Age:</strong> 
                        <span class="text-muted"><?php echo date('Y') - date('Y', strtotime($patient_info['date_of_birth'])); ?> years</span>
                    </div>
                    <div class="mb-2">
                        <strong>Gender:</strong> 
                        <span class="text-muted"><?php echo htmlspecialchars($patient_info['gender']); ?></span>
                    </div>
                    <div class="mb-2">
                        <strong>Phone:</strong> 
                        <span class="text-muted"><?php echo htmlspecialchars($patient_info['phone']); ?></span>
                    </div>
                    <div class="mb-3">
                        <strong>Email:</strong> 
                        <span class="text-muted"><?php echo htmlspecialchars($patient_info['email']); ?></span>
                    </div>
                    
                    <div class="d-grid">
                        <a href="profile.php" class="btn btn-outline-primary">
                            <i class="fas fa-edit"></i> Edit Profile
                        </a>
                    </div>
                </div>
            </div>

            <!-- Health Summary -->
            <div class="card border-0 shadow mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-heartbeat"></i> Health Summary
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <span>Last Visit</span>
                            <strong class="text-primary">
                                <?php echo $last_consultation ? date('Y-m-d', strtotime($last_consultation)) : 'Never'; ?>
                            </strong>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <span>Emergency Contact</span>
                            <small class="text-muted"><?php echo htmlspecialchars($patient_info['emergency_contact_name'] ?: 'Not Set'); ?></small>
                        </div>
                    </div>
                    
                    <?php if ($patient_info['emergency_contact_phone']): ?>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <span>Emergency Phone</span>
                                <small class="text-muted"><?php echo htmlspecialchars($patient_info['emergency_contact_phone']); ?></small>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($pending_payments > 0): ?>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            You have <strong><?php echo $pending_payments; ?></strong> pending payment(s).
                            <a href="payments.php" class="alert-link">Pay now</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Important Notes -->
            <div class="card border-0 shadow">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle"></i> Important Notes
                    </h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <i class="fas fa-check text-success"></i>
                            Keep your profile information updated
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success"></i>
                            Check your medical history regularly
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success"></i>
                            Follow up on prescribed medications
                        </li>
                        <li>
                            <i class="fas fa-check text-success"></i>
                            Contact us for any health concerns
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.stats-card {
    background: linear-gradient(135deg, var(--primary-color), #0a58ca);
    color: white;
    border-radius: 15px;
    overflow: hidden;
    transition: all 0.3s ease;
}

.stats-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
}

.stats-card.stats-warning {
    background: linear-gradient(135deg, var(--warning-color), #d39e00);
    color: var(--dark-color);
}

.stats-card.stats-info {
    background: linear-gradient(135deg, var(--info-color), #0aa2c0);
    color: var(--dark-color);
}

.stats-card.stats-danger {
    background: linear-gradient(135deg, var(--danger-color), #b02a37);
    color: white;
}

.stats-number {
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
}

.stats-label {
    font-size: 1rem;
    opacity: 0.9;
    font-weight: 500;
}

.stats-card i {
    font-size: 3rem;
    opacity: 0.3;
    right: 1.5rem;
    top: 1.5rem;
}

.btn-lg {
    padding: 1rem;
    font-size: 1rem;
    line-height: 1.5;
}
</style>

<?php include '../includes/footer.php'; ?>