<?php
require_once '../includes/config.php';
requireUserType(['external']);

$pdo = getDBConnection();

// Get external office information
$stmt = $pdo->prepare("SELECT * FROM external_health_office WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$office = $stmt->fetch(PDO::FETCH_ASSOC);

// Get recent uploads
$stmt = $pdo->prepare("SELECT * FROM external_patient_info WHERE external_id = ? ORDER BY uploaded_date DESC LIMIT 5");
$stmt->execute([$_SESSION['user_id']]);
$recent_uploads = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get statistics
$stmt = $pdo->prepare("SELECT COUNT(*) as total_uploads FROM external_patient_info WHERE external_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$total_uploads = $stmt->fetch(PDO::FETCH_ASSOC)['total_uploads'];

$stmt = $pdo->prepare("SELECT COUNT(*) as pending_uploads FROM external_patient_info WHERE external_id = ? AND status = 'Pending'");
$stmt->execute([$_SESSION['user_id']]);
$pending_uploads = $stmt->fetch(PDO::FETCH_ASSOC)['pending_uploads'];

$stmt = $pdo->prepare("SELECT COUNT(*) as processed_uploads FROM external_patient_info WHERE external_id = ? AND status = 'Processed'");
$stmt->execute([$_SESSION['user_id']]);
$processed_uploads = $stmt->fetch(PDO::FETCH_ASSOC)['processed_uploads'];

include '../includes/header.php';
?>

<div class="row">
    <!-- Sidebar -->
    <div class="col-md-3">
        <div class="sidebar">
            <div class="text-center mb-4">
                <img src="../assets/images/external-avatar.png" alt="External Office" class="profile-avatar mb-3">
                <h5 class="text-white"><?php echo $office['name']; ?></h5>
                <p class="text-white-50">External Health Office</p>
            </div>
            
            <nav class="nav flex-column">
                <a class="nav-link active" href="dashboard.php">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
                <a class="nav-link" href="upload_patient.php">
                    <i class="fas fa-upload"></i> Upload Patient Info
                </a>
                <a class="nav-link" href="uploads.php">
                    <i class="fas fa-file-medical"></i> My Uploads
                </a>
                <a class="nav-link" href="track_status.php">
                    <i class="fas fa-search"></i> Track Status
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
                        <i class="fas fa-hospital-alt me-2"></i>
                        Welcome, <?php echo $office['name']; ?>!
                    </h2>
                    <p class="text-muted">Upload and manage patient information for Goba Hospital</p>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="dashboard-card">
                        <h3><?php echo $total_uploads; ?></h3>
                        <p>Total Uploads</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="dashboard-card">
                        <h3><?php echo $pending_uploads; ?></h3>
                        <p>Pending</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="dashboard-card">
                        <h3><?php echo $processed_uploads; ?></h3>
                        <p>Processed</p>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="row mb-4">
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
                                <div class="col-md-4 mb-3">
                                    <a href="upload_patient.php" class="btn btn-primary w-100">
                                        <i class="fas fa-upload me-2"></i>
                                        Upload Patient Info
                                    </a>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <a href="uploads.php" class="btn btn-success w-100">
                                        <i class="fas fa-file-medical me-2"></i>
                                        View Uploads
                                    </a>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <a href="track_status.php" class="btn btn-info w-100">
                                        <i class="fas fa-search me-2"></i>
                                        Track Status
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Uploads -->
            <div class="row">
                <div class="col-md-8 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-file-medical me-2"></i>
                                Recent Uploads
                            </h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($recent_uploads)): ?>
                                <p class="text-muted">No recent uploads found.</p>
                            <?php else: ?>
                                <?php foreach ($recent_uploads as $upload): ?>
                                    <div class="border-bottom pb-2 mb-2">
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <strong><?php echo $upload['patient_name']; ?></strong>
                                                <br>
                                                <small class="text-muted">
                                                    Patient ID: <?php echo $upload['patient_id']; ?>
                                                    <br>
                                                    <?php echo formatDateTime($upload['uploaded_date']); ?>
                                                </small>
                                            </div>
                                            <span class="badge bg-<?php echo $upload['status'] == 'Processed' ? 'success' : ($upload['status'] == 'Pending' ? 'warning' : 'danger'); ?>">
                                                <?php echo $upload['status']; ?>
                                            </span>
                                        </div>
                                        <p class="mb-1"><?php echo substr($upload['medical_info'], 0, 100) . '...'; ?></p>
                                        <a href="view_upload.php?id=<?php echo $upload['id']; ?>" class="btn btn-sm btn-outline-primary">View Details</a>
                                    </div>
                                <?php endforeach; ?>
                                <a href="uploads.php" class="btn btn-sm btn-outline-primary">View All Uploads</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Upload Form -->
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-upload me-2"></i>
                                Quick Upload
                            </h5>
                        </div>
                        <div class="card-body">
                            <form action="upload_patient.php" method="POST" enctype="multipart/form-data">
                                <div class="mb-3">
                                    <label for="patient_name" class="form-label">Patient Name</label>
                                    <input type="text" class="form-control" id="patient_name" name="patient_name" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="patient_id" class="form-label">Patient ID</label>
                                    <input type="text" class="form-control" id="patient_id" name="patient_id" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="medical_info" class="form-label">Medical Information</label>
                                    <textarea class="form-control" id="medical_info" name="medical_info" rows="3" required></textarea>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="file_upload" class="form-label">File Upload</label>
                                    <input type="file" class="form-control" id="file_upload" name="file_upload">
                                </div>
                                
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-upload me-2"></i>Upload
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Status Overview -->
            <div class="row">
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-chart-pie me-2"></i>
                                Upload Status
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-4">
                                    <h4 class="text-primary"><?php echo $total_uploads; ?></h4>
                                    <small class="text-muted">Total</small>
                                </div>
                                <div class="col-4">
                                    <h4 class="text-warning"><?php echo $pending_uploads; ?></h4>
                                    <small class="text-muted">Pending</small>
                                </div>
                                <div class="col-4">
                                    <h4 class="text-success"><?php echo $processed_uploads; ?></h4>
                                    <small class="text-muted">Processed</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-info-circle me-2"></i>
                                Upload Guidelines
                            </h5>
                        </div>
                        <div class="card-body">
                            <ul class="list-unstyled">
                                <li><i class="fas fa-check text-success me-2"></i>Ensure patient information is accurate</li>
                                <li><i class="fas fa-check text-success me-2"></i>Include all relevant medical details</li>
                                <li><i class="fas fa-check text-success me-2"></i>Upload supporting documents if available</li>
                                <li><i class="fas fa-check text-success me-2"></i>Use clear and descriptive file names</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>