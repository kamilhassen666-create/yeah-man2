<?php
require_once '../includes/config.php';
check_session('staff');

$page_title = 'Staff Dashboard';

// Get staff information
$stmt = $db->prepare("SELECT * FROM staff_info WHERE staff_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$staff_info = $stmt->fetch();

// Get dashboard statistics
try {
    // Count medications managed this month
    $stmt = $db->prepare("
        SELECT COUNT(*) FROM medication_dosage 
        WHERE staff_id = ? 
        AND MONTH(created_at) = MONTH(CURDATE()) 
        AND YEAR(created_at) = YEAR(CURDATE())
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $monthly_medications = $stmt->fetchColumn();
    
    // Count total patients assisted
    $stmt = $db->prepare("SELECT COUNT(DISTINCT patient_id) FROM medication_dosage WHERE staff_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $total_patients = $stmt->fetchColumn();
    
    // Count active medications
    $stmt = $db->prepare("
        SELECT COUNT(*) FROM medication_dosage 
        WHERE staff_id = ? 
        AND (end_date IS NULL OR end_date >= CURDATE())
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $active_medications = $stmt->fetchColumn();
    
    // Get total medication records
    $stmt = $db->prepare("SELECT COUNT(*) FROM medication_dosage WHERE staff_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $total_medications = $stmt->fetchColumn();
    
    // Get recent medication entries
    $stmt = $db->prepare("
        SELECT m.*, p.first_name, p.last_name, p.patient_id as patient_code
        FROM medication_dosage m
        JOIN patient_info p ON m.patient_id = p.patient_id
        WHERE m.staff_id = ?
        ORDER BY m.created_at DESC
        LIMIT 10
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $recent_medications = $stmt->fetchAll();
    
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
                        <i class="fas fa-tachometer-alt"></i> Staff Dashboard
                    </h1>
                    <p class="text-muted">
                        Welcome back, <?php echo htmlspecialchars($staff_info['first_name'] . ' ' . $staff_info['last_name']); ?>
                        <span class="badge bg-secondary ms-2"><?php echo htmlspecialchars($staff_info['position']); ?></span>
                        <span class="badge bg-info ms-1"><?php echo htmlspecialchars($staff_info['department']); ?></span>
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
                    <div class="stats-label">Patients Assisted</div>
                    <i class="fas fa-user-injured position-absolute"></i>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="card stats-card stats-success border-0 shadow">
                <div class="card-body position-relative">
                    <div class="stats-number"><?php echo $monthly_medications; ?></div>
                    <div class="stats-label">This Month's Medications</div>
                    <i class="fas fa-pills position-absolute"></i>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="card stats-card stats-warning border-0 shadow">
                <div class="card-body position-relative">
                    <div class="stats-number"><?php echo $active_medications; ?></div>
                    <div class="stats-label">Active Medications</div>
                    <i class="fas fa-prescription-bottle-alt position-absolute"></i>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="card stats-card stats-info border-0 shadow">
                <div class="card-body position-relative">
                    <div class="stats-number"><?php echo $total_medications; ?></div>
                    <div class="stats-label">Total Medication Records</div>
                    <i class="fas fa-file-medical position-absolute"></i>
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
                            <a href="add_medication.php" class="btn btn-primary w-100 btn-lg">
                                <i class="fas fa-plus"></i><br>
                                Add Medication
                            </a>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <a href="medications.php" class="btn btn-success w-100 btn-lg">
                                <i class="fas fa-pills"></i><br>
                                View Medications
                            </a>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <a href="search_patient.php" class="btn btn-info w-100 btn-lg">
                                <i class="fas fa-search"></i><br>
                                Search Patient
                            </a>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <a href="profile.php" class="btn btn-warning w-100 btn-lg">
                                <i class="fas fa-user-edit"></i><br>
                                My Profile
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Recent Medication Activities -->
        <div class="col-lg-8">
            <div class="card border-0 shadow">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-history"></i> Recent Medication Activities
                    </h5>
                    <a href="medications.php" class="btn btn-outline-primary btn-sm">
                        View All
                    </a>
                </div>
                <div class="card-body">
                    <?php if (!empty($recent_medications)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Patient</th>
                                        <th>Medication</th>
                                        <th>Dosage</th>
                                        <th>Frequency</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_medications as $medication): ?>
                                        <tr>
                                            <td>
                                                <small><?php echo date('Y-m-d H:i', strtotime($medication['created_at'])); ?></small>
                                            </td>
                                            <td>
                                                <strong><?php echo htmlspecialchars($medication['first_name'] . ' ' . $medication['last_name']); ?></strong>
                                                <br><small class="text-muted"><?php echo htmlspecialchars($medication['patient_code']); ?></small>
                                            </td>
                                            <td>
                                                <strong><?php echo htmlspecialchars($medication['medication_name']); ?></strong>
                                            </td>
                                            <td>
                                                <span class="badge bg-info"><?php echo htmlspecialchars($medication['dosage']); ?></span>
                                            </td>
                                            <td>
                                                <small class="text-muted"><?php echo htmlspecialchars($medication['frequency']); ?></small>
                                            </td>
                                            <td>
                                                <a href="view_medication.php?id=<?php echo $medication['id']; ?>" 
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
                            <i class="fas fa-pills fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No medication records found.</p>
                            <a href="add_medication.php" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Add Your First Medication Record
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Staff Profile & Info -->
        <div class="col-lg-4">
            <!-- Staff Profile -->
            <div class="card border-0 shadow mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-user-nurse"></i> My Profile
                    </h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <i class="fas fa-user-circle fa-4x text-primary"></i>
                        <h6 class="mt-2 mb-0"><?php echo htmlspecialchars($staff_info['first_name'] . ' ' . $staff_info['last_name']); ?></h6>
                        <small class="text-muted">Staff ID: <?php echo htmlspecialchars($staff_info['staff_id']); ?></small>
                    </div>
                    
                    <div class="mb-2">
                        <strong>Position:</strong> 
                        <span class="badge bg-secondary"><?php echo htmlspecialchars($staff_info['position']); ?></span>
                    </div>
                    <div class="mb-2">
                        <strong>Department:</strong> 
                        <span class="text-muted"><?php echo htmlspecialchars($staff_info['department']); ?></span>
                    </div>
                    <div class="mb-2">
                        <strong>Phone:</strong> 
                        <span class="text-muted"><?php echo htmlspecialchars($staff_info['phone']); ?></span>
                    </div>
                    <div class="mb-3">
                        <strong>Email:</strong> 
                        <span class="text-muted"><?php echo htmlspecialchars($staff_info['email']); ?></span>
                    </div>
                    
                    <div class="d-grid">
                        <a href="profile.php" class="btn btn-outline-primary">
                            <i class="fas fa-edit"></i> Edit Profile
                        </a>
                    </div>
                </div>
            </div>

            <!-- Monthly Activity -->
            <div class="card border-0 shadow mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-pie"></i> This Month's Activity
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <span>Medications Managed</span>
                            <strong class="text-primary"><?php echo $monthly_medications; ?></strong>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-primary" style="width: 100%"></div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <span>Active Prescriptions</span>
                            <strong class="text-warning"><?php echo $active_medications; ?></strong>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-warning" style="width: <?php echo $active_medications > 0 ? ($active_medications / max($monthly_medications, 1)) * 100 : 0; ?>%"></div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <span>Patients Assisted</span>
                            <strong class="text-success"><?php echo $total_patients; ?></strong>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-success" style="width: <?php echo $total_patients > 0 ? ($total_patients / max($monthly_medications, 1)) * 100 : 0; ?>%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Important Guidelines -->
            <div class="card border-0 shadow">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle"></i> Medication Guidelines
                    </h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <i class="fas fa-check text-success"></i>
                            Always verify patient identity before administering medication
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success"></i>
                            Double-check dosage and medication name
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success"></i>
                            Record administration time accurately
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success"></i>
                            Monitor for adverse reactions
                        </li>
                        <li>
                            <i class="fas fa-check text-success"></i>
                            Report any medication errors immediately
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