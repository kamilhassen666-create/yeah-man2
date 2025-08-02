<?php
require_once '../includes/config.php';
check_session('admin');

$page_title = 'Admin Dashboard';

// Get dashboard statistics
try {
    // Count total patients
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM patient_info WHERE is_active = 1");
    $stmt->execute();
    $total_patients = $stmt->fetchColumn();
    
    // Count total doctors
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM doctor_info WHERE is_active = 1");
    $stmt->execute();
    $total_doctors = $stmt->fetchColumn();
    
    // Count total staff
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM staff_info WHERE is_active = 1");
    $stmt->execute();
    $total_staff = $stmt->fetchColumn();
    
    // Count total consultations this month
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM consultation_records WHERE MONTH(consultation_date) = MONTH(CURDATE()) AND YEAR(consultation_date) = YEAR(CURDATE())");
    $stmt->execute();
    $monthly_consultations = $stmt->fetchColumn();
    
    // Count total surgeries this month
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM surgery_records WHERE MONTH(surgery_date) = MONTH(CURDATE()) AND YEAR(surgery_date) = YEAR(CURDATE())");
    $stmt->execute();
    $monthly_surgeries = $stmt->fetchColumn();
    
    // Get recent activities
    $stmt = $db->prepare("
        SELECT user_id, user_type, action, created_at 
        FROM system_logs 
        ORDER BY created_at DESC 
        LIMIT 10
    ");
    $stmt->execute();
    $recent_activities = $stmt->fetchAll();
    
    // Get pending referrals
    $stmt = $db->prepare("
        SELECT COUNT(*) as total 
        FROM patient_referrals 
        WHERE status = 'Pending'
    ");
    $stmt->execute();
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
                        <i class="fas fa-tachometer-alt"></i> Admin Dashboard
                    </h1>
                    <p class="text-muted">Welcome back, <?php echo htmlspecialchars($_SESSION['full_name']); ?></p>
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
                    <div class="stats-number"><?php echo $total_doctors; ?></div>
                    <div class="stats-label">Total Doctors</div>
                    <i class="fas fa-user-md position-absolute"></i>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="card stats-card stats-info border-0 shadow">
                <div class="card-body position-relative">
                    <div class="stats-number"><?php echo $total_staff; ?></div>
                    <div class="stats-label">Total Staff</div>
                    <i class="fas fa-user-nurse position-absolute"></i>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="card stats-card stats-warning border-0 shadow">
                <div class="card-body position-relative">
                    <div class="stats-number"><?php echo $monthly_consultations; ?></div>
                    <div class="stats-label">This Month's Consultations</div>
                    <i class="fas fa-stethoscope position-absolute"></i>
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
                            <a href="manage_patients.php" class="btn btn-primary w-100 btn-lg">
                                <i class="fas fa-user-injured"></i><br>
                                Manage Patients
                            </a>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <a href="manage_doctors.php" class="btn btn-success w-100 btn-lg">
                                <i class="fas fa-user-md"></i><br>
                                Manage Doctors
                            </a>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <a href="manage_staff.php" class="btn btn-info w-100 btn-lg">
                                <i class="fas fa-user-nurse"></i><br>
                                Manage Staff
                            </a>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <a href="hospital_settings.php" class="btn btn-warning w-100 btn-lg">
                                <i class="fas fa-cog"></i><br>
                                Hospital Settings
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Recent Activities -->
        <div class="col-lg-8">
            <div class="card border-0 shadow">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-history"></i> Recent System Activities
                    </h5>
                    <a href="system_logs.php" class="btn btn-outline-primary btn-sm">
                        View All
                    </a>
                </div>
                <div class="card-body">
                    <?php if (!empty($recent_activities)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>User</th>
                                        <th>Type</th>
                                        <th>Action</th>
                                        <th>Time</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_activities as $activity): ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo htmlspecialchars($activity['user_id']); ?></strong>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary">
                                                    <?php echo ucfirst($activity['user_type']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo htmlspecialchars($activity['action']); ?></td>
                                            <td>
                                                <small class="text-muted">
                                                    <?php echo time_ago($activity['created_at']); ?>
                                                </small>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted text-center">No recent activities found.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- System Overview -->
        <div class="col-lg-4">
            <div class="card border-0 shadow mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-pie"></i> Monthly Overview
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
                            <strong class="text-success"><?php echo $monthly_surgeries; ?></strong>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-success" style="width: <?php echo $monthly_surgeries > 0 ? ($monthly_surgeries / max($monthly_consultations, 1)) * 100 : 0; ?>%"></div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <span>Pending Referrals</span>
                            <strong class="text-warning"><?php echo $pending_referrals; ?></strong>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-warning" style="width: <?php echo $pending_referrals > 0 ? ($pending_referrals / max($monthly_consultations, 1)) * 100 : 0; ?>%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- System Status -->
            <div class="card border-0 shadow">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-server"></i> System Status
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <span>Database</span>
                            <span class="badge bg-success">Online</span>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <span>Server</span>
                            <span class="badge bg-success">Running</span>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <span>Backup</span>
                            <span class="badge bg-success">Up to date</span>
                        </div>
                    </div>
                    
                    <div class="text-center mt-3">
                        <small class="text-muted">
                            Last updated: <?php echo date('Y-m-d H:i:s'); ?>
                        </small>
                    </div>
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

.stats-card.stats-info {
    background: linear-gradient(135deg, var(--info-color), #0aa2c0);
}

.stats-card.stats-warning {
    background: linear-gradient(135deg, var(--warning-color), #d39e00);
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