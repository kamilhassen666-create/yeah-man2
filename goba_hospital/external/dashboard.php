<?php
require_once '../includes/config.php';
check_session('external');

$page_title = 'External Health Office Dashboard';

// Get external office information
$stmt = $db->prepare("SELECT * FROM external_office WHERE office_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$office_info = $stmt->fetch();

// Get dashboard statistics
try {
    // Count total patient transfers received
    $stmt = $db->prepare("SELECT COUNT(*) FROM external_patient_info WHERE receiving_office_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $total_received = $stmt->fetchColumn();
    
    // Count patient transfers sent
    $stmt = $db->prepare("SELECT COUNT(*) FROM external_patient_info WHERE sending_office_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $total_sent = $stmt->fetchColumn();
    
    // Count pending transfers
    $stmt = $db->prepare("
        SELECT COUNT(*) FROM external_patient_info 
        WHERE (receiving_office_id = ? OR sending_office_id = ?) 
        AND status = 'Pending'
    ");
    $stmt->execute([$_SESSION['user_id'], $_SESSION['user_id']]);
    $pending_transfers = $stmt->fetchColumn();
    
    // Count this month's activities
    $stmt = $db->prepare("
        SELECT COUNT(*) FROM external_patient_info 
        WHERE (receiving_office_id = ? OR sending_office_id = ?) 
        AND MONTH(transfer_date) = MONTH(CURDATE()) 
        AND YEAR(transfer_date) = YEAR(CURDATE())
    ");
    $stmt->execute([$_SESSION['user_id'], $_SESSION['user_id']]);
    $monthly_transfers = $stmt->fetchColumn();
    
    // Get recent patient transfers
    $stmt = $db->prepare("
        SELECT epi.*, 
               ro.office_name as receiving_office, 
               so.office_name as sending_office,
               ro.hospital_name as receiving_hospital,
               so.hospital_name as sending_hospital
        FROM external_patient_info epi
        LEFT JOIN external_office ro ON epi.receiving_office_id = ro.office_id
        LEFT JOIN external_office so ON epi.sending_office_id = so.office_id
        WHERE epi.receiving_office_id = ? OR epi.sending_office_id = ?
        ORDER BY epi.transfer_date DESC
        LIMIT 10
    ");
    $stmt->execute([$_SESSION['user_id'], $_SESSION['user_id']]);
    $recent_transfers = $stmt->fetchAll();
    
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
                        <i class="fas fa-exchange-alt"></i> External Health Office Dashboard
                    </h1>
                    <p class="text-muted">
                        <?php echo htmlspecialchars($office_info['office_name']); ?>
                        <span class="badge bg-info ms-2"><?php echo htmlspecialchars($office_info['hospital_name']); ?></span>
                        <span class="badge bg-secondary ms-1"><?php echo htmlspecialchars($office_info['region']); ?></span>
                    </p>
                </div>
                <div>
                    <span class="badge bg-primary px-3 py-2">
                        Office ID: <?php echo htmlspecialchars($office_info['office_id']); ?>
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
                    <div class="stats-number"><?php echo $total_received; ?></div>
                    <div class="stats-label">Patients Received</div>
                    <i class="fas fa-download position-absolute"></i>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="card stats-card stats-success border-0 shadow">
                <div class="card-body position-relative">
                    <div class="stats-number"><?php echo $total_sent; ?></div>
                    <div class="stats-label">Patients Transferred</div>
                    <i class="fas fa-upload position-absolute"></i>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="card stats-card stats-warning border-0 shadow">
                <div class="card-body position-relative">
                    <div class="stats-number"><?php echo $pending_transfers; ?></div>
                    <div class="stats-label">Pending Transfers</div>
                    <i class="fas fa-clock position-absolute"></i>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="card stats-card stats-info border-0 shadow">
                <div class="card-body position-relative">
                    <div class="stats-number"><?php echo $monthly_transfers; ?></div>
                    <div class="stats-label">This Month's Activities</div>
                    <i class="fas fa-chart-line position-absolute"></i>
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
                            <a href="receive_patient.php" class="btn btn-primary w-100 btn-lg">
                                <i class="fas fa-user-plus"></i><br>
                                Receive Patient
                            </a>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <a href="transfer_patient.php" class="btn btn-success w-100 btn-lg">
                                <i class="fas fa-share"></i><br>
                                Transfer Patient
                            </a>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <a href="patient_transfers.php" class="btn btn-info w-100 btn-lg">
                                <i class="fas fa-list"></i><br>
                                View Transfers
                            </a>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <a href="search_patient.php" class="btn btn-warning w-100 btn-lg">
                                <i class="fas fa-search"></i><br>
                                Search Patient
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Recent Patient Transfers -->
        <div class="col-lg-8">
            <div class="card border-0 shadow">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-history"></i> Recent Patient Transfers
                    </h5>
                    <a href="patient_transfers.php" class="btn btn-outline-primary btn-sm">
                        View All
                    </a>
                </div>
                <div class="card-body">
                    <?php if (!empty($recent_transfers)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Patient Info</th>
                                        <th>Transfer Type</th>
                                        <th>Hospital</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_transfers as $transfer): ?>
                                        <tr>
                                            <td>
                                                <small><?php echo date('Y-m-d H:i', strtotime($transfer['transfer_date'])); ?></small>
                                            </td>
                                            <td>
                                                <strong><?php echo htmlspecialchars($transfer['patient_first_name'] . ' ' . $transfer['patient_last_name']); ?></strong>
                                                <br><small class="text-muted">ID: <?php echo htmlspecialchars($transfer['external_patient_id']); ?></small>
                                            </td>
                                            <td>
                                                <?php if ($transfer['receiving_office_id'] == $_SESSION['user_id']): ?>
                                                    <span class="badge bg-primary">
                                                        <i class="fas fa-download"></i> Received
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge bg-success">
                                                        <i class="fas fa-upload"></i> Sent
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($transfer['receiving_office_id'] == $_SESSION['user_id']): ?>
                                                    <strong>From:</strong> <?php echo htmlspecialchars($transfer['sending_hospital']); ?>
                                                <?php else: ?>
                                                    <strong>To:</strong> <?php echo htmlspecialchars($transfer['receiving_hospital']); ?>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php
                                                $status_class = '';
                                                switch ($transfer['status']) {
                                                    case 'Pending': $status_class = 'bg-warning'; break;
                                                    case 'Completed': $status_class = 'bg-success'; break;
                                                    case 'Cancelled': $status_class = 'bg-danger'; break;
                                                    default: $status_class = 'bg-secondary';
                                                }
                                                ?>
                                                <span class="badge <?php echo $status_class; ?>">
                                                    <?php echo htmlspecialchars($transfer['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="view_transfer.php?id=<?php echo $transfer['id']; ?>" 
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
                            <i class="fas fa-exchange-alt fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No patient transfers found.</p>
                            <div class="d-flex gap-2 justify-content-center">
                                <a href="receive_patient.php" class="btn btn-primary">
                                    <i class="fas fa-download"></i> Receive Patient
                                </a>
                                <a href="transfer_patient.php" class="btn btn-success">
                                    <i class="fas fa-upload"></i> Transfer Patient
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Office Profile & Info -->
        <div class="col-lg-4">
            <!-- Office Profile -->
            <div class="card border-0 shadow mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-building"></i> Office Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <i class="fas fa-hospital fa-4x text-primary"></i>
                        <h6 class="mt-2 mb-0"><?php echo htmlspecialchars($office_info['office_name']); ?></h6>
                        <small class="text-muted"><?php echo htmlspecialchars($office_info['hospital_name']); ?></small>
                    </div>
                    
                    <div class="mb-2">
                        <strong>Office ID:</strong> 
                        <span class="text-muted"><?php echo htmlspecialchars($office_info['office_id']); ?></span>
                    </div>
                    <div class="mb-2">
                        <strong>Region:</strong> 
                        <span class="badge bg-info"><?php echo htmlspecialchars($office_info['region']); ?></span>
                    </div>
                    <div class="mb-2">
                        <strong>Contact Person:</strong> 
                        <span class="text-muted"><?php echo htmlspecialchars($office_info['contact_person']); ?></span>
                    </div>
                    <div class="mb-2">
                        <strong>Phone:</strong> 
                        <span class="text-muted"><?php echo htmlspecialchars($office_info['phone']); ?></span>
                    </div>
                    <div class="mb-3">
                        <strong>Email:</strong> 
                        <span class="text-muted"><?php echo htmlspecialchars($office_info['email']); ?></span>
                    </div>
                    
                    <div class="d-grid">
                        <a href="profile.php" class="btn btn-outline-primary">
                            <i class="fas fa-edit"></i> Update Information
                        </a>
                    </div>
                </div>
            </div>

            <!-- Transfer Statistics -->
            <div class="card border-0 shadow mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-pie"></i> Transfer Statistics
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <span>Patients Received</span>
                            <strong class="text-primary"><?php echo $total_received; ?></strong>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-primary" style="width: <?php echo $total_received > 0 ? 100 : 0; ?>%"></div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <span>Patients Transferred</span>
                            <strong class="text-success"><?php echo $total_sent; ?></strong>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-success" style="width: <?php echo $total_sent > 0 ? ($total_sent / max($total_received, 1)) * 100 : 0; ?>%"></div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <span>This Month</span>
                            <strong class="text-info"><?php echo $monthly_transfers; ?></strong>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-info" style="width: <?php echo $monthly_transfers > 0 ? ($monthly_transfers / max($total_received + $total_sent, 1)) * 100 : 0; ?>%"></div>
                        </div>
                    </div>
                    
                    <?php if ($pending_transfers > 0): ?>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            You have <strong><?php echo $pending_transfers; ?></strong> pending transfer(s).
                            <a href="patient_transfers.php?status=pending" class="alert-link">Review them</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Transfer Guidelines -->
            <div class="card border-0 shadow">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle"></i> Transfer Guidelines
                    </h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <i class="fas fa-check text-success"></i>
                            Verify patient identity before transfer
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success"></i>
                            Ensure all medical records are complete
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success"></i>
                            Confirm receiving hospital capacity
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success"></i>
                            Update transfer status promptly
                        </li>
                        <li>
                            <i class="fas fa-check text-success"></i>
                            Maintain patient confidentiality
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