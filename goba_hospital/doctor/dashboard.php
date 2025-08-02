<?php
require_once '../includes/config.php';
check_session('doctor');

$page_title = 'Doctor Dashboard';

// Get doctor information
$stmt = $db->prepare("SELECT * FROM doctor_info WHERE doctor_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$doctor_info = $stmt->fetch();

// Get dashboard statistics
try {
    // Count total patients seen by this doctor
    $stmt = $db->prepare("
        SELECT COUNT(DISTINCT patient_id) as total 
        FROM consultation_records 
        WHERE doctor_id = ?
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $total_patients = $stmt->fetchColumn();
    
    // Count consultations this month
    $stmt = $db->prepare("
        SELECT COUNT(*) as total 
        FROM consultation_records 
        WHERE doctor_id = ? 
        AND MONTH(consultation_date) = MONTH(CURDATE()) 
        AND YEAR(consultation_date) = YEAR(CURDATE())
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $monthly_consultations = $stmt->fetchColumn();
    
    // Count surgeries this month
    $stmt = $db->prepare("
        SELECT COUNT(*) as total 
        FROM surgery_records 
        WHERE doctor_id = ? 
        AND MONTH(surgery_date) = MONTH(CURDATE()) 
        AND YEAR(surgery_date) = YEAR(CURDATE())
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $monthly_surgeries = $stmt->fetchColumn();
    
    // Count diagnoses this month
    $stmt = $db->prepare("
        SELECT COUNT(*) as total 
        FROM diagnosis_records 
        WHERE doctor_id = ? 
        AND MONTH(diagnosis_date) = MONTH(CURDATE()) 
        AND YEAR(diagnosis_date) = YEAR(CURDATE())
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $monthly_diagnoses = $stmt->fetchColumn();
    
    // Get recent patients
    $stmt = $db->prepare("
        SELECT DISTINCT 
            p.patient_id, 
            CONCAT(p.first_name, ' ', p.last_name) as patient_name,
            p.phone,
            p.blood_group,
            c.consultation_date,
            c.diagnosis
        FROM consultation_records c
        JOIN patient_info p ON c.patient_id = p.patient_id
        WHERE c.doctor_id = ?
        ORDER BY c.consultation_date DESC
        LIMIT 10
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $recent_patients = $stmt->fetchAll();
    
    // Get pending referrals made by this doctor
    $stmt = $db->prepare("
        SELECT COUNT(*) as total 
        FROM patient_referrals 
        WHERE referring_doctor_id = ? 
        AND status = 'Pending'
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $pending_referrals = $stmt->fetchColumn();
    
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
                        <i class="fas fa-tachometer-alt"></i> Doctor Dashboard
                    </h1>
                    <p class="text-muted">
                        Welcome back, Dr. <?php echo htmlspecialchars($doctor_info['first_name'] . ' ' . $doctor_info['last_name']); ?>
                        <span class="badge bg-info ms-2"><?php echo htmlspecialchars($doctor_info['specialization']); ?></span>
                    </p>
                </div>
                <div>
                    <span class="badge bg-primary px-3 py-2">
                        <i class="fas fa-clock"></i> <?php echo date('Y-m-d H:i:s'); ?>
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
                    <div class="stats-number"><?php echo $total_patients; ?></div>
                    <div class="stats-label">Total Patients</div>
                    <i class="fas fa-user-injured position-absolute"></i>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="card stats-card stats-success border-0 shadow">
                <div class="card-body position-relative">
                    <div class="stats-number"><?php echo $monthly_consultations; ?></div>
                    <div class="stats-label">This Month's Consultations</div>
                    <i class="fas fa-stethoscope position-absolute"></i>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="card stats-card stats-warning border-0 shadow">
                <div class="card-body position-relative">
                    <div class="stats-number"><?php echo $monthly_surgeries; ?></div>
                    <div class="stats-label">This Month's Surgeries</div>
                    <i class="fas fa-procedures position-absolute"></i>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="card stats-card stats-info border-0 shadow">
                <div class="card-body position-relative">
                    <div class="stats-number"><?php echo $monthly_diagnoses; ?></div>
                    <div class="stats-label">This Month's Diagnoses</div>
                    <i class="fas fa-diagnoses position-absolute"></i>
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
                            <a href="add_consultation.php" class="btn btn-primary w-100 btn-lg">
                                <i class="fas fa-stethoscope"></i><br>
                                New Consultation
                            </a>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <a href="add_surgery.php" class="btn btn-warning w-100 btn-lg">
                                <i class="fas fa-procedures"></i><br>
                                Record Surgery
                            </a>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <a href="add_diagnosis.php" class="btn btn-info w-100 btn-lg">
                                <i class="fas fa-diagnoses"></i><br>
                                Add Diagnosis
                            </a>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <a href="patients.php" class="btn btn-success w-100 btn-lg">
                                <i class="fas fa-users"></i><br>
                                Search Patients
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Recent Patients -->
        <div class="col-lg-8">
            <div class="card border-0 shadow">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-history"></i> Recent Patient Consultations
                    </h5>
                    <a href="consultations.php" class="btn btn-outline-primary btn-sm">
                        View All
                    </a>
                </div>
                <div class="card-body">
                    <?php if (!empty($recent_patients)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Patient ID</th>
                                        <th>Patient Name</th>
                                        <th>Blood Group</th>
                                        <th>Date</th>
                                        <th>Diagnosis</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_patients as $patient): ?>
                                        <tr>
                                            <td>
                                                <strong class="text-primary"><?php echo htmlspecialchars($patient['patient_id']); ?></strong>
                                            </td>
                                            <td>
                                                <strong><?php echo htmlspecialchars($patient['patient_name']); ?></strong>
                                                <br><small class="text-muted"><?php echo htmlspecialchars($patient['phone']); ?></small>
                                            </td>
                                            <td>
                                                <?php if ($patient['blood_group']): ?>
                                                    <span class="badge bg-danger"><?php echo $patient['blood_group']; ?></span>
                                                <?php else: ?>
                                                    <small class="text-muted">N/A</small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <small><?php echo date('Y-m-d H:i', strtotime($patient['consultation_date'])); ?></small>
                                            </td>
                                            <td>
                                                <small><?php echo htmlspecialchars(substr($patient['diagnosis'], 0, 50)) . (strlen($patient['diagnosis']) > 50 ? '...' : ''); ?></small>
                                            </td>
                                            <td>
                                                <a href="view_patient.php?id=<?php echo $patient['patient_id']; ?>" 
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
                            <i class="fas fa-user-injured fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No recent patient consultations found.</p>
                            <a href="add_consultation.php" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Add Your First Consultation
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Doctor Profile & Stats -->
        <div class="col-lg-4">
            <!-- Doctor Profile -->
            <div class="card border-0 shadow mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-user-md"></i> My Profile
                    </h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <i class="fas fa-user-circle fa-4x text-primary"></i>
                        <h6 class="mt-2 mb-0">Dr. <?php echo htmlspecialchars($doctor_info['first_name'] . ' ' . $doctor_info['last_name']); ?></h6>
                        <small class="text-muted"><?php echo htmlspecialchars($doctor_info['specialization']); ?></small>
                    </div>
                    
                    <div class="mb-2">
                        <strong>License:</strong> 
                        <span class="text-muted"><?php echo htmlspecialchars($doctor_info['license_number']); ?></span>
                    </div>
                    <div class="mb-2">
                        <strong>Department:</strong> 
                        <span class="text-muted"><?php echo htmlspecialchars($doctor_info['department']); ?></span>
                    </div>
                    <div class="mb-2">
                        <strong>Phone:</strong> 
                        <span class="text-muted"><?php echo htmlspecialchars($doctor_info['phone']); ?></span>
                    </div>
                    <div class="mb-3">
                        <strong>Email:</strong> 
                        <span class="text-muted"><?php echo htmlspecialchars($doctor_info['email']); ?></span>
                    </div>
                    
                    <div class="d-grid">
                        <a href="profile.php" class="btn btn-outline-primary">
                            <i class="fas fa-edit"></i> Edit Profile
                        </a>
                    </div>
                </div>
            </div>

            <!-- Monthly Activity -->
            <div class="card border-0 shadow">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-pie"></i> This Month's Activity
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <span>Consultations</span>
                            <strong class="text-primary"><?php echo $monthly_consultations; ?></strong>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-primary" style="width: 100%"></div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <span>Surgeries</span>
                            <strong class="text-warning"><?php echo $monthly_surgeries; ?></strong>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-warning" style="width: <?php echo $monthly_surgeries > 0 ? ($monthly_surgeries / max($monthly_consultations, 1)) * 100 : 0; ?>%"></div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <span>Diagnoses</span>
                            <strong class="text-info"><?php echo $monthly_diagnoses; ?></strong>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-info" style="width: <?php echo $monthly_diagnoses > 0 ? ($monthly_diagnoses / max($monthly_consultations, 1)) * 100 : 0; ?>%"></div>
                        </div>
                    </div>
                    
                    <?php if ($pending_referrals > 0): ?>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            You have <strong><?php echo $pending_referrals; ?></strong> pending referral(s).
                            <a href="referrals.php" class="alert-link">View them</a>
                        </div>
                    <?php endif; ?>
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

.stats-card.stats-success {
    background: linear-gradient(135deg, var(--success-color), #146c43);
}

.stats-card.stats-warning {
    background: linear-gradient(135deg, var(--warning-color), #d39e00);
    color: var(--dark-color);
}

.stats-card.stats-info {
    background: linear-gradient(135deg, var(--info-color), #0aa2c0);
    color: var(--dark-color);
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

.table th {
    border-top: none;
    font-weight: 600;
}

.progress {
    background-color: rgba(255, 255, 255, 0.2);
}
</style>

<?php include '../includes/footer.php'; ?>